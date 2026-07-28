<?php
$page = 'messages'; $title = 'Заявки с сайта';
require __DIR__ . '/partials/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'read')        db()->prepare('UPDATE messages SET is_read=1 WHERE id=?')->execute([(int)$_POST['id']]);
    elseif ($action === 'unread')  db()->prepare('UPDATE messages SET is_read=0 WHERE id=?')->execute([(int)$_POST['id']]);
    elseif ($action === 'delete')  db()->prepare('DELETE FROM messages WHERE id=?')->execute([(int)$_POST['id']]);
    elseif ($action === 'read_all') db()->query('UPDATE messages SET is_read=1');
    header('Location: messages?saved=1'); exit;
}

try {
    $rows = db()->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 300')->fetchAll();
} catch (Throwable $e) {
    $rows = null;    // таблицы ещё нет
}
?>
<?php if ($rows === null): ?>
  <div class="alert err">Таблица заявок ещё не создана. Запустите <code>/install.php</code> ещё раз —
    он добавит недостающие таблицы и не тронет существующие данные.</div>
<?php else: ?>

<div class="card">
  <h2>Сообщения из формы обратной связи</h2>
  <p class="hint">Всего: <?= count($rows) ?>. Заявки также дублируются на почту
    <strong><?= e(setting('contact_form_email', 'info@cenofex.ai')) ?></strong>.</p>
  <?php if ($rows): ?>
    <form method="post" style="display:inline">
      <?= csrf_field() ?><input type="hidden" name="action" value="read_all">
      <button class="btn ghost sm" type="submit">Отметить все прочитанными</button>
    </form>
  <?php endif; ?>
</div>

<?php if (!$rows): ?>
  <div class="card"><p class="hint" style="margin:0">Заявок пока нет.</p></div>
<?php endif; ?>

<?php foreach ($rows as $m): ?>
  <div class="card" style="<?= $m['is_read'] ? '' : 'border-left:4px solid var(--green)' ?>">
    <div class="row" style="align-items:flex-start">
      <div style="flex:2">
        <h2 style="margin-bottom:6px">
          <?= e($m['name']) ?>
          <?= $m['is_read'] ? '' : '<span class="badge">новая</span>' ?>
          <span class="badge gray"><?= e(strtoupper($m['lang'])) ?></span>
        </h2>
        <p class="hint" style="margin:0 0 10px">
          <?= e(date('d.m.Y H:i', strtotime($m['created_at']))) ?> · IP <?= e($m['ip'] ?? '—') ?>
        </p>
        <p style="margin:0 0 6px;font-size:14.5px">
          <strong>Телефон:</strong> <a href="tel:<?= e(preg_replace('/\s+/', '', $m['phone'])) ?>"><?= e($m['phone']) ?></a>
          <?php if ($m['email']): ?><br><strong>E-mail:</strong> <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a><?php endif; ?>
        </p>
        <div style="background:#f7faf9;border:1px solid var(--line);border-radius:10px;padding:12px 14px;
                    font-size:14.5px;white-space:pre-wrap"><?= e($m['message']) ?></div>
      </div>
      <div style="flex:0 0 190px;min-width:170px">
        <form method="post" style="margin-bottom:8px">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= $m['is_read'] ? 'unread' : 'read' ?>">
          <input type="hidden" name="id" value="<?= $m['id'] ?>">
          <button class="btn ghost sm" type="submit" style="width:100%">
            <?= $m['is_read'] ? 'Пометить непрочитанной' : 'Прочитано' ?>
          </button>
        </form>
        <form method="post" onsubmit="return confirm('Удалить заявку?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $m['id'] ?>">
          <button class="btn danger sm" type="submit" style="width:100%">Удалить</button>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
