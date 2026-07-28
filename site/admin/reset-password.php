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
        header('Location: login?reset=1'); exit;
    }
}
?><!doctype html>
<html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Новый пароль — CENOFEX</title>
<link rel="icon" type="image/png" href="../images/brand-icon.png">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css?v=3">
</head><body>
<div class="auth">
  <div class="auth-card">
    <img class="logo" src="../images/logo-dark-text.png" alt="CENOFEX">
    <h1>Новый пароль</h1>

    <?php if (!$row): ?>
      <div class="alert err">Ссылка недействительна или устарела. Запросите восстановление заново.</div>
      <div class="auth-links"><a href="forgot-password">Запросить новую ссылку</a></div>
    <?php else: ?>
      <p class="sub"><?= e($row['email']) ?></p>
      <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="field"><label for="p1">Новый пароль</label>
          <div class="pw-wrap" style="position:relative"><input id="p1" type="password" name="password" required autofocus style="padding-right:44px"><button type="button" class="pw-toggle" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:34px;height:34px;border:0;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#048282;border-radius:8px;padding:0" data-target="p1" aria-label="Показать пароль"><svg class="eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M3 3l18 18"/><path d="M10.6 10.6a3 3 0 0 0 4.2 4.2"/><path d="M9.9 5.2A9.6 9.6 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.7A17 17 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4-.8"/></svg></button></div></div>
        <div class="field"><label for="p2">Повторите пароль</label>
          <div class="pw-wrap" style="position:relative"><input id="p2" type="password" name="password2" required style="padding-right:44px"><button type="button" class="pw-toggle" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:34px;height:34px;border:0;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#048282;border-radius:8px;padding:0" data-target="p2" aria-label="Показать пароль"><svg class="eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M3 3l18 18"/><path d="M10.6 10.6a3 3 0 0 0 4.2 4.2"/><path d="M9.9 5.2A9.6 9.6 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.7A17 17 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4-.8"/></svg></button></div></div>
        <button class="btn" type="submit">Сохранить пароль</button>
      </form>
      <div class="auth-links"><a href="login">← Вернуться ко входу</a></div>
    <?php endif; ?>
  </div>
</div>
<script src="assets/admin.js?v=3"></script>
</body></html>
