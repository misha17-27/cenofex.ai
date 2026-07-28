<?php
require_once dirname(__DIR__) . '/app/auth.php';
require_admin();

$page = 'security'; $title = 'Безопасность';
require __DIR__ . '/partials/header.php';
require_once dirname(__DIR__) . '/app/captcha.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    setting_set('turnstile_site_key', trim($_POST['turnstile_site_key'] ?? ''));
    setting_set('turnstile_secret',   trim($_POST['turnstile_secret'] ?? ''));
    cache_clear();
    header('Location: security?saved=1'); exit;
}
?>
<div class="card">
  <h2>Cloudflare Turnstile (капча)</h2>
  <p class="hint">Защищает форму на сайте и вход в панель от ботов.
    Статус: <?= turnstile_enabled()
      ? '<span class="badge">включена</span>'
      : '<span class="badge gray">выключена — ключи не заданы</span>' ?></p>

  <form method="post">
    <?= csrf_field() ?>
    <div class="field">
      <label>Site Key (публичный ключ)</label>
      <input type="text" name="turnstile_site_key" value="<?= e(setting('turnstile_site_key')) ?>"
             placeholder="0x4AAAAAAA...">
    </div>
    <div class="field">
      <label>Secret Key (секретный ключ)</label>
      <div class="pw-wrap">
        <input id="tsecret" type="password" name="turnstile_secret" value="<?= e(setting('turnstile_secret')) ?>"
               placeholder="0x4AAAAAAA...">
        <button type="button" class="pw-toggle" data-target="tsecret" aria-label="Показать ключ">
          <svg class="eye" viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg class="eye-off" viewBox="0 0 24 24" style="display:none"><path d="M3 3l18 18"/><path d="M10.6 10.6a3 3 0 0 0 4.2 4.2"/><path d="M9.9 5.2A9.6 9.6 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.7A17 17 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4-.8"/></svg>
        </button>
      </div>
    </div>
    <button class="btn" type="submit">Сохранить</button>
  </form>
</div>

<div class="card">
  <h2>Где взять ключи</h2>
  <p class="hint" style="margin-bottom:10px">Turnstile бесплатен и не требует от посетителя разгадывать картинки.</p>
  <ol style="font-size:14.5px;line-height:1.9;padding-left:20px;margin:0">
    <li>Зайдите в <strong>dash.cloudflare.com</strong> → раздел <strong>Turnstile</strong></li>
    <li>Нажмите <strong>Add widget</strong></li>
    <li>Domain — укажите <code>cenofex.ai</code> (и <code>www.cenofex.ai</code>)</li>
    <li>Widget Mode — <strong>Managed</strong></li>
    <li>Скопируйте <strong>Site Key</strong> и <strong>Secret Key</strong> в поля выше</li>
  </ol>
</div>

<div class="card">
  <h2>Что уже защищено</h2>
  <ul style="font-size:14.5px;line-height:1.9;margin:0;padding-left:20px">
    <li>Пароли хранятся в виде необратимого хэша</li>
    <li>Все формы защищены от CSRF (подделки запросов)</li>
    <li>Ограничение попыток входа: 10 за 15 минут с одного IP</li>
    <li>Ограничение отправок формы: 5 за час с одного IP</li>
    <li>Скрытая ловушка для ботов в форме обратной связи</li>
    <li>Проверка типа файлов при загрузке, запрет выполнения PHP в папке загрузок</li>
    <li>Защита от подстановки заголовков в письмах</li>
  </ul>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
