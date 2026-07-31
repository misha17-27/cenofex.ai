<?php
/**
 * Шаблон публичной страницы. Данные — из БД.
 * Результат кэшируется в storage/cache/page_{lang}.html и отдаётся как статика.
 */
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/captcha.php';

if (!isset($lang) || !in_array($lang, ['en', 'az'], true)) $lang = 'en';

/* Сообщения после отправки формы — такие страницы не кэшируем */
$notice = '';
if (isset($_GET['sent']))       $notice = 'ok';
elseif (isset($_GET['error']))  $notice = 'err';
$useCache = ($notice === '');

/* ---- отдать готовый кэш, если есть ---- */
$cacheFile = cache_path($lang);
$ttl = (int)cfg('cache_ttl');
if ($useCache && is_file($cacheFile) && ($ttl === 0 || (time() - filemtime($cacheFile)) < $ttl)) {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Cache: HIT');
    readfile($cacheFile);
    exit;
}

/* ---- данные ---- */
$c        = content_all($lang);
$services = items('services', $lang);
$solutions= items('solutions', $lang);
$finance  = items('finance', $lang);
$hr       = items('hr', $lang);
$plist    = partners();

$seoTitle = setting("seo_title_{$lang}", 'CENOFEX');
$seoDesc  = setting("seo_desc_{$lang}", '');
$ogImage  = setting('og_image', '/images/logo-white-text.png');
$siteUrl  = rtrim(cfg('site.url'), '/');
$enPath   = cfg('site.en_path');
$azPath   = cfg('site.az_path');
$base     = ($lang === 'az') ? '..' : '.';     // корректные пути к картинкам из /az/
$canonical = $siteUrl . ($lang === 'az' ? $azPath : $enPath);

$svgSvc = [
  '<svg viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>',
  '<svg viewBox="0 0 24 24"><rect x="7" y="7" width="10" height="10" rx="2"/><path d="M10 3v2M14 3v2M10 19v2M14 19v2M3 10h2M3 14h2M19 10h2M19 14h2"/></svg>',
  '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3.2"/><path d="M12 4.2v2M12 17.8v2M4.2 12h2M17.8 12h2M6.6 6.6l1.4 1.4M16 16l1.4 1.4M17.4 6.6L16 8M8 16l-1.4 1.4"/></svg>',
  '<svg viewBox="0 0 24 24"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z"/><path d="M12 12l8-4.5M12 12v9M12 12L4 7.5"/></svg>',
];
$svgSol = [
  '<svg viewBox="0 0 24 24"><path d="M6 3h9l4 4v14H6z"/><path d="M15 3v4h4"/><path d="M9 13h7M9 17h5"/></svg>',
  '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M17 11h4M19 9v4"/></svg>',
  '<svg viewBox="0 0 24 24"><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="M8.2 10.9 15.8 7.1M8.2 13.1 15.8 16.9"/></svg>',
  '<svg viewBox="0 0 24 24"><rect x="7" y="7" width="10" height="10" rx="2"/><path d="M10 3v2M14 3v2M10 19v2M14 19v2M3 10h2M3 14h2M19 10h2M19 14h2"/></svg>',
];

/* Слайды hero */
$slides = [
  ['k'=>'s1_kicker','a'=>'s1_ta','b'=>'s1_tb','sub'=>'s1_sub','cta'=>'cta_talk','ctaHref'=>'#contact',
   'alt'=>'cta_about','altHref'=>'#about','img'=>'photo_hero1','ct'=>'chip_t1','cs'=>'chip_s1',
   'def'=>'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1800&q=80','fb'=>'1.png'],
  ['k'=>'s2_kicker','a'=>'s2_ta','b'=>'s2_tb','sub'=>'s2_sub','cta'=>'cta_partners','ctaHref'=>'#technology',
   'alt'=>'cta_talk','altHref'=>'#contact','img'=>'photo_hero2','ct'=>'chip_t2','cs'=>'chip_s2',
   'def'=>'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1800&q=80','fb'=>'5.png'],
  ['k'=>'s3_kicker','a'=>'s3_ta','b'=>'s3_tb','sub'=>'s3_sub','cta'=>'cta_services','ctaHref'=>'#services',
   'alt'=>'cta_talk','altHref'=>'#contact','img'=>'photo_hero3','ct'=>'chip_t3','cs'=>'chip_s3',
   'def'=>'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1800&q=80','fb'=>'3.png'],
];

