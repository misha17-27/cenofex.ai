<?php
$page = 'media'; $title = 'Изображения сайта';
require __DIR__ . '/partials/header.php';

/** Фото, которые можно заменить: ключ настройки => подпись */
$slots = [
  'photo_hero1' => 'Слайд 1 — фото',
  'photo_hero2' => 'Слайд 2 — фото',
  'photo_hero3' => 'Слайд 3 — фото',
  'photo_about' => 'Блок «О компании» — фото',
  'photo_tech'  => 'Технологии — фото',
];
$err = '';

function upload_photo(array $file, string &$err): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $err = 'Ошибка загрузки.'; return null; }
    if ($file['size'] > 6 * 1024 * 1024) { $err = 'Файл больше 6 МБ.'; return null; }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = @mime_content_type($file['tmp_name']) ?: '';
    if (!isset($allowed[$mime])) { $err = 'Допустимы JPG, PNG или WEBP.'; return null; }

    $dir = dirname(__DIR__) . '/uploads/photos';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $name = 'img' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) { $err = 'Не удалось сохранить.'; return null; }
    return '/uploads/photos/' . $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (array_keys($slots) as $key) {
        // 1) загруженный файл важнее
        $path = upload_photo($_FILES[$key] ?? [], $err);
        if ($path) { setting_set($key, $path); continue; }
        // 2) иначе — ссылка из поля
        if (isset($_POST[$key])) {
            $url = trim($_POST[$key]);
            if ($url !== '') setting_set($key, $url);
        }
    }
    if (!$err) { cache_clear(); header('Location: media?saved=1'); exit; }
}
?>
<?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data">
<?= csrf_field() ?>
<div class="card">
  <h2>Фотографии страницы</h2>
  <p class="hint">Можно загрузить свой файл или указать ссылку на изображение.
    Рекомендуемый размер — не меньше 1600 px по ширине.</p>

  <?php foreach ($slots as $key => $label):
        $cur = setting($key); ?>
    <div style="border:1px solid var(--line);border-radius:12px;padding:16px;margin-bottom:14px">
      <div class="row" style="align-items:flex-start">
        <div style="flex:0 0 190px">
          <div style="background:#0c1615;border-radius:10px;overflow:hidden;aspect-ratio:4/3;display:grid;place-items:center">
            <?php if ($cur): ?><img src="<?= e($cur) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?><span style="color:#7d8f8b;font-size:13px">нет фото</span><?php endif; ?>
          </div>
        </div>
        <div style="flex:1">
          <label><?= e($label) ?></label>
          <input type="text" name="<?= e($key) ?>" value="<?= e($cur) ?>" placeholder="/uploads/photos/... или https://...">
          <p class="hint" style="margin:8px 0 6px">Загрузить новый файл (заменит ссылку):</p>
          <input type="file" name="<?= e($key) ?>" accept="image/jpeg,image/png,image/webp">
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <button class="btn" type="submit">Сохранить</button>
</div>
</form>

<div class="card">
  <h2>Логотипы и брендовые файлы</h2>
  <p class="hint">Лежат в папке <code>/images</code> на сервере. Меняются загрузкой файла с тем же именем
    (через Диспетчер файлов cPanel) — так логотип обновится сразу везде.</p>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
