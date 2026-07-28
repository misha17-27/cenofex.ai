<?php
/**
 * /admin/ — если пользователь не вошёл, показываем форму входа.
 * Если вошёл — панель управления.
 */
require_once dirname(__DIR__) . '/app/auth.php';
require_once dirname(__DIR__) . '/app/captcha.php';

/* ================= НЕ ВОШЁЛ — форма входа ================= */
if (!current_user()) {

    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        if (!rate_limit_ok('login', 10, 15)) {
            $error = 'Слишком много попыток. Попробуйте через 15 минут.';
        } elseif (!turnstile_verify()) {
            $error = 'Не пройдена проверка «я не робот».';
        } elseif (attempt_login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
            header('Location: /admin/'); exit;
        } else {
            $error = 'Неверный e-mail или пароль.';
        }
    }
    ?><!doctype html>
    <html lang="ru"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Вход — CENOFEX</title>
    <link rel="icon" type="image/png" href="../images/brand-icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css?v=3">
    <?= turnstile_script() ?>
    </head><body>
    <div class="auth">
      <div class="auth-card">
        <img class="logo" src="../images/logo-dark-text.png" alt="CENOFEX">
        <h1>Вход в панель</h1>
        <p class="sub">Управление контентом сайта</p>

        <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
        <?php if (!empty($_GET['reset'])): ?><div class="alert ok">Пароль изменён. Войдите с новым паролем.</div><?php endif; ?>
        <?php if (!empty($_GET['bye'])): ?><div class="alert ok">Вы вышли из панели.</div><?php endif; ?>

        <form method="post">
          <?= csrf_field() ?>
          <div class="field"><label for="email">E-mail</label>
            <input id="email" type="email" name="email" required autofocus
                   value="<?= e($_POST['email'] ?? '') ?>"></div>

          <div class="field"><label for="password">Пароль</label>
            <div class="pw-wrap" style="position:relative">
              <input id="password" type="password" name="password" required style="padding-right:44px">
              <button type="button" class="pw-toggle" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:34px;height:34px;border:0;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#048282;border-radius:8px;padding:0" data-target="password" aria-label="Показать пароль">
                <svg class="eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M3 3l18 18"/><path d="M10.6 10.6a3 3 0 0 0 4.2 4.2"/><path d="M9.9 5.2A9.6 9.6 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.2 6.7A17 17 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4-.8"/></svg>
              </button>
            </div>
          </div>

          <?= turnstile_widget() ?>
          <button class="btn" type="submit">Войти</button>
        </form>

        <div class="auth-links"><a href="forgot-password">Забыли пароль?</a></div>
      </div>
    </div>
    <script src="assets/admin.js?v=3"></script>
    </body></html>
    <?php
    exit;
}

/* ================= ВОШЁЛ — панель управления ================= */
$page = 'dashboard'; $title = 'Обзор';
require __DIR__ . '/partials/header.php';

$counts = [
  'texts'    => (int)db()->query('SELECT COUNT(*) c FROM content')->fetch()['c'],
  'items'    => (int)db()->query('SELECT COUNT(*) c FROM items WHERE active=1')->fetch()['c'],
  'partners' => (int)db()->query('SELECT COUNT(*) c FROM partners WHERE active=1')->fetch()['c'],
  'users'    => (int)db()->query('SELECT COUNT(*) c FROM users WHERE active=1')->fetch()['c'],
];
try {
    $newMsg = (int)db()->query('SELECT COUNT(*) c FROM messages WHERE is_read=0')->fetch()['c'];
} catch (Throwable $e) { $newMsg = 0; }

$cacheEn = is_file(cache_path('en')); $cacheAz = is_file(cache_path('az'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_cache') {
    csrf_check(); cache_clear();
    header('Location: /admin/?saved=1'); exit;
}
?>
<div class="grid c4" style="margin-bottom:18px">
  <div class="stat"><b><?= $counts['texts'] ?></b><span>текстовых блоков</span></div>
  <div class="stat"><b><?= $counts['items'] ?></b><span>карточек услуг/решений</span></div>
  <div class="stat"><b><?= $counts['partners'] ?></b><span>партнёров</span></div>
  <div class="stat"><b><?= $newMsg ?></b><span>новых заявок</span></div>
</div>

<?php if (!turnstile_enabled()): ?>
  <div class="alert err">Капча не настроена — формы работают без защиты Cloudflare.
    Добавьте ключи в разделе <a href="security" style="font-weight:800">Безопасность</a>.</div>
<?php endif; ?>

<div class="grid c2">
  <div class="card">
    <h2>Быстрые действия</h2>
    <p class="hint">С чего обычно начинают.</p>
    <div class="row">
      <a class="btn" href="content">Редактировать тексты</a>
      <a class="btn ghost" href="partners">Добавить партнёра</a>
      <a class="btn ghost" href="messages">Заявки с сайта</a>
    </div>
  </div>

  <div class="card">
    <h2>Кэш страниц</h2>
    <p class="hint">Сайт отдаётся как статика — это делает его быстрым.
      Кэш обновляется сам при сохранении.</p>
    <p style="font-size:14px;margin:0 0 14px">
      Английская: <span class="badge<?= $cacheEn ? '' : ' gray' ?>"><?= $cacheEn ? 'готова' : 'будет создана' ?></span>
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
  <div class="row">
    <a class="btn ghost" href="../index5.php" target="_blank" rel="noopener">Английская версия</a>
    <a class="btn ghost" href="../az/" target="_blank" rel="noopener">Азербайджанская версия</a>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