/** Набор размеров для картинок Unsplash — мобильные грузят маленькую версию. */
function img_srcset(string $url): string
{
    if (strpos($url, 'images.unsplash.com') === false) return '';
    $set = [];
    foreach ([480, 640, 960, 1280, 1600] as $w) {
        $u = preg_replace('/([?&])w=\d+/', '$1w=' . $w, $url);
        if (strpos($u, 'w=') === false) $u .= '&w=' . $w;
        $set[] = $u . ' ' . $w . 'w';
    }
    return implode(', ', $set);
}
/** Уменьшенная версия для мобильных — используется как основной src. */
function img_small(string $url, int $w = 960): string
{
    if (strpos($url, 'images.unsplash.com') === false) return $url;
    $u = preg_replace('/([?&])w=\d+/', '$1w=' . $w, $url);
    return preg_replace('/([?&])q=\d+/', '${1}q=72', $u);
}

ob_start();
?><!doctype html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<title><?= e($seoTitle) ?></title>
<meta name="description" content="<?= e($seoDesc) ?>">
<meta property="og:title" content="<?= e($seoTitle) ?>">
<meta property="og:description" content="<?= e($seoDesc) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="CENOFEX">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($siteUrl . $ogImage) ?>">
<meta property="og:image:alt" content="CENOFEX">
<meta property="og:locale" content="<?= $lang === 'az' ? 'az_AZ' : 'en_US' ?>">
<meta property="og:locale:alternate" content="<?= $lang === 'az' ? 'en_US' : 'az_AZ' ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($seoTitle) ?>">
<meta name="twitter:description" content="<?= e($seoDesc) ?>">
<meta name="twitter:image" content="<?= e($siteUrl . $ogImage) ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="theme-color" content="#02A78E">
<meta name="author" content="CENOFEX">
<link rel="canonical" href="<?= e($canonical) ?>">
<link rel="alternate" hreflang="en" href="<?= e($siteUrl . $enPath) ?>">
<link rel="alternate" hreflang="az" href="<?= e($siteUrl . $azPath) ?>">
<link rel="alternate" hreflang="x-default" href="<?= e($siteUrl . $enPath) ?>">
<link rel="icon" type="image/svg+xml" href="<?= $base ?>/images/brand-icon.svg">
<link rel="apple-touch-icon" href="<?= $base ?>/images/brand-icon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://images.unsplash.com" crossorigin>
<link rel="stylesheet" href="<?= e(asset('/assets/site.css', $base)) ?>">
<?php /* Шрифты грузим неблокирующе — страница рисуется сразу */ ?>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
      onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"></noscript>
<?php /* Предзагрузка главной картинки — ускоряет LCP */
      $lcp = setting($slides[0]['img'], $slides[0]['def']);
      $lcpSet = img_srcset($lcp); ?>
<link rel="preload" as="image" href="<?= e(img_small($lcp)) ?>"
      <?php if ($lcpSet): ?>imagesrcset="<?= e($lcpSet) ?>" imagesizes="(max-width:980px) 92vw, 46vw"<?php endif; ?>
      fetchpriority="high">
<?php
/* ---------- Структурированные данные для Google (schema.org) ---------- */
$sameAs = array_values(array_filter([
    setting('social_linkedin'), setting('social_instagram'), setting('social_facebook'),
    setting('social_youtube'), setting('social_x'),
], fn($u) => $u !== '' && $u !== '#'));

