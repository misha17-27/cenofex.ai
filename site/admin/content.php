<?php
$page = 'content'; $title = 'Тексты сайта';
require __DIR__ . '/partials/header.php';

/** Группировка ключей по секциям — чтобы редактировать было понятно. */
$sections = [
  'Меню и футер' => [
    'nav_about' => 'Пункт: О нас', 'nav_services' => 'Пункт: Услуги', 'nav_tech' => 'Пункт: Технологии',
    'nav_solutions' => 'Пункт: Решения', 'nav_contact' => 'Пункт: Контакты', 'copyright' => 'Копирайт',
  ],
  'Слайд 1 (Trust)' => [
    's1_kicker' => 'Надзаголовок', 's1_ta' => 'Заголовок — часть 1', 's1_tb' => 'Заголовок — часть 2 (зелёная)',
    's1_sub' => 'Подзаголовок', 'chip_t1' => 'Плашка — заголовок', 'chip_s1' => 'Плашка — подпись',
  ],
  'Слайд 2 (Technology)' => [
    's2_kicker' => 'Надзаголовок', 's2_ta' => 'Заголовок — часть 1', 's2_tb' => 'Заголовок — часть 2 (зелёная)',
    's2_sub' => 'Подзаголовок', 'chip_t2' => 'Плашка — заголовок', 'chip_s2' => 'Плашка — подпись',
  ],
  'Слайд 3 (CoE)' => [
    's3_kicker' => 'Надзаголовок', 's3_ta' => 'Заголовок — часть 1', 's3_tb' => 'Заголовок — часть 2 (зелёная)',
    's3_sub' => 'Подзаголовок', 'chip_t3' => 'Плашка — заголовок', 'chip_s3' => 'Плашка — подпись',
  ],
  'Кнопки' => [
    'cta_talk' => 'Связаться', 'cta_about' => 'О нас', 'cta_partners' => 'Партнёры',
    'cta_services' => 'Услуги', 'cta_demo' => 'Запросить демо', 'send' => 'Кнопка формы',
  ],
  'О компании' => [
    'about_label' => 'Надзаголовок', 'about_title' => 'Заголовок',
    'about_p1' => 'Абзац 1', 'about_p2' => 'Абзац 2', 'about_p3' => 'Абзац 3',
  ],
  'Знак: 4 части (блок «The mark»)' => [
    'concept_label' => 'Надзаголовок блока', 'concept_title' => 'Заголовок блока',
    'pil1' => '01 — название', 'pil1_desc' => '01 — описание (необязательно)',
    'pil2' => '02 — название', 'pil2_desc' => '02 — описание (необязательно)',
    'pil3' => '03 — название', 'pil3_desc' => '03 — описание (необязательно)',
    'pil4' => '04 — название', 'pil4_desc' => '04 — описание (необязательно)',
    'pil_sum' => 'Итоговая плашка (Together — Transformation)',
  ],
  'Услуги — заголовок' => [
    'services_label' => 'Надзаголовок', 'services_title' => 'Заголовок',
  ],
  'Технологии' => [
    'tech_label' => 'Надзаголовок', 'tech_title' => 'Заголовок',
    'tech_p1' => 'Абзац 1', 'tech_p2' => 'Абзац 2', 'tech_p3' => 'Абзац 3',
    'partners_label' => 'Заголовок блока партнёров',
  ],
  'Готовые решения' => [
    'sol_label' => 'Надзаголовок', 'sol_title' => 'Заголовок', 'sol_intro' => 'Вступление',
    'ready_title' => 'Заголовок «Ready to Deploy»', 'ready_intro' => 'Описание «Ready to Deploy»',
    'grp_fin' => 'Группа: Финансы и налоги', 'grp_hr' => 'Группа: HR',
    'note_title' => 'Плашка — заголовок', 'note_text' => 'Плашка — текст',
  ],
  'Контакты' => [
    'contact_label' => 'Надзаголовок', 'contact_title' => 'Заголовок', 'contact_intro' => 'Вступление',
    'phone_label' => 'Подпись «Телефон»', 'contact_phone' => 'Номер телефона',
    'email_label' => 'Подпись «E-mail»', 'contact_email' => 'Адрес почты',
    'address_label' => 'Подпись «Адрес»', 'contact_address' => 'Адрес',
    'name_label' => 'Форма: имя', 'email_label2' => 'Форма: e-mail', 'message_label' => 'Форма: сообщение',
    'name_ph' => 'Форма: подсказка имени', 'email_ph' => 'Форма: подсказка почты', 'message_ph' => 'Форма: подсказка сообщения',
  ],
];

$long = ['pil1_desc','pil2_desc','pil3_desc','pil4_desc','about_p1','about_p2','about_p3','tech_p1','tech_p2','tech_p3','sol_intro','ready_intro',
         'note_text','contact_intro','s1_sub','s2_sub','s3_sub'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['en', 'az'] as $lg) {
        foreach (($_POST[$lg] ?? []) as $k => $v) {
            content_set($lg, (string)$k, trim((string)$v));
        }
    }
    cache_clear();
    header('Location: content?saved=1'); exit;
}

$en = content_all('en');
$az = content_all('az');
?>
<form method="post">
<?= csrf_field() ?>
<div class="card">
  <h2>Тексты на двух языках</h2>
  <p class="hint">Слева — английский (основной), справа — азербайджанский. После сохранения сайт обновляется сразу.</p>
  <button class="btn" type="submit">Сохранить всё</button>
</div>

<?php foreach ($sections as $secName => $keys): ?>
<div class="card">
  <h2><?= e($secName) ?></h2>
  <?php foreach ($keys as $k => $label): ?>
    <div class="row" style="margin-bottom:12px">
      <div>
        <label><?= e($label) ?> <span class="badge gray">EN</span></label>
        <?php if (in_array($k, $long, true)): ?>
          <textarea name="en[<?= e($k) ?>]"><?= e($en[$k] ?? '') ?></textarea>
        <?php else: ?>
          <input type="text" name="en[<?= e($k) ?>]" value="<?= e($en[$k] ?? '') ?>">
        <?php endif; ?>
      </div>
      <div>
        <label><?= e($label) ?> <span class="badge">AZ</span></label>
        <?php if (in_array($k, $long, true)): ?>
          <textarea name="az[<?= e($k) ?>]"><?= e($az[$k] ?? '') ?></textarea>
        <?php else: ?>
          <input type="text" name="az[<?= e($k) ?>]" value="<?= e($az[$k] ?? '') ?>">
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>

<div class="card"><button class="btn" type="submit">Сохранить всё</button></div>
</form>
<?php require __DIR__ . '/partials/footer.php'; ?>
