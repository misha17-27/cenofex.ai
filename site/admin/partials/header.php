<?php
/** Шапка админ-панели: подключение ядра, проверка доступа, боковое меню. */
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/adminlang.php';

$user = require_login();
ob_start();                       // весь вывод переводим в footer.php
$page = $page ?? '';
$title = $title ?? 'Панель';

function nav_item(string $href, string $key, string $label, string $svg, string $current): void
{
    $active = $key === $current ? ' active' : '';
    echo '<a class="item' . $active . '" href="' . e($href) . '"><svg viewBox="0 0 24 24">' . $svg . '</svg>' . e($label) . '</a>';
}
?><!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> — CENOFEX</title>
<link rel="icon" type="image/png" href="../images/brand-icon.png">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('/admin/assets/admin.css', '')) ?>">
</head>
<body>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <div class="brand"><img src="../images/logo-white-text.svg" alt="CENOFEX"></div>
    <nav>
      <div class="grp">Основное</div>
      <?php nav_item('/admin/', 'dashboard', 'Обзор',
        '<rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/>', $page); ?>

      <div class="grp">Контент</div>
      <?php
      nav_item('content', 'content', 'Тексты сайта',
        '<path d="M4 5h16M4 12h16M4 19h10"/>', $page);
      nav_item('items', 'items', 'Услуги и решения',
        '<rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="8" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/><rect x="13" y="13" width="8" height="8" rx="2"/>', $page);
      nav_item('partners', 'partners', 'Партнёры',
        '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 3.5a3 3 0 0 1 0 9"/><path d="M18 20a6 6 0 0 0-3-5.2"/>', $page);
      nav_item('media', 'media', 'Изображения',
        '<rect x="3" y="4" width="18" height="16" rx="3"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="m4 17 5-5 4 4 3-2 4 4"/>', $page);
      nav_item('contacts', 'contacts', 'Контакты и соцсети',
        '<path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h2A1.5 1.5 0 0 1 9 5.5v2A1.5 1.5 0 0 1 7.5 9C7.5 13 11 16.5 15 16.5A1.5 1.5 0 0 1 16.5 15h2A1.5 1.5 0 0 1 20 16.5v2a1.5 1.5 0 0 1-1.5 1.5C10.5 20 4 13.5 4 5.5z"/>', $page);
      nav_item('messages', 'messages', 'Заявки с сайта',
        '<path d="M4 5h16v12H7l-3 3z"/><path d="M8 9h8M8 13h5"/>', $page);
      ?>

      <div class="grp">Настройки</div>
      <?php
      nav_item('seo', 'seo', 'SEO',
        '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>', $page);
      nav_item('mail', 'mail', 'Почта (SMTP)',
        '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>', $page);
      nav_item('security', 'security', 'Безопасность',
        '<path d="M12 3l7 3v6c0 4.4-3 7.6-7 9-4-1.4-7-4.6-7-9V6l7-3z"/><path d="m9 12 2 2 4-4"/>', $page);
      nav_item('users', 'users', 'Пользователи',
        '<circle cx="12" cy="8" r="3.2"/><path d="M5 20a7 7 0 0 1 14 0"/>', $page);
      nav_item('profile', 'profile', 'Мой профиль',
        '<circle cx="12" cy="8" r="3.2"/><path d="M5 20a7 7 0 0 1 14 0"/>', $page);
      ?>
    </nav>
    <div class="foot">
      Вы вошли как<br><strong style="color:#fff"><?= e($user['name']) ?></strong>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <button class="burger" id="burger" aria-label="Меню"><span></span><span></span><span></span></button>
      <h1><?= e($title) ?></h1>
      <div class="right">
        <?= admin_lang_switch() ?>
        <a class="btn ghost sm" href="../index5.php" target="_blank" rel="noopener">Открыть сайт</a>
        <a class="btn ghost sm" href="logout">Выйти</a>
      </div>
    </div>
    <div class="content">
<?php if (!empty($_GET['saved'])): ?>
      <div class="alert ok">Сохранено. Изменения уже на сайте.</div>
<?php endif; ?>
