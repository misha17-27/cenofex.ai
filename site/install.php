<?php
/**
 * Установщик CENOFEX.
 * 1) Заполните app/config.php (доступы к БД)
 * 2) Откройте https://cenofex.ai/install.php
 * 3) После установки УДАЛИТЕ этот файл.
 */
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

$done = false; $errors = []; $log = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    if ($name === '')                                  $errors[] = 'Укажите имя.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = 'Некорректный e-mail.';
    if (strlen($pass) < 8)                             $errors[] = 'Пароль — минимум 8 символов.';

    if (!$errors) {
        try {
            // 1. Таблицы (сначала убираем строки-комментарии, иначе запрос не распознаётся)
            $sql = file_get_contents(__DIR__ . '/app/schema.sql');
            $sql = preg_replace('/^\s*--.*$/m', '', $sql);
            $made = 0;
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if ($stmt === '') continue;
                db()->exec($stmt);
                $made++;
            }
            $log[] = "Таблицы созданы (запросов: {$made}).";

            // 2. Администратор
            $st = db()->prepare('SELECT id FROM users WHERE email = ?');
            $st->execute([$email]);
            if ($st->fetch()) {
                $log[] = 'Пользователь уже существует — пропущено.';
            } else {
                db()->prepare(
                    'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "admin")'
                )->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
                $log[] = 'Администратор создан.';
            }

            // 3. Контент из seed_content.json
            $seedFile = __DIR__ . '/app/seed_content.json';
            if (is_file($seedFile)) {
                $seed = json_decode(file_get_contents($seedFile), true);
                $groups = ['services', 'solutions', 'finance', 'hr'];
                $texts = 0; $cards = 0;

                foreach (['en', 'az'] as $lang) {
                    if (empty($seed[$lang])) continue;
                    foreach ($seed[$lang] as $key => $val) {
                        if (in_array($key, $groups, true)) continue;   // списки — ниже
                        if (!is_string($val)) continue;
                        content_set($lang, $key, $val);
                        $texts++;
                    }
                    foreach ($groups as $g) {
                        if (empty($seed[$lang][$g])) continue;
                        $cnt = db()->prepare('SELECT COUNT(*) c FROM items WHERE `group`=? AND lang=?');
                        $cnt->execute([$g, $lang]);
                        if ((int)$cnt->fetch()['c'] > 0) continue;      // уже заполнено
                        $i = 0;
                        foreach ($seed[$lang][$g] as $row) {
                            // services/solutions: [код, заголовок, текст]; finance/hr: [заголовок, текст]
                            $title = count($row) === 3 ? $row[1] : $row[0];
                            $body  = count($row) === 3 ? $row[2] : $row[1];
                            db()->prepare(
                                'INSERT INTO items (`group`, lang, title, body, sort_order) VALUES (?,?,?,?,?)'
                            )->execute([$g, $lang, $title, $body, $i++]);
                            $cards++;
                        }
                    }
                }
                $log[] = "Тексты перенесены: {$texts}, карточек: {$cards}.";
            }

            // 4. SEO по умолчанию
            $defaults = [
                'seo_title_en'       => 'CENOFEX | Your Transformation Partner. Your CoE.',
                'seo_desc_en'        => 'Cenofex helps companies turn transformation plans into working solutions — strategy, automation, AI and Center of Excellence as a Service.',
                'seo_title_az'       => 'CENOFEX | Transformasiya tərəfdaşınız. Sizin CoE.',
                'seo_desc_az'        => 'Cenofex şirkətlərə transformasiya planlarını işlək həllərə çevirməyə kömək edir — strategiya, avtomatlaşdırma, AI və CoE as a Service.',
                'og_image'           => '/images/logo-white-text.png',
                'contact_form_email' => 'info@cenofex.ai',
            ];
            foreach ($defaults as $k => $v) {
                if (setting($k) === '') setting_set($k, $v);
            }
            $log[] = 'SEO-настройки записаны.';

            // 5. Партнёры из папки images/partners
            $cnt = (int)db()->query('SELECT COUNT(*) c FROM partners')->fetch()['c'];
            if ($cnt === 0) {
                $known = [
                    'sap-logo.jpg' => 'SAP', 'ms-dynamics.jpg' => 'Microsoft Dynamics',
                    'ms-copilot.jpg' => 'Microsoft Copilot', 'uipath-logo.jpg' => 'UiPath',
                    'odoo-logo.png' => 'Odoo', '1c-logo.png' => '1C', 'sun-systems-logo.png' => 'Sun Systems',
                ];
                $i = 0;
                foreach ($known as $file => $title) {
                    if (!is_file(__DIR__ . '/images/partners/' . $file)) continue;
                    db()->prepare('INSERT INTO partners (name, logo, sort_order) VALUES (?,?,?)')
                        ->execute([$title, '/images/partners/' . $file, $i++]);
                }
                $log[] = "Партнёры добавлены: {$i}.";
            }

            cache_clear();
            $done = true;
        } catch (Throwable $ex) {
            $errors[] = 'Ошибка: ' . $ex->getMessage();
        }
    }
}
?><!doctype html>
<html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>CENOFEX — установка</title>
<style>
body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0c1615;color:#fff;
  font-family:system-ui,Segoe UI,Arial,sans-serif;padding:24px}
.card{background:#fff;color:#1c1c1c;border-radius:20px;padding:32px;max-width:520px;width:100%;
  box-shadow:0 30px 70px rgba(0,0,0,.35)}
h1{margin:0 0 6px;font-size:24px} p.mut{margin:0 0 22px;color:#5c6b69;font-size:14px}
label{display:block;font-size:13px;font-weight:700;margin:14px 0 6px}
input{width:100%;padding:12px 14px;border:1px solid #e5eeec;border-radius:10px;font:inherit;background:#fbfdfc}
button{margin-top:20px;width:100%;min-height:50px;border:0;border-radius:999px;cursor:pointer;
  background:linear-gradient(120deg,#02A78E,#048282);color:#fff;font-weight:800;font-size:15px}
.err{background:#fdecec;color:#a12020;border-radius:10px;padding:12px 14px;font-size:14px;margin-bottom:14px}
.ok{background:#e7f8f3;color:#046b5c;border-radius:10px;padding:12px 14px;font-size:14px}
code{background:#f2f6f5;padding:2px 6px;border-radius:6px}
</style></head><body>
<div class="card">
<?php if ($done): ?>
  <h1>Установка завершена</h1>
  <div class="ok"><?= implode('<br>', array_map('e', $log)) ?></div>
  <p class="mut" style="margin-top:16px">
    Обязательно удалите файл <code>install.php</code> с сервера.<br>
    Панель: <a href="admin/login.php">/admin/login.php</a>
  </p>
<?php else: ?>
  <h1>Установка CENOFEX</h1>
  <p class="mut">Создаст таблицы, перенесёт тексты сайта и первого администратора.</p>
  <?php foreach ($errors as $er): ?><div class="err"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post">
    <label>Имя</label><input name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
    <label>E-mail (логин)</label><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
    <label>Пароль (мин. 8 символов)</label><input type="password" name="password" required>
    <button type="submit">Установить</button>
  </form>
<?php endif; ?>
</div></body></html>
