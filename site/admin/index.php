<?php
$page = 'dashboard'; $title = 'Обзор';
require __DIR__ . '/partials/header.php';

$counts = [
  'texts'    => (int)db()->query('SELECT COUNT(*) c FROM content')->fetch()['c'],
  'items'    => (int)db()->query('SELECT COUNT(*) c FROM items WHERE active=1')->fetch()['c'],
  'partners' => (int)db()->query('SELECT COUNT(*) c FROM partners WHERE active=1')->fetch()['c'],
  'users'    => (int)db()->query('SELECT COUNT(*) c FROM users WHERE active=1')->fetch()['c'],
];
$cacheEn = is_file(cache_path('en')); $cacheAz = is_file(cache_path('az'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_cache') {
    csrf_check(); cache_clear();
    header('Location: index.php?saved=1'); exit;
}
?>
<div class="grid c4" style="margin-bottom:18px">
  <div class="stat"><b><?= $counts['texts'] ?></b><span>текстовых блоков</span></div>
  <div class="stat"><b><?= $counts['items'] ?></b><span>карточек услуг/решений</span></div>
  <div class="stat"><b><?= $counts['partners'] ?></b><span>партнёров</span></div>
  <div class="stat"><b><?= $counts['users'] ?></b><span>пользователей</span></div>
</div>

<div class="grid c2">
  <div class="card">
    <h2>Быстрые действия</h2>
    <p class="hint">С чего обычно начинают.</p>
    <div class="row">
      <a class="btn" href="content.php">Редактировать тексты</a>
      <a class="btn ghost" href="partners.php">Добавить партнёра</a>
      <a class="btn ghost" href="seo.php">Настроить SEO</a>
    </div>
  </div>

  <div class="card">
    <h2>Кэш страниц</h2>
    <p class="hint">Сайт отдаётся как статика — это делает его быстрым.
      Кэш обновляется сам при сохранении. Кнопка ниже — на случай, если нужно принудительно.</p>
    <p style="font-size:14px;margin:0 0 14px">
      Английская версия: <span class="badge<?= $cacheEn ? '' : ' gray' ?>"><?= $cacheEn ? 'готова' : 'будет создана' ?></span>
      &nbsp; Азербайджанская: <span class="badge<?= $cacheAz ? '' : ' gray' ?>"><?= $cacheAz ? 'готова' : 'будет создана' ?></span>
    </p>
    <form method="post"><?= csrf_field() ?>
      <input type="hidden" name="action" value="clear_cache">
      <button class="btn ghost" type="submit">Обновить кэш</button>
    </form>
  </div>
</div>

<div class="card">
  <h2>Страницы сайта</h2>
  <p class="hint">Открыть текущую версию в новой вкладке.</p>
  <div class="row">
    <a class="btn ghost" href="../index5.php" target="_blank" rel="noopener">Английская версия</a>
    <a class="btn ghost" href="../az/" target="_blank" rel="noopener">Азербайджанская версия</a>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
