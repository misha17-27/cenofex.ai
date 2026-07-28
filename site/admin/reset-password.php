<?php
require_once dirname(__DIR__) . '/app/auth.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$row   = $token ? find_reset($token) : null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $row) {
    csrf_check();
    $p1 = $_POST['password'] ?? '';
    $p2 = $_POST['password2'] ?? '';
    if (strlen($p1) < 8)      $error = 'Пароль — минимум 8 символов.';
    elseif ($p1 !== $p2)      $error = 'Пароли не совпадают.';
    else {
        apply_reset($token, $p1);
        header('Location: login.php?reset=1'); exit;
    }
}
?><!doctype html>
<html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Новый пароль — CENOFEX</title>
<link rel="icon" type="image/png" href="../images/brand-icon.png">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
</head><body>
<div class="auth">
  <div class="auth-card">
    <img class="logo" src="../images/logo-dark-text.png" alt="CENOFEX">
    <h1>Новый пароль</h1>

    <?php if (!$row): ?>
      <div class="alert err">Ссылка недействительна или устарела. Запросите восстановление заново.</div>
      <div class="auth-links"><a href="forgot-password.php">Запросить новую ссылку</a></div>
    <?php else: ?>
      <p class="sub"><?= e($row['email']) ?></p>
      <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="field"><label for="p1">Новый пароль</label>
          <input id="p1" type="password" name="password" required autofocus></div>
        <div class="field"><label for="p2">Повторите пароль</label>
          <input id="p2" type="password" name="password2" required></div>
        <button class="btn" type="submit">Сохранить пароль</button>
      </form>
      <div class="auth-links"><a href="login.php">← Вернуться ко входу</a></div>
    <?php endif; ?>
  </div>
</div>
</body></html>
