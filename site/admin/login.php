<?php
require_once dirname(__DIR__) . '/app/auth.php';

if (current_user()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';
    if (attempt_login($email, $pass)) { header('Location: index.php'); exit; }
    $error = 'Неверный e-mail или пароль.';
}
?><!doctype html>
<html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Вход — CENOFEX</title>
<link rel="icon" type="image/png" href="../images/brand-icon.png">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
</head><body>
<div class="auth">
  <div class="auth-card">
    <img class="logo" src="../images/logo-dark-text.png" alt="CENOFEX">
    <h1>Вход в панель</h1>
    <p class="sub">Управление контентом сайта</p>

    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
    <?php if (!empty($_GET['reset'])): ?><div class="alert ok">Пароль изменён. Войдите с новым паролем.</div><?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="field"><label for="email">E-mail</label>
        <input id="email" type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>"></div>
      <div class="field"><label for="password">Пароль</label>
        <input id="password" type="password" name="password" required></div>
      <button class="btn" type="submit">Войти</button>
    </form>

    <div class="auth-links"><a href="forgot-password.php">Забыли пароль?</a></div>
  </div>
</div>
</body></html>
