<?php
$page = 'profile'; $title = 'Мой профиль';
require __DIR__ . '/partials/header.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $cur  = $_POST['current'] ?? '';
    $new  = $_POST['password'] ?? '';
    $new2 = $_POST['password2'] ?? '';

    if ($name === '') $err = 'Укажите имя.';
    else {
        db()->prepare('UPDATE users SET name=? WHERE id=?')->execute([$name, $user['id']]);

        if ($new !== '' || $new2 !== '') {
            if (!password_verify($cur, $user['password_hash'])) $err = 'Текущий пароль указан неверно.';
            elseif (strlen($new) < 8)  $err = 'Новый пароль — минимум 8 символов.';
            elseif ($new !== $new2)    $err = 'Пароли не совпадают.';
            else db()->prepare('UPDATE users SET password_hash=? WHERE id=?')
                     ->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        }
        if (!$err) { header('Location: profile.php?saved=1'); exit; }
    }
}
?>
<?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

<div class="card" style="max-width:620px">
  <h2>Профиль</h2>
  <p class="hint">E-mail (логин): <strong><?= e($user['email']) ?></strong> · роль:
    <span class="badge"><?= $user['role'] === 'admin' ? 'Администратор' : 'Редактор' ?></span></p>
  <form method="post">
    <?= csrf_field() ?>
    <div class="field"><label>Имя</label><input type="text" name="name" value="<?= e($user['name']) ?>" required></div>

    <h2 style="margin-top:22px">Смена пароля</h2>
    <p class="hint">Заполните, только если хотите изменить пароль.</p>
    <div class="field"><label>Текущий пароль</label><input type="password" name="current"></div>
    <div class="row">
      <div><label>Новый пароль</label><input type="password" name="password"></div>
      <div><label>Повторите</label><input type="password" name="password2"></div>
    </div>
    <div style="margin-top:16px"><button class="btn" type="submit">Сохранить</button></div>
  </form>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