$org = [
    '@context' => 'https://schema.org',
    '@type'    => 'Organization',
    '@id'      => $siteUrl . '/#organization',
    'name'     => 'CENOFEX',
    'url'      => $siteUrl . '/',
    'logo'     => ['@type' => 'ImageObject', 'url' => $siteUrl . '/images/logo-dark-text.png'],
    'image'    => $siteUrl . $ogImage,
    'description' => $seoDesc,
    'address'  => ['@type' => 'PostalAddress', 'addressCountry' => 'AZ', 'addressLocality' => 'Baku'],
    'contactPoint' => [[
        '@type'       => 'ContactPoint',
        'contactType' => 'sales',
        'telephone'   => preg_replace('/\s+/', '', t($c, 'contact_phone', '+994503939812')),
        'email'       => t($c, 'contact_email', 'info@cenofex.ai'),
        'availableLanguage' => ['en', 'az'],
    ]],
];
if ($sameAs) $org['sameAs'] = $sameAs;

$website = [
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    '@id'      => $siteUrl . '/#website',
    'url'      => $siteUrl . '/',
    'name'     => 'CENOFEX',
    'inLanguage' => $lang,
    'publisher'  => ['@id' => $siteUrl . '/#organization'],
];

$webpage = [
    '@context'   => 'https://schema.org',
    '@type'      => 'WebPage',
    'url'        => $canonical,
    'name'       => $seoTitle,
    'description'=> $seoDesc,
    'inLanguage' => $lang,
    'isPartOf'   => ['@id' => $siteUrl . '/#website'],
    'about'      => ['@id' => $siteUrl . '/#organization'],
];

/* Услуги — отдельным списком */
$svcList = [];
foreach ($services as $n => $it) {
    $svcList[] = [
        '@type'    => 'ListItem',
        'position' => $n + 1,
        'item'     => [
            '@type'       => 'Service',
            'name'        => $it['title'],
            'description' => $it['body'],
            'provider'    => ['@id' => $siteUrl . '/#organization'],
        ],
    ];
}
$svcSchema = $svcList ? [
    '@context' => 'https://schema.org',
    '@type'    => 'ItemList',
    'name'     => t($c, 'services_title', 'Services'),
    'itemListElement' => $svcList,
] : null;

$FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
?>
<script type="application/ld+json"><?= json_encode($org, $FLAGS) ?></script>
<script type="application/ld+json"><?= json_encode($website, $FLAGS) ?></script>
<script type="application/ld+json"><?= json_encode($webpage, $FLAGS) ?></script>
<?php if ($svcSchema): ?>
<script type="application/ld+json"><?= json_encode($svcSchema, $FLAGS) ?></script>
<?php endif; ?>
<?= turnstile_script() ?>
</head>
<body>
<header>
  <nav class="nav" id="nav">
    <a class="logo" href="#home"><img src="<?= $base ?>/images/logo-dark-text.png" alt="CENOFEX"></a>
    <button class="menu" id="menu" aria-label="Menu" aria-expanded="false"><span></span><span></span><span></span></button>
    <div class="links">
      <a href="#about"><?= e(t($c,'nav_about','Who We Are')) ?></a>
      <a href="#services"><?= e(t($c,'nav_services','What We Do')) ?></a>
      <a href="#technology"><?= e(t($c,'nav_tech','Technology')) ?></a>
      <a href="#solutions"><?= e(t($c,'nav_solutions','Ready Solutions')) ?></a>
      <a href="#contact"><?= e(t($c,'nav_contact','Contact')) ?></a>
    </div>
    <div class="lang">
      <a href="<?= e($enPath) ?>" class="lang-btn<?= $lang==='en'?' active':'' ?>">EN</a>
      <a href="<?= e($azPath) ?>" class="lang-btn<?= $lang==='az'?' active':'' ?>">AZ</a>
    </div>
  </nav>
</header>

