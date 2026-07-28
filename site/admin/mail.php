<?php
require_once dirname(__DIR__) . '/app/auth.php';
require_admin();

$page = 'mail'; $title = 'Почта (SMTP)';
require __DIR__ . '/partials/header.php';
require_once dirname(__DIR__) . '/app/mailer.php';

$notice = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'save') {
        foreach (['smtp_host','smtp_port','smtp_user','smtp_secure','smtp_from','smtp_from_name'] as $k) {
            setting_set($k, trim($_POST[$k] ?? ''));
        }
        // пароль не затираем, если поле оставили пустым
        if (($_POST['smtp_pass'] ?? '') !== '') setting_set('smtp_pass', $_POST['smtp_pass']);
        header('Location: mail?saved=1'); exit;
    }

    if ($action === 'test') {
        $to = trim($_POST['test_to'] ?? '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $err = 'Укажите корректный адрес для проверки.';
        } else {
            $error = '';
            $ok = send_mail($to, 'CENOFEX — проверка почты',
                "Это тестовое письмо из админ-панели CENOFEX.\nЕсли вы его читаете — отправка настроена верно.",
                '', $error);
            if ($ok) $notice = 'Письмо отправлено на ' . $to . '. Проверьте входящие и «Спам».';
            else     $err = 'Не отправлено. ' . $error;
        }
    }
}
?>
<?php if ($notice): ?><div class="alert ok"><?= e($notice) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

<div class="card">
  <h2>Настройки SMTP</h2>
  <p class="hint">Через SMTP письма доходят надёжнее, чем через стандартную функцию сервера.
    Способ отправки сейчас:
    <?= mail_enabled_smtp() ? '<span class="badge">SMTP</span>' : '<span class="badge gray">стандартная функция mail()</span>' ?>
  </p>

  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="save">
    <div class="row">
      <div style="flex:2"><label>SMTP-сервер</label>
        <input type="text" name="smtp_host" value="<?= e(setting('smtp_host')) ?>" placeholder="mail.cenofex.ai"></div>
      <div style="flex:0 0 130px"><label>Порт</label>
        <input type="number" name="smtp_port" value="<?= e(setting('smtp_port', '587')) ?>"></div>
      <div style="flex:0 0 180px"><label>Шифрование</label>
        <select name="smtp_secure">
          <?php foreach (['tls' => 'STARTTLS (587)', 'ssl' => 'SSL (465)', 'none' => 'без шифрования'] as $v => $lbl): ?>
            <option value="<?= $v ?>" <?= setting('smtp_secure', 'tls') === $v ? 'selected' : '' ?>><?= e($lbl) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row" style="margin-top:12px">
      <div><label>Пользователь (обычно полный адрес почты)</label>
        <input type="text" name="smtp_user" value="<?= e(setting('smtp_user')) ?>" placeholder="info@cenofex.ai"></div>
      <div><label>Пароль</label>
        <div class="pw-wrap" style="position:relative">
          <input id="smtppass" type="password" name="smtp_pass" style="padding-right:44px"
                 placeholder="<?= setting('smtp_pass') !== '' ? 'сохранён — оставьте пустым' : '' ?>">
          <button type="button" class="pw-toggle" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:34px;height:34px;border:0;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#048282;border-radius:8px;padding:0" data-target="smtppass" aria-label="Показать пароль">
            <svg class="eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M3 3l18 18"/><path d="M10.6 10.6a3 3 0 0 0 4.2 4.2"/><path d="M9.9 5.2A9.6 9.6 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.7A17 17 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4-.8"/></svg>
          </button>
        </div>
      </div>
    </div>

    <div class="row" style="margin-top:12px">
      <div><label>Адрес отправителя</label>
        <input type="text" name="smtp_from" value="<?= e(setting('smtp_from', 'info@cenofex.ai')) ?>"></div>
      <div><label>Имя отправителя</label>
        <input type="text" name="smtp_from_name" value="<?= e(setting('smtp_from_name', 'CENOFEX')) ?>"></div>
    </div>

    <div style="margin-top:16px"><button class="btn" type="submit">Сохранить</button></div>
  </form>
</div>

<div class="card">
  <h2>Проверка отправки</h2>
  <p class="hint">Отправим тестовое письмо, чтобы убедиться, что настройки верные.</p>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="test">
    <div class="row">
      <div><label>Адрес получателя</label>
        <input type="email" name="test_to" value="<?= e($user['email']) ?>" required></div>
      <div style="flex:0 0 220px;display:flex;align-items:flex-end">
        <button class="btn ghost" type="submit" style="width:100%">Отправить тест</button></div>
    </div>
  </form>
</div>

<div class="card">
  <h2>Где взять данные</h2>
  <p class="hint" style="margin:0">В cPanel → <strong>Email Accounts</strong> → у нужного ящика нажмите
    <strong>Connect Devices</strong>. Там указаны сервер, порт и способ шифрования.
    Пользователь — полный адрес почты, пароль — от этого ящика.</p>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
