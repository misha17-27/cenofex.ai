<?php
/**
 * Контакты и соцсети.
 * Телефон / почта / адрес хранятся в контенте (свои для EN и AZ),
 * WhatsApp и ссылки на соцсети — общие настройки сайта.
 */
$page = 'contacts'; $title = 'Контакты и соцсети';
require __DIR__ . '/partials/header.php';

/** Ключи контактов: они двуязычные, поэтому лежат в таблице контента. */
$contactKeys = [
  'contact_phone'   => 'Номер телефона',
  'contact_email'   => 'Адрес почты',
  'contact_address' => 'Адрес',
];

/** Соцсети: ключ настройки => [подпись, пример] */
$socials = [
  'social_linkedin'  => ['LinkedIn',  'https://www.linkedin.com/company/cenofex'],
  'social_instagram' => ['Instagram', 'https://www.instagram.com/cenofex'],
  'social_facebook'  => ['Facebook',  'https://www.facebook.com/cenofex'],
  'social_youtube'   => ['YouTube',   'https://www.youtube.com/@cenofex'],
  'social_x'         => ['X (Twitter)', 'https://x.com/cenofex'],
];

$err = '';

/** Приводим ссылку к рабочему виду: без протокола браузер считает её относительной. */
function tidy_url(string $u): string
{
    $u = trim($u);
    if ($u === '' || $u === '#') return '';
    if (!preg_match('~^https?://~i', $u)) $u = 'https://' . ltrim($u, '/');
    return $u;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // 1. Двуязычные контакты
    foreach (['en', 'az'] as $lg) {
        foreach (array_keys($contactKeys) as $k) {
            if (isset($_POST[$lg][$k])) content_set($lg, $k, trim((string)$_POST[$lg][$k]));
        }
    }

    // 2. WhatsApp — храним как ввели, в ссылку уходят только цифры
    $wa = trim((string)($_POST['whatsapp'] ?? ''));
    if ($wa !== '' && preg_match('/\d/', $wa) === 0) {
        $err = 'В номере WhatsApp нет ни одной цифры.';
    } else {
        setting_set('whatsapp', $wa);
    }

    // 3. Ссылки на соцсети
    foreach (array_keys($socials) as $k) {
        setting_set($k, tidy_url((string)($_POST[$k] ?? '')));
    }

    if (!$err) { cache_clear(); header('Location: contacts?saved=1'); exit; }
}

$en = content_all('en');
$az = content_all('az');
$waNow = setting('whatsapp', '');
$waLink = preg_replace('/\D+/', '', $waNow !== '' ? $waNow : ($en['contact_phone'] ?? ''));
?>
<?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

<form method="post">
<?= csrf_field() ?>

<div class="card">
  <h2>Контакты на сайте</h2>
  <p class="hint">Слева — английская версия, справа — азербайджанская.
    Эти данные показываются в блоке «Контакты» и попадают в разметку для поисковиков.</p>

  <?php foreach ($contactKeys as $k => $label): ?>
    <div class="row" style="margin-bottom:12px">
      <div>
        <label><?= e($label) ?> <span class="badge gray">EN</span></label>
        <input type="text" name="en[<?= e($k) ?>]" value="<?= e($en[$k] ?? '') ?>">
      </div>
      <div>
        <label><?= e($label) ?> <span class="badge">AZ</span></label>
        <input type="text" name="az[<?= e($k) ?>]" value="<?= e($az[$k] ?? '') ?>">
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h2>WhatsApp</h2>
  <p class="hint">Номер для круглой кнопки в правом нижнем углу сайта.
    Пишите как удобно — в ссылку уйдут только цифры. Оставьте поле пустым,
    чтобы использовался телефон из контактов выше.</p>

  <div class="row">
    <div>
      <label>Номер WhatsApp</label>
      <input type="text" name="whatsapp" value="<?= e($waNow) ?>" placeholder="+994 50 393 98 12">
    </div>
    <div>
      <label>Ссылка получится такой</label>
      <input type="text" value="<?= $waLink !== '' ? 'https://wa.me/' . e($waLink) : 'кнопка скрыта' ?>" disabled>
    </div>
  </div>
</div>

<div class="card">
  <h2>Социальные сети</h2>
  <p class="hint">Иконки в блоке «Контакты» показываются только для заполненных полей.
    Протокол можно не писать — добавим сами.</p>

  <?php foreach ($socials as $k => [$label, $example]): ?>
    <div style="margin-bottom:12px">
      <label><?= e($label) ?></label>
      <input type="text" name="<?= e($k) ?>" value="<?= e(setting($k)) ?>" placeholder="<?= e($example) ?>">
    </div>
  <?php endforeach; ?>
</div>

<div class="card"><button class="btn" type="submit">Сохранить</button></div>
</form>
<?php require __DIR__ . '/partials/footer.php'; ?>
