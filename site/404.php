<?php
/** Страница «не найдено» — с правильным кодом ответа 404 для Google. */
http_response_code(404);
require_once __DIR__ . '/app/helpers.php';
$siteUrl = rtrim(cfg('site.url'), '/');
$home    = cfg('site.en_path');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Page not found — CENOFEX</title>
<meta name="robots" content="noindex, follow">
<link rel="icon" type="image/svg+xml" href="/images/brand-icon.svg">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{--green:#02A78E;--teal:#048282;--ink:#0c1615}
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;display:grid;place-items:center;text-align:center;padding:24px;
    font-family:Manrope,system-ui,Arial,sans-serif;color:#fff;
    background:radial-gradient(900px 520px at 50% -10%,rgba(2,167,142,.22),transparent 62%),var(--ink)}
  img.logo{width:min(260px,70vw);margin:0 auto 32px}
  b.code{display:block;font-size:clamp(64px,14vw,120px);line-height:1;font-weight:800;
    background:linear-gradient(120deg,#02A78E,#048282);-webkit-background-clip:text;background-clip:text;color:transparent}
  h1{font-size:clamp(22px,4vw,32px);margin:12px 0 10px;font-weight:800}
  p{color:rgba(255,255,255,.7);margin:0 0 30px;font-size:16px}
  a.btn{display:inline-flex;align-items:center;min-height:52px;padding:0 28px;border-radius:999px;
    background:linear-gradient(120deg,#02A78E,#048282);color:#fff;font-weight:800;text-decoration:none;
    box-shadow:0 14px 34px rgba(2,167,142,.28)}
</style>
</head>
<body>
  <main>
    <img class="logo" src="/images/logo-white-text.svg" alt="CENOFEX">
    <b class="code">404</b>
    <h1>Page not found</h1>
    <p>The page you are looking for doesn’t exist or has been moved.</p>
    <a class="btn" href="<?= e($home) ?>">Back to homepage</a>
  </main>
</body>
</html>
