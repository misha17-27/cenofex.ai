<?php
/** Общие функции: контент, настройки, партнёры, кэш. */

require_once __DIR__ . '/db.php';

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/**
 * Ссылка на файл стилей/скриптов с версией по времени изменения.
 * Браузер сам подтянет новую версию после каждого обновления файла.
 */
function asset(string $relative, string $base = '.'): string
{
    $file = __DIR__ . '/..' . $relative;          // /assets/site.css -> site/assets/site.css
    $v    = is_file($file) ? filemtime($file) : time();
    return $base . $relative . '?v=' . $v;
}

/* ---------------- Контент (тексты сайта) ---------------- */

/** Все тексты одного языка: ['ключ' => 'значение'] */
function content_all(string $lang): array
{
    static $cache = [];
    if (isset($cache[$lang])) return $cache[$lang];

    $st = db()->prepare('SELECT `key`, `value` FROM content WHERE lang = ?');
    $st->execute([$lang]);
    $out = [];
    foreach ($st->fetchAll() as $r) $out[$r['key']] = $r['value'];
    return $cache[$lang] = $out;
}

/** Один текст с запасным значением. */
function t(array $c, string $key, string $fallback = ''): string
{
    return isset($c[$key]) && $c[$key] !== '' ? $c[$key] : $fallback;
}

function content_set(string $lang, string $key, string $value): void
{
    $st = db()->prepare(
        'INSERT INTO content (lang, `key`, `value`) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
    );
    $st->execute([$lang, $key, $value]);
}

/* ---------------- Настройки / SEO ---------------- */

function settings_all(): array
{
    static $s;
    if ($s !== null) return $s;
    $rows = db()->query('SELECT `key`, `value` FROM settings')->fetchAll();
    $s = [];
    foreach ($rows as $r) $s[$r['key']] = $r['value'];
    return $s;
}

function setting(string $key, string $fallback = ''): string
{
    $s = settings_all();
    return isset($s[$key]) && $s[$key] !== '' ? $s[$key] : $fallback;
}

function setting_set(string $key, string $value): void
{
    $st = db()->prepare(
        'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
    );
    $st->execute([$key, $value]);
}

/* ---------------- Списки (услуги, решения и т.д.) ---------------- */

/**
 * Элементы блока: services | solutions | finance | hr
 * Возвращает [['title'=>..,'body'=>..], ...] в нужном языке.
 */
function items(string $group, string $lang): array
{
    $st = db()->prepare(
        'SELECT title, body FROM items WHERE `group` = ? AND lang = ? AND active = 1 ORDER BY sort_order, id'
    );
    $st->execute([$group, $lang]);
    return $st->fetchAll();
}

/* ---------------- Партнёры ---------------- */

function partners(): array
{
    return db()->query(
        'SELECT * FROM partners WHERE active = 1 ORDER BY sort_order, id'
    )->fetchAll();
}

/* ---------------- Кэш публичных страниц ---------------- */

/**
 * Картинки страницы: адрес из панели, а если там пусто — стандартный.
 * Один список на сайт и на админку, иначе в панели «нет фото»,
 * хотя на странице картинка есть.
 */
function photo_default(string $key): string
{
    $map = [
        'photo_hero1' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1800&q=80',
        'photo_hero2' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1800&q=80',
        'photo_hero3' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1800&q=80',
        'photo_about' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=1800&q=80',
        'photo_tech'  => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1600&q=80',
    ];
    return $map[$key] ?? '';
}

function photo(string $key): string
{
    $v = trim(setting($key));
    return $v !== '' ? $v : photo_default($key);
}

/**
 * Подпись alt для картинки: своя из панели (на нужном языке),
 * иначе — переданный запасной текст.
 */
function photo_alt(string $key, string $lang, string $fallback = ''): string
{
    $v = trim(setting('alt_' . $key . '_' . $lang));
    return $v !== '' ? $v : $fallback;
}

function cache_path(string $lang): string
{
    return rtrim(cfg('paths.cache'), '/') . "/page_{$lang}.html";
}

/** Сбросить статические копии страниц (вызывается после сохранения в админке). */
function cache_clear(): void
{
    $dir = cfg('paths.cache');
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*.html') as $f) @unlink($f);
}
