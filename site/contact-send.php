<?php
/** Обработка формы обратной связи. */
require_once __DIR__ . '/app/helpers.php';

$lang = ($_POST['lang'] ?? 'en') === 'az' ? 'az' : 'en';
$back = ($lang === 'az' ? cfg('site.az_path') : cfg('site.en_path')) . '#contact';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: $back"); exit; }

// приманка для ботов
if (!empty($_POST['website'])) { header("Location: $back&sent=1"); exit; }

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    header("Location: $back?error=1"); exit;
}

$to      = setting('contact_form_email', 'info@cenofex.ai');
$subject = '=?UTF-8?B?' . base64_encode('CENOFEX — website inquiry') . '?=';
$body    = "Name: $name\nE-mail: $email\nLanguage: $lang\n\n$message\n";
$headers = 'From: ' . cfg('site.from_name') . ' <' . cfg('site.from_email') . ">\r\n"
         . "Reply-To: $email\r\n"
         . "Content-Type: text/plain; charset=UTF-8\r\nMIME-Version: 1.0\r\n";

@mail($to, $subject, $body, $headers);
header("Location: $back?sent=1");
