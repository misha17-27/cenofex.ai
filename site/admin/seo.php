<?php
$page = 'seo'; $title = 'SEO и настройки';
require __DIR__ . '/partials/header.php';

$fields = [
  'seo_title_en'       => ['Заголовок страницы (EN)', 'Title в результатах поиска. Оптимально 50–60 символов.'],
  'seo_desc_en'        => ['Описание (EN)', 'Description в поиске. Оптимально 140–160 символов.', true],
  'seo_title_az'       => ['Заголовок страницы (AZ)', ''],
  'seo_desc_az'        => ['Описание (AZ)', '', true],
  'og_image'           => ['Картинка для соцсетей', 'Путь от корня сайта, например /images/logo-white-text.png'],
  'robots_index'       => ['Видимость в поиске', 'Закрывайте только на время работ — закрытый сайт выпадает из выдачи.', false, 'choice'],
  'contact_form_email' => ['Почта для заявок с формы', 'Куда приходят сообщения из формы обратной связи.'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (array_keys($fields) as $k) {
        setting_set($k, trim($_POST[$k] ?? ''));
    }
    cache_clear();
    header('Location: seo?saved=1'); exit;
}
?>
<form method="post">
<?= csrf_field() ?>
<div class="card">
  <h2>Поисковая оптимизация</h2>
  <p class="hint">Эти данные видят Google и соцсети при отправке ссылки.</p>

  <?php foreach ($fields as $k => $f):
        $isArea = !empty($f[2]); ?>
    <div class="field">
      <label><?= e($f[0]) ?></label>
      <?php if (($f[3] ?? '') === 'choice'):
            $cur = setting($k, '1') === '0' ? '0' : '1'; ?>
        <select name="<?= e($k) ?>">
          <option value="1"<?= $cur === '1' ? ' selected' : '' ?>>Открыт для поисковиков</option>
          <option value="0"<?= $cur === '0' ? ' selected' : '' ?>>Закрыт от поисковиков</option>
        </select>
      <?php elseif ($isArea): ?>
        <textarea name="<?= e($k) ?>" style="min-height:80px"><?= e(setting($k)) ?></textarea>
      <?php else: ?>
        <input type="text" name="<?= e($k) ?>" value="<?= e(setting($k)) ?>">
      <?php endif; ?>
      <?php if (!empty($f[1])): ?><p class="hint" style="margin:6px 0 0"><?= e($f[1]) ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>

  <button class="btn" type="submit">Сохранить</button>
</div>
</form>
<?php require __DIR__ . '/partials/footer.php'; ?>
