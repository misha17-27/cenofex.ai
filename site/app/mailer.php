<?php
/**
 * Отправка писем: SMTP (если настроен в админке) или функция mail() как запасной вариант.
 * Без внешних библиотек.
 */
require_once __DIR__ . '/helpers.php';

function mail_enabled_smtp(): bool
{
    return setting('smtp_host') !== '' && setting('smtp_user') !== '';
}

/**
 * @param string $to      получатель
 * @param string $subject тема (обычный текст)
 * @param string $body    текст письма
 * @param string $replyTo необязательный адрес для ответа
 * @param string $error   сюда попадёт текст ошибки
 */
function send_mail(string $to, string $subject, string $body, string $replyTo = '', string &$error = ''): bool
{
    $fromEmail = setting('smtp_from', cfg('site.from_email'));
    $fromName  = setting('smtp_from_name', cfg('site.from_name'));

    // запрет подстановки заголовков
    $to      = str_replace(["\r", "\n"], '', $to);
    $replyTo = str_replace(["\r", "\n"], '', $replyTo);
    $subject = str_replace(["\r", "\n"], ' ', $subject);

    if (mail_enabled_smtp()) {
        return smtp_send($to, $subject, $body, $fromEmail, $fromName, $replyTo, $error);
    }

    $headers = 'From: ' . mime_word($fromName) . " <{$fromEmail}>\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\nMIME-Version: 1.0\r\n";
    if ($replyTo !== '') $headers .= "Reply-To: {$replyTo}\r\n";

    $ok = @mail($to, mime_word($subject), $body, $headers);
    if (!$ok) $error = 'Функция mail() не смогла отправить письмо. Настройте SMTP.';
    return $ok;
}

/** Кодирование не-ASCII в заголовке письма. */
function mime_word(string $s): string
{
    return preg_match('/[\x80-\xFF]/', $s)
        ? '=?UTF-8?B?' . base64_encode($s) . '?='
        : $s;
}

function smtp_send(string $to, string $subject, string $body, string $fromEmail,
                   string $fromName, string $replyTo, string &$error): bool
{
    $host   = setting('smtp_host');
    $port   = (int)(setting('smtp_port', '587'));
    $user   = setting('smtp_user');
    $pass   = setting('smtp_pass');
    $secure = setting('smtp_secure', 'tls');           // tls | ssl | none

    $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);

    $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) { $error = "Не удалось подключиться к {$host}:{$port} ({$errstr})"; return false; }
    stream_set_timeout($fp, 15);

    $read = static function () use ($fp): string {
        $data = '';
        while ($line = fgets($fp, 515)) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') break;
        }
        return $data;
    };
    $cmd = static function (string $c) use ($fp, $read): string {
        fwrite($fp, $c . "\r\n");
        return $read();
    };
    $code = static fn(string $r): int => (int)substr(trim($r), 0, 3);

    if ($code($read()) !== 220) { $error = 'Сервер не ответил приветствием.'; fclose($fp); return false; }

    $ehlo = $cmd('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
    if ($code($ehlo) !== 250) { $error = 'Ошибка EHLO: ' . trim($ehlo); fclose($fp); return false; }

    if ($secure === 'tls') {
        if ($code($cmd('STARTTLS')) !== 220) { $error = 'Сервер отклонил STARTTLS.'; fclose($fp); return false; }
        if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $error = 'Не удалось включить шифрование TLS.'; fclose($fp); return false;
        }
        $cmd('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
    }

    if ($user !== '') {
        if ($code($cmd('AUTH LOGIN')) !== 334)              { $error = 'Сервер не принял AUTH LOGIN.'; fclose($fp); return false; }
        if ($code($cmd(base64_encode($user))) !== 334)      { $error = 'Логин не принят.'; fclose($fp); return false; }
        if ($code($cmd(base64_encode($pass))) !== 235)      { $error = 'Неверный логин или пароль SMTP.'; fclose($fp); return false; }
    }

    if ($code($cmd('MAIL FROM:<' . $fromEmail . '>')) !== 250) { $error = 'Адрес отправителя отклонён.'; fclose($fp); return false; }
    if ($code($cmd('RCPT TO:<' . $to . '>')) > 299)           { $error = 'Адрес получателя отклонён.'; fclose($fp); return false; }
    if ($code($cmd('DATA')) !== 354)                          { $error = 'Сервер не принял команду DATA.'; fclose($fp); return false; }

    $headers = 'From: ' . mime_word($fromName) . " <{$fromEmail}>\r\n"
             . "To: <{$to}>\r\n"
             . 'Subject: ' . mime_word($subject) . "\r\n"
             . 'Date: ' . date('r') . "\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "Content-Transfer-Encoding: 8bit\r\n";
    if ($replyTo !== '') $headers .= "Reply-To: <{$replyTo}>\r\n";

    // точка в начале строки экранируется по правилам SMTP
    $data = preg_replace('/^\./m', '..', str_replace("\r\n", "\n", $body));
    $data = str_replace("\n", "\r\n", $data);

    fwrite($fp, $headers . "\r\n" . $data . "\r\n.\r\n");
    $res = $read();
    if ($code($res) !== 250) { $error = 'Письмо отклонено: ' . trim($res); fclose($fp); return false; }

    $cmd('QUIT');
    fclose($fp);
    return true;
}