<main>
  <!-- HERO -->
  <section class="hero" id="home">
    <div class="hero-viewport">
      <div class="hero-track" id="heroTrack">
        <?php foreach ($slides as $i => $s):
          $img = setting($s['img'], $s['def']); ?>
        <div class="hero-slide<?= $i===0?' active':'' ?>">
          <div class="wrap hero-inner">
            <div>
              <span class="kicker"><?= e(t($c,$s['k'])) ?></span>
              <?php /* На странице должен быть один H1 — он у первого слайда, остальные H2 */ ?>
              <?php if ($i === 0): ?>
                <h1><span><?= e(t($c,$s['a'])) ?></span> <span class="hl"><?= e(t($c,$s['b'])) ?></span></h1>
              <?php else: ?>
                <h2 class="as-h1"><span><?= e(t($c,$s['a'])) ?></span> <span class="hl"><?= e(t($c,$s['b'])) ?></span></h2>
              <?php endif; ?>
              <p class="sub"><?= e(t($c,$s['sub'])) ?></p>
              <div class="hero-cta">
                <a class="btn" href="<?= e($s['ctaHref']) ?>"><?= e(t($c,$s['cta'])) ?></a>
                <a class="btn ghost" href="<?= e($s['altHref']) ?>"><?= e(t($c,$s['alt'])) ?></a>
              </div>
            </div>
            <div class="hero-visual">
              <img src="<?= e(img_small($img)) ?>"
                   <?php $ss = img_srcset($img); if ($ss): ?>srcset="<?= e($ss) ?>" sizes="(max-width:980px) 92vw, 46vw"<?php endif; ?>
                   width="1600" height="1100" decoding="async"
                   alt="<?= e(t($c,$s['ct'])) ?>" <?= $i===0?'loading="eager" fetchpriority="high"':'loading="lazy"' ?>
                   onerror="this.onerror=null;this.src='<?= $base ?>/images/<?= e($s['fb']) ?>';this.style.objectFit='contain';this.parentNode.style.background='#4C6971'">
              <div class="hero-chip"><img src="<?= $base ?>/images/brand-icon.svg" alt="">
                <div><b><?= e(t($c,$s['ct'])) ?></b><span><?= e(t($c,$s['cs'])) ?></span></div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="hero-ctrl">
        <button class="hero-arrow prev" type="button" aria-label="Previous">&lsaquo;</button>
        <div class="hero-dots" id="heroDots"></div>
        <button class="hero-arrow next" type="button" aria-label="Next">&rsaquo;</button>
      </div>
    </div>
  </section>

  <!-- ABOUT -->
  <section id="about">
    <div class="wrap about">
      <div class="reveal">
        <span class="kicker"><?= e(t($c,'about_label')) ?></span>
        <h2 class="title"><?= e(t($c,'about_title')) ?></h2>
        <div class="tech-copy">
          <p><?= e(t($c,'about_p1')) ?></p>
          <p><?= e(t($c,'about_p2')) ?></p>
          <p><?= e(t($c,'about_p3')) ?></p>
        </div>
      </div>
      <div class="about-media reveal">
        <?php $ab = setting('photo_about', 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=1800&q=80'); ?>
        <img src="<?= e(img_small($ab)) ?>"
             <?php $ssa = img_srcset($ab); if ($ssa): ?>srcset="<?= e($ssa) ?>" sizes="(max-width:980px) 92vw, 46vw"<?php endif; ?>
             width="1600" height="1200" decoding="async"
             alt="<?= e(t($c,'about_title')) ?>" loading="lazy"
             onerror="this.onerror=null;this.src='<?= $base ?>/images/4.png';this.style.objectFit='contain';this.parentNode.style.background='#4C6971'">
      </div>
    </div>
  </section>

  <!-- BRAND CONCEPT: знак из 4 частей -->
  <?php
  $pillars = [
    ['n' => '01', 'key' => 'pil1', 'def' => 'Consultation', 'seg' => 'p1'],
    ['n' => '02', 'key' => 'pil2', 'def' => 'Technology',   'seg' => 'p2'],
    ['n' => '03', 'key' => 'pil3', 'def' => 'Excellence',   'seg' => 'p3'],
    ['n' => '04', 'key' => 'pil4', 'def' => 'Trust',        'seg' => 'p4'],
  ];
  ?>
  <section id="concept">
    <div class="wrap concept">
      <div class="concept-list reveal">
        <span class="kicker"><?= e(t($c,'concept_label', $lang === 'az' ? 'Loqonun mənası' : 'The mark')) ?></span>
        <h2 class="title"><?= e(t($c,'concept_title', $lang === 'az'
            ? 'Dörd hissə. Bir transformasiya.'
            : 'Four parts. One transformation.')) ?></h2>

        <div class="pill-rows" id="pillRows">
          <?php foreach ($pillars as $i => $p):
                $desc = t($c, $p['key'] . '_desc', ''); ?>
            <button type="button" class="pill-row<?= $i === 0 ? ' on' : '' ?>" data-seg="<?= e($p['seg']) ?>">
              <span class="pill-dot"></span>
              <span class="pill-txt">
                <em><?= e($p['n']) ?></em>
                <b><?= e(t($c, $p['key'], $p['def'])) ?></b>
                <?php if ($desc !== ''): ?><i><?= e($desc) ?></i><?php endif; ?>
              </span>
            </button>
          <?php endforeach; ?>
        </div>

        <div class="pil-sum">
          <img src="<?= $base ?>/images/brand-icon.svg" alt="">
          <b><?= e(t($c,'pil_sum')) ?></b>
        </div>
      </div>

      <div class="concept-mark reveal" id="conceptMark" data-active="p1">
        <div class="mark-stage" role="img" aria-label="CENOFEX">
          <?php foreach (['p1','p2','p3','p4'] as $seg): ?>
            <span class="petal <?= $seg ?>" aria-hidden="true"></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- SERVICES -->
  <section class="band" id="services">
    <div class="wrap">
      <div class="head reveal">
        <span class="kicker"><?= e(t($c,'services_label')) ?></span>
        <h2 class="title"><?= e(t($c,'services_title')) ?></h2>
      </div>
      <div class="cards reveal">
        <?php foreach ($services as $i => $it): ?>
        <article class="card">
          <div class="icon"><?= $svgSvc[$i % count($svgSvc)] ?></div>
          <h3><?= e($it['title']) ?></h3><p><?= e($it['body']) ?></p>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- TECHNOLOGY -->
  <section id="technology">
    <div class="wrap">
      <div class="head reveal">
        <span class="kicker"><?= e(t($c,'tech_label')) ?></span>
        <h2 class="title"><?= e(t($c,'tech_title')) ?></h2>
      </div>
      <div class="tech-copy reveal">
        <p><?= e(t($c,'tech_p1')) ?></p>
        <p><?= e(t($c,'tech_p2')) ?></p>
        <p><?= e(t($c,'tech_p3')) ?></p>
      </div>
      <?php if ($plist): ?>
      <div class="group-head reveal" style="margin-top:clamp(30px,4vw,48px);margin-bottom:0">
        <h3><?= e(t($c,'partners_label','Our Official Partners')) ?></h3>
      </div>
      <div class="marquee reveal">
        <div class="marquee-track">
          <?php for ($pass = 0; $pass < 2; $pass++): foreach ($plist as $p): ?>
            <div class="partner"><img src="<?= e($p['logo']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></div>
          <?php endforeach; endfor; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- SOLUTIONS -->
  <section class="band" id="solutions">
    <div class="wrap">
      <div class="head reveal">
        <h2 class="title"><?= e(t($c,'ready_title')) ?></h2>
        <p class="lead"><?= e(t($c,'ready_intro')) ?></p>
      </div>

      <?php if ($finance): ?>
      <div class="group reveal">
        <div class="group-head"><h3><?= e(t($c,'grp_fin','Finance & Tax')) ?></h3></div>
        <div class="ready">
          <?php foreach ($finance as $it): ?>
          <article class="ready-item"><h4><?= e($it['title']) ?></h4><p><?= e($it['body']) ?></p></article>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($hr): ?>
      <div class="group reveal">
        <div class="group-head"><h3><?= e(t($c,'grp_hr','Human Resources')) ?></h3></div>
        <div class="ready">
          <?php foreach ($hr as $it): ?>
          <article class="ready-item"><h4><?= e($it['title']) ?></h4><p><?= e($it['body']) ?></p></article>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="note reveal">
        <div>
          <h3><?= e(t($c,'note_title')) ?></h3>
          <p><?= e(t($c,'note_text')) ?></p>
        </div>
        <a class="btn" href="#contact"><?= e(t($c,'cta_demo','Request a Demo')) ?></a>
      </div>
    </div>
  </section>

  <!-- CONTACT -->
  <section id="contact">
    <div class="wrap contact">
      <div class="contact-info reveal">
        <span class="kicker" style="color:var(--green)"><?= e(t($c,'contact_label')) ?></span>
        <h2 class="title"><?= e(t($c,'contact_title')) ?></h2>
        <p class="lead"><?= e(t($c,'contact_intro')) ?></p>
        <div style="margin-top:14px">
          <div class="detail"><div class="di"><svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6A2 2 0 0 1 22 16.9z"/></svg></div>
            <div><strong><?= e(t($c,'phone_label','Phone')) ?></strong>
              <a href="tel:<?= e(preg_replace('/\s+/', '', t($c,'contact_phone'))) ?>"><span><?= e(t($c,'contact_phone')) ?></span></a></div></div>
          <div class="detail"><div class="di"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div>
            <div><strong><?= e(t($c,'email_label','E-mail')) ?></strong>
              <a href="mailto:<?= e(t($c,'contact_email')) ?>"><span><?= e(t($c,'contact_email')) ?></span></a></div></div>
          <div class="detail"><div class="di"><svg viewBox="0 0 24 24"><path d="M12 21s-7-6.3-7-11a7 7 0 0 1 14 0c0 4.7-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></div>
            <div><strong><?= e(t($c,'address_label','Address')) ?></strong><span><?= e(t($c,'contact_address')) ?></span></div></div>
        </div>
        <div class="social">
          <?php
          /* Показываем только те сети, которые заполнены в админ-панели. */
          $nets = [
            'linkedin'  => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v6M8 7v.01M12 16v-3a2 2 0 0 1 4 0v3"/>',
            'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/>',
            'facebook'  => '<path d="M14 8h3V4h-3c-3 0-5 2-5 5v3H6v4h3v4h4v-4h3l1-4h-4V9c0-.6.4-1 1-1z"/>',
            'youtube'   => '<rect x="2.5" y="5" width="19" height="14" rx="4"/><path d="m10.5 9.5 5 2.5-5 2.5z"/>',
            'x'         => '<path d="m4 4 16 16M20 4 4 20"/>',
          ];
          foreach ($nets as $netKey => $svg):
                $u = trim(setting('social_' . $netKey, ''));
                if ($u === '' || $u === '#') continue; ?>
            <a href="<?= e($u) ?>" aria-label="<?= e($netKey === 'x' ? 'X' : ucfirst($netKey)) ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><?= $svg ?></svg></a>
          <?php endforeach; ?>
        </div>
      </div>

      <form class="form reveal" method="post" action="<?= $base ?>/contact-send.php">
        <?php if ($notice === 'ok'): ?>
          <div class="form-msg ok"><?= e(t($c, 'form_ok', $lang === 'az'
              ? 'Mesajınız göndərildi. Tezliklə əlaqə saxlayacağıq.'
              : 'Thank you — your message has been sent.')) ?></div>
        <?php elseif ($notice === 'err'): ?>
          <div class="form-msg err"><?php
            $code = (string)($_GET['error'] ?? '1');
            if ($code === '2') {
                echo e($lang === 'az'
                  ? 'Çox sayda müraciət göndərilib. Zəhmət olmasa bir azdan yenidən cəhd edin.'
                  : 'Too many messages sent. Please try again later.');
            } elseif ($code === '3') {
                echo e($lang === 'az'
                  ? '«Mən robot deyiləm» yoxlamasını tamamlayın.'
                  : 'Please complete the “I’m not a robot” check.');
            } else {
                echo e($lang === 'az'
                  ? 'Zəhmət olmasa ad, telefon və mesaj xanalarını düzgün doldurun.'
                  : 'Please fill in your name, phone and message correctly.');
            }
          ?></div>
        <?php endif; ?>
        <input type="hidden" name="lang" value="<?= e($lang) ?>">
        <input type="hidden" name="ts" value="<?= time() ?>">
        <div style="position:absolute;left:-9999px" aria-hidden="true">
          <input name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="field"><label for="cf-name"><?= e(t($c,'name_label','Name')) ?> *</label>
          <input id="cf-name" name="name" required maxlength="120" placeholder="<?= e(t($c,'name_ph')) ?>"></div>

        <div class="field"><label for="cf-phone"><?= e(t($c,'phone_form_label', $lang==='az'?'Telefon':'Phone')) ?> *</label>
          <input id="cf-phone" name="phone" type="tel" required maxlength="40"
                 inputmode="tel" autocomplete="tel"
                 pattern="[0-9()+\-\s]{7,40}"
                 placeholder="<?= e(t($c,'phone_ph','+994 __ ___ __ __')) ?>"></div>

        <div class="field"><label for="cf-email"><?= e(t($c,'email_label2','E-mail')) ?></label>
          <input id="cf-email" type="email" name="email" maxlength="190"
                 inputmode="email" autocomplete="email" spellcheck="false" autocapitalize="off"
                 pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}"
                 placeholder="<?= e(t($c,'email_ph')) ?>"></div>

        <div class="field"><label for="cf-msg"><?= e(t($c,'message_label','Message')) ?></label>
          <textarea id="cf-msg" name="message" maxlength="4000" placeholder="<?= e(t($c,'message_ph')) ?>"></textarea></div>

        <?= turnstile_widget() ?>
        <button class="btn" type="submit"><?= e(t($c,'send','Send Message')) ?></button>
      </form>
    </div>
  </section>
