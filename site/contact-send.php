<?php
/**
 * Обработка формы обратной связи.
 * Защита: капча Turnstile, ловушка для ботов, лимит по IP,
 * проверка времени заполнения, строгая валидация, защита от инъекции заголовков.
 */
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/captcha.php';
require_once __DIR__ . '/app/mailer.php';

$lang = ($_POST['lang'] ?? 'en') === 'az' ? 'az' : 'en';
$back = ($lang === 'az' ? cfg('site.az_path') : cfg('site.en_path'));

function back_to(string $back, string $status): void
{
    $sep = (strpos($back, '?') === false) ? '?' : '&';
    header('Location: ' . $back . $sep . $status . '#contact');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') back_to($back, 'error=1');

/* 1. Ловушка для ботов — поле скрыто, человек его не заполнит */
if (!empty($_POST['website'])) back_to($back, 'sent=1');   // делаем вид, что всё хорошо

/* 2. Слишком быстрая отправка — почти наверняка бот */
$ts = (int)($_POST['ts'] ?? 0);
if ($ts > 0 && (time() - $ts) < 3) back_to($back, 'error=1');

/* 3. Лимит: не больше 5 отправок в час с одного IP */
if (!rate_limit_ok('contact', 5, 60)) back_to($back, 'error=2');

/* 4. Капча Cloudflare */
if (!turnstile_verify()) back_to($back, 'error=3');

/* 5. Валидация полей */
$clean = static function (string $v, int $max): string {
    $v = str_replace(["\r", "\n", "\0"], ' ', strip_tags($v));   // защита от инъекции заголовков
    $v = trim(preg_replace('/\s+/u', ' ', $v));
    return mb_substr($v, 0, $max);
};

$name    = $clean((string)($_POST['name'] ?? ''), 120);
$phone   = $clean((string)($_POST['phone'] ?? ''), 40);
$email   = $clean((string)($_POST['email'] ?? ''), 190);
$message = trim(strip_tags((string)($_POST['message'] ?? '')));
$message = mb_substr($message, 0, 4000);

$errors = [];
if (mb_strlen($name) < 2)                              $errors[] = 'name';
if (!preg_match('/^[0-9()+\-\s]{7,40}$/', $phone))     $errors[] = 'phone';
if (preg_match('/\d/', $phone) === 0)                  $errors[] = 'phone';
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
if (mb_strlen($message) < 5)                           $errors[] = 'message';

/* простая защита от спам-ссылок */
if (preg_match_all('~https?://~i', $message) > 3)      $errors[] = 'spam';

if ($errors) back_to($back, 'error=1');

/* 6. Сохраняем заявку в базе */
try {
    db()->prepare('INSERT INTO messages (name, phone, email, message, lang, ip) VALUES (?,?,?,?,?,?)')
        ->execute([$name, $phone, $email ?: null, $message, $lang, client_ip()]);
} catch (Throwable $e) { /* если таблицы нет — письмо всё равно уйдёт */ }

/* 7. Письмо администратору */
$to   = setting('contact_form_email', 'info@cenofex.ai');
$body = "Имя: $name\nТелефон: $phone\nE-mail: " . ($email ?: '—') . "\nЯзык: $lang\nIP: " . client_ip()
      . "\n\nСообщение:\n$message\n";

$err = '';
send_mail($to, 'CENOFEX — новая заявка с сайта', $body, $email, $err);

back_to($back, 'sent=1');
