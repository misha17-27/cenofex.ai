<?php
/**
 * Cloudflare Turnstile — защита форм.
 * Ключи задаются в админке: Настройки → Безопасность.
 * Если ключи не заполнены — проверка автоматически отключается,
 * чтобы формы продолжали работать.
 */

require_once __DIR__ . '/helpers.php';

function turnstile_enabled(): bool
{
    return setting('turnstile_site_key') !== '' && setting('turnstile_secret') !== '';
}

/** Подключение скрипта Turnstile (в <head> или перед </body>). */
function turnstile_script(): string
{
    if (!turnstile_enabled()) return '';
    return '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
}

/** Виджет в форме. */
function turnstile_widget(string $theme = 'light'): string
{
    if (!turnstile_enabled()) return '';
    return '<div class="cf-turnstile" data-sitekey="' . e(setting('turnstile_site_key'))
         . '" data-theme="' . e($theme) . '" style="margin:6px 0 14px"></div>';
}

/** Проверка ответа на сервере. */
function turnstile_verify(): bool
{
    if (!turnstile_enabled()) return true;              // капча не настроена — пропускаем

    $token = $_POST['cf-turnstile-response'] ?? '';
    if ($token === '') return false;

    $post = http_build_query([
        'secret'   => setting('turnstile_secret'),
        'response' => $token,
        'remoteip' => client_ip(),
    ]);

    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $raw = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_TIMEOUT        => 8,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
    }
    if ($raw === false) {
        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $post,
            'timeout' => 8,
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
    }
    if ($raw === false) return false;

    $data = json_decode($raw, true);
    return !empty($data['success']);
}

function client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

/**
 * Ограничение частоты отправок: не более $limit за $minutes минут с одного IP.
 */
function rate_limit_ok(string $action, int $limit = 5, int $minutes = 60): bool
{
    $ip = client_ip();
    try {
        db()->prepare('DELETE FROM form_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)')
            ->execute([$minutes]);
        $st = db()->prepare(
            'SELECT COUNT(*) c FROM form_log WHERE ip = ? AND action = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $st->execute([$ip, $action, $minutes]);
        if ((int)$st->fetch()['c'] >= $limit) return false;

        db()->prepare('INSERT INTO form_log (ip, action) VALUES (?, ?)')->execute([$ip, $action]);
        return true;
    } catch (Throwable $e) {
        return true;    // если таблицы ещё нет — не блокируем работу формы
    }
}