</main>

<footer>
  <div class="foot">
    <img src="<?= $base ?>/images/logo-white-text.png" alt="CENOFEX">
    <div class="foot-links">
      <a href="#about"><?= e(t($c,'nav_about')) ?></a>
      <a href="#services"><?= e(t($c,'nav_services')) ?></a>
      <a href="#technology"><?= e(t($c,'nav_tech')) ?></a>
      <a href="#solutions"><?= e(t($c,'nav_solutions')) ?></a>
      <a href="#contact"><?= e(t($c,'nav_contact')) ?></a>
    </div>
    <p><?= e(t($c,'copyright')) ?>
      <span class="by">Site by <a href="https://webline.az/" target="_blank" rel="noopener">Webline</a></span>
    </p>
  </div>
</footer>
<?php
/* Плавающая кнопка WhatsApp. Номер берём из настроек, оставляем только цифры. */
$waNum = preg_replace('/\D+/', '', setting('whatsapp', setting('contact_phone', '+994 50 393 98 12')));
?>
<?php if ($waNum !== ''): ?>
<a class="wa-btn" href="https://wa.me/<?= e($waNum) ?>" target="_blank" rel="noopener"
   aria-label="WhatsApp" title="WhatsApp">
  <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor" aria-hidden="true">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
  </svg>
</a>
<?php endif; ?>
<button class="to-top" id="toTop" type="button" aria-label="<?= $lang==='az' ? 'Yuxarı qalx' : 'Back to top' ?>">
  <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
       stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M12 19V5"/><path d="M5 12l7-7 7 7"/>
  </svg>
</button>
<script src="<?= e(asset('/assets/site.js', $base)) ?>" defer></script>
</body>
</html>
<?php
$html = ob_get_clean();

/* ---- сохранить статическую копию ---- */
if ($useCache) {
    if (!is_dir(cfg('paths.cache'))) @mkdir(cfg('paths.cache'), 0775, true);
    @file_put_contents($cacheFile, $html);
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Cache: MISS');
echo $html;
