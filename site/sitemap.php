<?php
/**
 * Карта сайта. Доступна как /sitemap.xml (правило в .htaccess).
 * Указывает обе языковые версии и связывает их через hreflang.
 */
require_once __DIR__ . '/app/helpers.php';

$siteUrl = rtrim(cfg('site.url'), '/');
$enUrl   = $siteUrl . cfg('site.en_path');
$azUrl   = $siteUrl . cfg('site.az_path');

/* Дата последнего изменения = когда в панели последний раз сохраняли контент */
$lastmod = date('Y-m-d');
try {
    $row = db()->query('SELECT GREATEST(
            COALESCE((SELECT MAX(id) FROM content), 0),
            COALESCE((SELECT MAX(id) FROM items), 0)
        ) AS v')->fetch();
    $cacheFile = cache_path('en');
    if (is_file($cacheFile)) $lastmod = date('Y-m-d', filemtime($cacheFile));
} catch (Throwable $e) { /* оставляем сегодняшнюю дату */ }

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
  <url>
    <loc><?= e($enUrl) ?></loc>
    <lastmod><?= e($lastmod) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
    <xhtml:link rel="alternate" hreflang="en" href="<?= e($enUrl) ?>"/>
    <xhtml:link rel="alternate" hreflang="az" href="<?= e($azUrl) ?>"/>
    <xhtml:link rel="alternate" hreflang="x-default" href="<?= e($enUrl) ?>"/>
  </url>
  <url>
    <loc><?= e($azUrl) ?></loc>
    <lastmod><?= e($lastmod) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
    <xhtml:link rel="alternate" hreflang="en" href="<?= e($enUrl) ?>"/>
    <xhtml:link rel="alternate" hreflang="az" href="<?= e($azUrl) ?>"/>
    <xhtml:link rel="alternate" hreflang="x-default" href="<?= e($enUrl) ?>"/>
  </url>
</urlset>
