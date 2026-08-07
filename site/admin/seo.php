<?php
$page = 'seo'; $title = 'SEO и настройки';
require __DIR__ . '/partials/header.php';

$fields = [
  'seo_title_en'       => ['Заголовок страницы (EN)', 'Title в результатах поиска. Оптимально 50–60 символов.'],
  'seo_desc_en'        => ['Описание (EN)', 'Description в поиске. Оптимально 140–160 символов.', true],
  'seo_title_az'       => ['Заголовок страницы (AZ)', ''],
  'seo_desc_az'        => ['Описание (AZ)', '', true],
  'og_image'           => ['Картинка для соцсетей', 'Показывается, когда ссылку на сайт отправляют в мессенджере или соцсети. Лучше 1200×630 px.', false, 'image'],
  'robots_index'       => ['Видимость в поиске', 'Закрывайте только на время работ — закрытый сайт выпадает из выдачи.', false, 'choice'],
  'contact_form_email' => ['Почта для заявок с формы', 'Куда приходят сообщения из формы обратной связи.'],
];

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // загруженный файл важнее того, что вписано в поле
    $uploaded = upload_image($_FILES['og_image'] ?? [], $err);

    foreach (array_keys($fields) as $k) {
        if ($k === 'og_image' && $uploaded) { setting_set($k, $uploaded); continue; }
        setting_set($k, trim($_POST[$k] ?? ''));
    }
    if (!$err) { cache_clear(); header('Location: seo?saved=1'); exit; }
}
?>
<?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<?= csrf_field() ?>
<div class="card">
  <h2>Поисковая оптимизация</h2>
  <p class="hint">Эти данные видят Google и соцсети при отправке ссылки.</p>

  <?php foreach ($fields as $k => $f):
        $isArea = !empty($f[2]); ?>
    <div class="field">
      <label><?= e($f[0]) ?></label>
      <?php if (($f[3] ?? '') === 'image'):
            $og = setting($k, '/images/logo-white-text.png'); ?>
        <div class="row" style="align-items:flex-start">
          <div style="flex:0 0 190px">
            <div style="background:#0c1615;border-radius:10px;overflow:hidden;aspect-ratio:1200/630;display:grid;place-items:center">
              <?php if ($og): ?><img src="<?= e($og) ?>" alt="" style="width:100%;height:100%;object-fit:contain">
              <?php else: ?><span style="color:#7d8f8b;font-size:13px">нет фото</span><?php endif; ?>
            </div>
          </div>
          <div style="flex:1">
            <input type="text" name="<?= e($k) ?>" value="<?= e(setting($k)) ?>" placeholder="/images/logo-white-text.png">
            <p class="hint" style="margin:8px 0 6px">Загрузить новый файл (заменит ссылку):</p>
            <input type="file" name="<?= e($k) ?>" accept="image/jpeg,image/png,image/webp">
          </div>
        </div>
      <?php elseif (($f[3] ?? '') === 'choice'):
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
