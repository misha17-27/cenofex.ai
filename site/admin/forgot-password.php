<?php
require_once dirname(__DIR__) . '/app/auth.php';

$sent = false; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный e-mail.';
    } else {
        $token = create_reset_token($email);
        if ($token) send_reset_mail($email, $token);
        // Одинаковый ответ в любом случае — не раскрываем, есть ли такой пользователь
        $sent = true;
    }
}
?><!doctype html>
<html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Восстановление пароля — CENOFEX</title>
<link rel="icon" type="image/png" href="../images/brand-icon.png">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
</head><body>
<div class="auth">
  <div class="auth-card">
    <img class="logo" src="../images/logo-dark-text.png" alt="CENOFEX">
    <h1>Забыли пароль?</h1>
    <p class="sub">Пришлём ссылку для восстановления</p>

    <?php if ($sent): ?>
      <div class="alert ok">Если такой e-mail зарегистрирован, письмо со ссылкой отправлено.
        Ссылка действует 60 минут. Проверьте папку «Спам».</div>
    <?php else: ?>
      <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <div class="field"><label for="email">E-mail</label>
          <input id="email" type="email" name="email" required autofocus></div>
        <button class="btn" type="submit">Отправить ссылку</button>
      </form>
    <?php endif; ?>

    <div class="auth-links"><a href="login.php">← Вернуться ко входу</a></div>
  </div>
</div>
</body></html>
