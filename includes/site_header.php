<?php
/** Site <head> + topbar + navbar. */
$siteName = c('site_name');
$scheme   = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
$baseUrl  = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$canon    = $baseUrl . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$ver      = fn(string $f) => file_exists(GROWFY_ROOT . '/' . $f) ? filemtime(GROWFY_ROOT . '/' . $f) : '1';
$navItems = ['#home' => 'Home', '#services' => 'Services', '#about' => 'About', '#process' => 'Process', '#why' => 'Why Us', '#reviews' => 'Reviews', '#faq' => 'FAQ'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h(c('seo_title')) ?></title>
<meta name="description" content="<?= h(c('seo_description')) ?>">
<meta name="keywords" content="<?= h(c('seo_keywords')) ?>">
<meta name="theme-color" content="#070b12">
<link rel="canonical" href="<?= h($canon) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= h(c('seo_title')) ?>">
<meta property="og:description" content="<?= h(c('seo_description')) ?>">
<meta property="og:url" content="<?= h($canon) ?>">
<meta property="og:site_name" content="<?= h($siteName) ?>">
<meta property="og:image" content="<?= h($baseUrl) ?>/assets/img/og.jpg">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/site.css?v=<?= $ver('assets/css/site.css') ?>">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": <?= json_encode($siteName) ?>,
  "url": <?= json_encode($baseUrl . '/') ?>,
  "logo": <?= json_encode($baseUrl . '/favicon.svg') ?>,
  "description": <?= json_encode(c('seo_description')) ?>,
  "sameAs": <?= json_encode(array_values(array_filter([
        c('social_facebook'), c('social_instagram'), c('social_twitter'),
        c('social_linkedin'), c('social_youtube'), c('social_tiktok')
      ], fn($u) => $u && $u !== '#'))) ?>,
  "contactPoint": {
    "@type": "ContactPoint",
    "email": <?= json_encode(c('contact_email')) ?>,
    "telephone": <?= json_encode(c('contact_phone')) ?>,
    "contactType": "sales"
  }
}
</script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<div class="topbar">
  <div class="container topbar__in">
    <div class="topbar__contact">
      <a href="mailto:<?= h(c('contact_email')) ?>"><?= icon('mail') ?><span><?= h(c('contact_email')) ?></span></a>
      <a href="tel:<?= h(preg_replace('/[^+\d]/', '', c('contact_phone'))) ?>"><?= icon('phone') ?><span><?= h(c('contact_phone')) ?></span></a>
    </div>
    <div class="topbar__social">
      <?php foreach (['facebook','instagram','twitter','linkedin','youtube','tiktok'] as $sn):
        $u = c('social_' . $sn); if (!$u || $u === '#') continue; ?>
        <a href="<?= h($u) ?>" target="_blank" rel="noopener" aria-label="<?= h($sn) ?>"><?= icon($sn) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<header class="navbar" id="navbar">
  <div class="container nav">
    <a class="brand" href="#home" aria-label="<?= h($siteName) ?> home">
      <span class="brand__mark"><?php include __DIR__ . '/logo.php'; ?></span>
      <span class="brand__name"><?= h($siteName) ?></span>
    </a>
    <nav class="nav-links" id="navLinks" aria-label="Primary">
      <?php foreach ($navItems as $href => $label): ?>
        <a href="<?= h($href) ?>" class="nav-link"><?= h($label) ?></a>
      <?php endforeach; ?>
      <a class="btn btn--primary btn--sm nav-links__cta" href="#contact">Get Started</a>
    </nav>
    <div class="nav-right">
      <a class="btn btn--primary btn--sm nav-cta" href="#contact">Get Started</a>
      <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<main id="main">
