<?php
/** Аутентификация, сессии, CSRF, сброс пароля. */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function session_start_safe(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

function current_user(): ?array
{
    session_start_safe();
    if (empty($_SESSION['user_id'])) return null;

    static $user;
    if ($user !== null) return $user;

    $st = db()->prepare('SELECT * FROM users WHERE id = ? AND active = 1');
    $st->execute([$_SESSION['user_id']]);
    $user = $st->fetch() ?: null;
    return $user;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) { header('Location: login.php'); exit; }
    return $u;
}

function require_admin(): array
{
    $u = require_login();
    if (($u['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Доступ только для администратора.');
    }
    return $u;
}

function attempt_login(string $email, string $password): bool
{
    $st = db()->prepare('SELECT * FROM users WHERE email = ? AND active = 1');
    $st->execute([strtolower(trim($email))]);
    $u = $st->fetch();
    if (!$u || !password_verify($password, $u['password_hash'])) return false;

    session_start_safe();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $u['id'];
    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$u['id']]);
    return true;
}

function logout(): void
{
    session_start_safe();
    $_SESSION = [];
    session_destroy();
}

/* ---------------- CSRF ---------------- */

function csrf_token(): string
{
    session_start_safe();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    session_start_safe();
    $t = $_POST['_csrf'] ?? '';
    if (!$t || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
        http_response_code(419);
        exit('Сессия устарела. Обновите страницу и попробуйте снова.');
    }
}

/* ---------------- Сброс пароля ---------------- */

function create_reset_token(string $email): ?string
{
    $st = db()->prepare('SELECT id FROM users WHERE email = ? AND active = 1');
    $st->execute([strtolower(trim($email))]);
    if (!$st->fetch()) return null;                 // пользователя нет

    $token = bin2hex(random_bytes(32));
    db()->prepare('DELETE FROM password_resets WHERE email = ?')->execute([strtolower(trim($email))]);
    db()->prepare(
        'INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 60 MINUTE))'
    )->execute([strtolower(trim($email)), hash('sha256', $token)]);

    return $token;
}

function find_reset(string $token): ?array
{
    $st = db()->prepare('SELECT * FROM password_resets WHERE token_hash = ? AND expires_at > NOW()');
    $st->execute([hash('sha256', $token)]);
    return $st->fetch() ?: null;
}

function apply_reset(string $token, string $newPassword): bool
{
    $row = find_reset($token);
    if (!$row) return false;
    db()->prepare('UPDATE users SET password_hash = ? WHERE email = ?')
        ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $row['email']]);
    db()->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$row['email']]);
    return true;
}

function send_reset_mail(string $email, string $token): bool
{
    $url  = rtrim(cfg('site.url'), '/') . '/admin/reset-password.php?token=' . urlencode($token);
    $from = cfg('site.from_email');
    $name = cfg('site.from_name');

    $subject = '=?UTF-8?B?' . base64_encode('CENOFEX — password reset') . '?=';
    $body = "Здравствуйте!\n\n"
          . "Вы запросили сброс пароля для админ-панели CENOFEX.\n"
          . "Ссылка действительна 60 минут:\n\n$url\n\n"
          . "Если вы не запрашивали сброс — просто проигнорируйте письмо.\n";

    $headers = "From: {$name} <{$from}>\r\n"
             . "Reply-To: {$from}\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "MIME-Version: 1.0\r\n";

    return @mail($email, $subject, $body, $headers);
}
