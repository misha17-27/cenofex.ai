<?php
$page = 'partners'; $title = 'Партнёры';
require __DIR__ . '/partials/header.php';

$msg = ''; $err = '';

/** Загрузка логотипа. Возвращает публичный путь или null. */
function upload_logo(array $file, string &$err): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $err = 'Ошибка загрузки файла.'; return null; }
    if ($file['size'] > 3 * 1024 * 1024) { $err = 'Файл больше 3 МБ.'; return null; }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
    $mime = @mime_content_type($file['tmp_name']) ?: '';
    if (!isset($allowed[$mime])) { $err = 'Допустимы JPG, PNG, WEBP или SVG.'; return null; }

    $dir = cfg('paths.uploads');
    if (!is_dir($dir)) @mkdir($dir, 0775, true);

    $name = 'p' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) { $err = 'Не удалось сохранить файл.'; return null; }

    return rtrim(cfg('paths.uploads_url'), '/') . '/' . $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $logo = upload_logo($_FILES['logo'] ?? [], $err);
        $name = trim($_POST['name'] ?? '');
        if ($name === '')      $err = 'Укажите название партнёра.';
        elseif (!$logo && !$err) $err = 'Выберите файл логотипа.';
        if (!$err) {
            db()->prepare('INSERT INTO partners (name, logo, url, sort_order) VALUES (?,?,?,?)')
                ->execute([$name, $logo, trim($_POST['url'] ?? '') ?: null, (int)($_POST['sort_order'] ?? 99)]);
            cache_clear();
            header('Location: partners.php?saved=1'); exit;
        }
    } elseif ($action === 'save') {
        foreach (($_POST['p'] ?? []) as $id => $f) {
            db()->prepare('UPDATE partners SET name=?, url=?, sort_order=?, active=? WHERE id=?')
                ->execute([trim($f['name'] ?? ''), trim($f['url'] ?? '') ?: null,
                           (int)($f['sort_order'] ?? 0), isset($f['active']) ? 1 : 0, (int)$id]);
        }
        cache_clear();
        header('Location: partners.php?saved=1'); exit;
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare('SELECT logo FROM partners WHERE id=?'); $st->execute([$id]);
        if ($row = $st->fetch()) {
            // удаляем только собственные загрузки, файлы из /images не трогаем
            if (strpos($row['logo'], cfg('paths.uploads_url')) === 0) {
                @unlink(dirname(__DIR__) . $row['logo']);
            }
        }
        db()->prepare('DELETE FROM partners WHERE id=?')->execute([$id]);
        cache_clear();
        header('Location: partners.php?saved=1'); exit;
    }
}

$rows = db()->query('SELECT * FROM partners ORDER BY sort_order, id')->fetchAll();
?>
<?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

<div class="card">
  <h2>Добавить партнёра</h2>
  <p class="hint">Лучше всего — логотип на прозрачном фоне (PNG/SVG), высотой от 100 px. До 3 МБ.</p>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="row">
      <div><label>Название</label><input type="text" name="name" required placeholder="Например: SAP"></div>
      <div><label>Ссылка на сайт (необязательно)</label><input type="url" name="url" placeholder="https://..."></div>
      <div style="flex:0 0 120px"><label>Порядок</label><input type="number" name="sort_order" value="<?= count($rows) ?>"></div>
    </div>
    <div class="field" style="margin-top:10px"><label>Файл логотипа</label>
      <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" required></div>
    <button class="btn" type="submit">Добавить партнёра</button>
  </form>
</div>

<form method="post">
  <?= csrf_field() ?><input type="hidden" name="action" value="save">
  <div class="card">
    <h2>Список партнёров</h2>
    <p class="hint">Логотипы показываются бегущей лентой на сайте. Порядок — по числу.</p>
    <table>
      <tr><th>Логотип</th><th>Название</th><th>Ссылка</th><th style="width:90px">Порядок</th><th style="width:110px">Показывать</th><th></th></tr>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><div class="logo-cell"><img src="<?= e($r['logo']) ?>" alt="<?= e($r['name']) ?>"></div></td>
        <td><input type="text" name="p[<?= $r['id'] ?>][name]" value="<?= e($r['name']) ?>"></td>
        <td><input type="url" name="p[<?= $r['id'] ?>][url]" value="<?= e($r['url'] ?? '') ?>"></td>
        <td><input type="number" name="p[<?= $r['id'] ?>][sort_order]" value="<?= (int)$r['sort_order'] ?>"></td>
        <td><input type="checkbox" name="p[<?= $r['id'] ?>][active]" value="1" <?= $r['active'] ? 'checked' : '' ?> style="width:auto"></td>
        <td>
          <button class="btn danger sm" type="submit" form="del<?= $r['id'] ?>"
                  onclick="return confirm('Удалить <?= e($r['name']) ?>?')">Удалить</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php if ($rows): ?><div style="margin-top:16px"><button class="btn" type="submit">Сохранить изменения</button></div><?php endif; ?>
  </div>
</form>

<?php foreach ($rows as $r): ?>
<form id="del<?= $r['id'] ?>" method="post" style="display:none">
  <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
</form>
<?php endforeach; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
