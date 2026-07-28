<?php
// Доступ проверяем ДО вывода страницы
require_once dirname(__DIR__) . '/app/auth.php';
require_admin();     // управление пользователями — только для администратора

$page = 'users'; $title = 'Пользователи';
require __DIR__ . '/partials/header.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name  = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass  = $_POST['password'] ?? '';
        $role  = ($_POST['role'] ?? 'editor') === 'admin' ? 'admin' : 'editor';

        if ($name === '')                               $err = 'Укажите имя.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Некорректный e-mail.';
        elseif (strlen($pass) < 8)                      $err = 'Пароль — минимум 8 символов.';
        else {
            $st = db()->prepare('SELECT id FROM users WHERE email=?'); $st->execute([$email]);
            if ($st->fetch()) $err = 'Пользователь с таким e-mail уже есть.';
            else {
                db()->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)')
                    ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role]);
                header('Location: users?saved=1'); exit;
            }
        }
    } elseif ($action === 'save') {
        foreach (($_POST['u'] ?? []) as $id => $f) {
            $id = (int)$id;
            $role   = ($f['role'] ?? 'editor') === 'admin' ? 'admin' : 'editor';
            $active = isset($f['active']) ? 1 : 0;
            // нельзя разжаловать или отключить самого себя
            if ($id === (int)$user['id']) { $role = $user['role']; $active = 1; }
            db()->prepare('UPDATE users SET name=?, role=?, active=? WHERE id=?')
                ->execute([trim($f['name'] ?? ''), $role, $active, $id]);
            if (!empty($f['password'])) {
                if (strlen($f['password']) < 8) { $err = 'Пароль — минимум 8 символов.'; }
                else db()->prepare('UPDATE users SET password_hash=? WHERE id=?')
                         ->execute([password_hash($f['password'], PASSWORD_DEFAULT), $id]);
            }
        }
        if (!$err) { header('Location: users?saved=1'); exit; }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$user['id']) $err = 'Нельзя удалить самого себя.';
        else { db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]); header('Location: users?saved=1'); exit; }
    }
}

$rows = db()->query('SELECT * FROM users ORDER BY role, name')->fetchAll();
?>
<?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

<div class="card">
  <h2>Добавить пользователя</h2>
  <p class="hint">Роль «Администратор» даёт доступ к управлению пользователями. «Редактор» — только контент.</p>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="row">
      <div><label>Имя</label><input type="text" name="name" required></div>
      <div><label>E-mail</label><input type="email" name="email" required></div>
    </div>
    <div class="row" style="margin-top:10px">
      <div><label>Пароль (мин. 8 символов)</label><input type="password" name="password" required></div>
      <div style="flex:0 0 200px"><label>Роль</label>
        <select name="role"><option value="editor">Редактор</option><option value="admin">Администратор</option></select>
      </div>
    </div>
    <div style="margin-top:14px"><button class="btn" type="submit">Добавить</button></div>
  </form>
</div>

<form method="post">
  <?= csrf_field() ?><input type="hidden" name="action" value="save">
  <div class="card">
    <h2>Все пользователи</h2>
    <p class="hint">Поле пароля оставьте пустым, если менять его не нужно.</p>
    <table>
      <tr><th>Имя</th><th>E-mail</th><th style="width:160px">Роль</th><th style="width:190px">Новый пароль</th>
          <th style="width:90px">Активен</th><th style="width:130px">Вход</th><th></th></tr>
      <?php foreach ($rows as $r): $self = (int)$r['id'] === (int)$user['id']; ?>
      <tr>
        <td><input type="text" name="u[<?= $r['id'] ?>][name]" value="<?= e($r['name']) ?>"></td>
        <td><?= e($r['email']) ?><?= $self ? ' <span class="badge">это вы</span>' : '' ?></td>
        <td>
          <select name="u[<?= $r['id'] ?>][role]" <?= $self ? 'disabled' : '' ?>>
            <option value="editor" <?= $r['role'] === 'editor' ? 'selected' : '' ?>>Редактор</option>
            <option value="admin"  <?= $r['role'] === 'admin'  ? 'selected' : '' ?>>Администратор</option>
          </select>
        </td>
        <td><input type="password" name="u[<?= $r['id'] ?>][password]" placeholder="—"></td>
        <td><input type="checkbox" name="u[<?= $r['id'] ?>][active]" value="1" <?= $r['active'] ? 'checked' : '' ?>
                   <?= $self ? 'disabled checked' : '' ?> style="width:auto"></td>
        <td style="font-size:13px;color:var(--muted)"><?= $r['last_login_at'] ? e(date('d.m.Y H:i', strtotime($r['last_login_at']))) : '—' ?></td>
        <td><?php if (!$self): ?>
          <button class="btn danger sm" type="submit" form="du<?= $r['id'] ?>"
                  onclick="return confirm('Удалить <?= e($r['name']) ?>?')">Удалить</button>
        <?php endif; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <div style="margin-top:16px"><button class="btn" type="submit">Сохранить изменения</button></div>
  </div>
</form>

<?php foreach ($rows as $r): if ((int)$r['id'] === (int)$user['id']) continue; ?>
<form id="du<?= $r['id'] ?>" method="post" style="display:none">
  <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
</form>
<?php endforeach; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
