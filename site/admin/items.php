<?php
$page = 'items'; $title = 'Услуги и решения';
require __DIR__ . '/partials/header.php';

$groups = [
  'services'  => 'Услуги (What We Do)',
  'solutions' => 'Готовые решения (Ready Solutions)',
  'finance'   => 'Ready to Deploy — Финансы и налоги',
  'hr'        => 'Ready to Deploy — HR',
];
$group = $_GET['group'] ?? 'services';
if (!isset($groups[$group])) $group = 'services';
$lang  = ($_GET['lang'] ?? 'en') === 'az' ? 'az' : 'en';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        foreach (($_POST['item'] ?? []) as $id => $f) {
            db()->prepare('UPDATE items SET title=?, body=?, sort_order=?, active=? WHERE id=?')
                ->execute([trim($f['title'] ?? ''), trim($f['body'] ?? ''),
                           (int)($f['sort_order'] ?? 0), isset($f['active']) ? 1 : 0, (int)$id]);
        }
    } elseif ($action === 'add') {
        db()->prepare('INSERT INTO items (`group`, lang, title, body, sort_order) VALUES (?,?,?,?,?)')
            ->execute([$group, $lang, trim($_POST['title'] ?? 'Новый пункт'), trim($_POST['body'] ?? ''),
                       (int)($_POST['sort_order'] ?? 99)]);
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM items WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
    }
    cache_clear();
    header("Location: items.php?group={$group}&lang={$lang}&saved=1"); exit;
}

$list = db()->prepare('SELECT * FROM items WHERE `group`=? AND lang=? ORDER BY sort_order, id');
$list->execute([$group, $lang]);
$rows = $list->fetchAll();
?>
<div class="tabs">
  <?php foreach ($groups as $g => $label): ?>
    <a class="<?= $g === $group ? 'active' : '' ?>" href="?group=<?= e($g) ?>&lang=<?= e($lang) ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>
<div class="tabs">
  <a class="<?= $lang === 'en' ? 'active' : '' ?>" href="?group=<?= e($group) ?>&lang=en">English</a>
  <a class="<?= $lang === 'az' ? 'active' : '' ?>" href="?group=<?= e($group) ?>&lang=az">Azərbaycan</a>
</div>

<form method="post">
  <?= csrf_field() ?><input type="hidden" name="action" value="save">
  <div class="card">
    <h2><?= e($groups[$group]) ?> — <?= $lang === 'en' ? 'English' : 'Azərbaycan' ?></h2>
    <p class="hint">Порядок задаётся числом: чем меньше, тем выше. Снимите галочку, чтобы временно скрыть пункт.</p>

    <?php if (!$rows): ?>
      <p class="hint">Пока пусто — добавьте первый пункт ниже.</p>
    <?php endif; ?>

    <?php foreach ($rows as $r): ?>
      <div style="border:1px solid var(--line);border-radius:12px;padding:16px;margin-bottom:14px">
        <div class="row">
          <div style="flex:3">
            <label>Заголовок</label>
            <input type="text" name="item[<?= $r['id'] ?>][title]" value="<?= e($r['title']) ?>">
          </div>
          <div style="flex:0 0 120px;min-width:110px">
            <label>Порядок</label>
            <input type="number" name="item[<?= $r['id'] ?>][sort_order]" value="<?= (int)$r['sort_order'] ?>">
          </div>
          <div style="flex:0 0 140px;min-width:130px">
            <label>Показывать</label>
            <label style="font-weight:600;font-size:14px">
              <input type="checkbox" name="item[<?= $r['id'] ?>][active]" value="1" <?= $r['active'] ? 'checked' : '' ?>
                     style="width:auto;margin-right:6px"> на сайте
            </label>
          </div>
        </div>
        <div class="field" style="margin-top:10px">
          <label>Описание</label>
          <textarea name="item[<?= $r['id'] ?>][body]"><?= e($r['body']) ?></textarea>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if ($rows): ?><button class="btn" type="submit">Сохранить изменения</button><?php endif; ?>
  </div>
</form>

<?php foreach ($rows as $r): ?>
<form method="post" onsubmit="return confirm('Удалить «<?= e($r['title']) ?>»?')" style="display:inline-block;margin:0 8px 8px 0">
  <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
  <button class="btn danger sm" type="submit">Удалить: <?= e(mb_strimwidth($r['title'], 0, 28, '…')) ?></button>
</form>
<?php endforeach; ?>

<div class="card">
  <h2>Добавить пункт</h2>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="row">
      <div style="flex:3"><label>Заголовок</label><input type="text" name="title" required></div>
      <div style="flex:0 0 120px"><label>Порядок</label><input type="number" name="sort_order" value="<?= count($rows) ?>"></div>
    </div>
    <div class="field" style="margin-top:10px"><label>Описание</label><textarea name="body"></textarea></div>
    <button class="btn" type="submit">Добавить</button>
  </form>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
