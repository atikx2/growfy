<?php
/** Admin layout: sidebar + topbar shell. */

function admin_asset(string $f): string
{
    $path = GROWFY_ROOT . '/' . $f;
    return '../' . $f . '?v=' . (file_exists($path) ? filemtime($path) : '1');
}

function admin_header(string $title, string $active, array $user): void
{
    $unread = q_count('messages', 'is_read = 0');
    $newOrders = q_count('orders', "status = 'new'");
    $nav = [
        ['index.php',        'Dashboard',    'chart',  null],
        ['content.php',      'Site Content', 'brush',  null],
        ['services.php',     'Services',     'spark',  null],
        ['steps.php',        'Process Steps','trend',  null],
        ['features.php',     'Why Us Cards', 'shield', null],
        ['stats.php',        'Counters',     'target', null],
        ['testimonials.php', 'Testimonials', 'message',null],
        ['faqs.php',         'FAQs',         'search', null],
        ['orders.php',       'Orders',       'bolt',   $newOrders],
        ['inbox.php',        'Inbox',        'mail',   $unread],
        ['subscribers.php',  'Subscribers',  'users',  null],
        ['settings.php',     'Settings',     'globe',  null],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($title) ?> · <?= h(c('site_name')) ?> Admin</title>
<meta name="robots" content="noindex,nofollow">
<link rel="icon" type="image/svg+xml" href="../favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h(admin_asset('assets/css/admin.css')) ?>">
</head>
<body class="admin">
<div class="admin-shell">
  <aside class="admin-side" id="adminSide">
    <a class="admin-brand" href="index.php">
      <span class="brand__mark"><?php include dirname(__DIR__) . '/includes/logo.php'; ?></span>
      <span>Growfy<small>Admin Panel</small></span>
    </a>
    <nav class="admin-nav">
      <?php foreach ($nav as [$href, $label, $ic, $badge]):
        $isActive = strpos($href, $active) === 0; ?>
        <a href="<?= h($href) ?>" class="admin-nav__link<?= $isActive ? ' is-active' : '' ?>">
          <?= icon($ic) ?><span><?= h($label) ?></span>
          <?php if ($badge): ?><em class="nav-badge"><?= (int) $badge ?></em><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-side__foot">
      <a href="../index.php" target="_blank" class="admin-nav__link"><?= icon('globe') ?><span>View Website</span></a>
      <a href="logout.php" class="admin-nav__link admin-nav__link--danger"><?= icon('logout') ?><span>Logout</span></a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-top">
      <button class="admin-burger" id="adminBurger" aria-label="Menu"><span></span><span></span><span></span></button>
      <h1><?= h($title) ?></h1>
      <div class="admin-top__user">
        <span class="admin-avatar"><?= h(initials($user['display_name'] ?: $user['username'])) ?></span>
        <div class="admin-top__meta">
          <strong><?= h($user['display_name'] ?: $user['username']) ?></strong>
          <small>Administrator</small>
        </div>
      </div>
    </header>
    <div class="admin-body">
      <?php foreach (flash_get() as $fl): ?>
        <div class="alert alert--<?= h($fl['type'] === 'error' ? 'error' : 'success') ?>"><?= h($fl['msg']) ?></div>
      <?php endforeach; ?>
    <?php
}

function admin_footer(): void
{
    ?>
    </div>
  </div>
</div>
<script src="<?= h(admin_asset('assets/js/admin.js')) ?>" defer></script>
</body>
</html>
<?php
}

/* ---- reusable CRUD form blocks ---- */

function admin_icon_select(string $name, string $current): void
{
    ?>
    <div class="icon-picker">
      <select name="<?= h($name) ?>" id="iconSel">
        <?php foreach (icon_names() as $n): ?>
          <option value="<?= h($n) ?>" <?= $n === $current ? 'selected' : '' ?>><?= h(ucfirst($n)) ?></option>
        <?php endforeach; ?>
      </select>
      <span class="icon-picker__preview" id="iconPrev"><?= icon($current) ?></span>
    </div>
    <script type="application/json" id="iconMap"><?= json_encode(array_combine(icon_names(), array_map(fn($n) => icon($n), icon_names())), JSON_UNESCAPED_SLASHES) ?></script>
    <?php
}
