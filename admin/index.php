<?php
/** Dashboard */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();

$counters = [
    ['Active Services',  q_count('services', 'active = 1'),       'spark'],
    ['New Orders',       q_count('orders', "status = 'new'"),     'bolt'],
    ['Unread Messages',  q_count('messages', 'is_read = 0'),      'mail'],
    ['Subscribers',      q_count('subscribers'),                  'users'],
    ['Testimonials',     q_count('testimonials', 'active = 1'),   'message'],
    ['FAQs',             q_count('faqs', 'active = 1'),           'search'],
];

// 14-day activity (messages + orders per day)
$days = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $days[$d] = 0;
}
$since = date('Y-m-d', strtotime('-13 days')) . ' 00:00:00';
foreach ([ 'messages' => 'created_at', 'orders' => 'created_at' ] as $tbl => $col) {
    $st = db()->query('SELECT substr(' . $col . ', 1, 10) d, COUNT(*) c FROM ' . DB_PREFIX . $tbl . " WHERE $col >= '$since' GROUP BY d");
    foreach ($st->fetchAll() as $row) {
        if (isset($days[$row['d']])) $days[$row['d']] += (int) $row['c'];
    }
}
$maxDay = max(1, max($days));

$recentMsgs  = q_all('messages', '1=1', 'id DESC');
$recentMsgs  = array_slice($recentMsgs, 0, 5);
$recentOrders = array_slice(q_all('orders', '1=1', 'id DESC'), 0, 5);

admin_header('Dashboard', 'index.php', $user);
?>
<div class="cards-grid">
  <?php foreach ($counters as [$label, $val, $ic]): ?>
  <div class="metric-card">
    <span class="metric-card__icon"><?= icon($ic) ?></span>
    <div>
      <strong><?= (int) $val ?></strong>
      <small><?= h($label) ?></small>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="panel">
  <div class="panel__head"><h2>Activity — last 14 days</h2></div>
  <div class="chart" role="img" aria-label="Bar chart of messages and orders per day">
    <?php foreach ($days as $d => $count): ?>
    <div class="chart__col" title="<?= h(date('M j', strtotime($d))) ?>: <?= (int) $count ?>">
      <span class="chart__bar" style="height: <?= round($count / $maxDay * 100) ?>%" data-v="<?= (int) $count ?>"></span>
      <small><?= h(date('j', strtotime($d))) ?></small>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="two-col">
  <div class="panel">
    <div class="panel__head">
      <h2>Latest messages</h2>
      <a class="btn btn--ghost btn--sm" href="inbox.php">View all</a>
    </div>
    <?php if (!$recentMsgs): ?><p class="empty">No messages yet.</p><?php endif; ?>
    <div class="mini-list">
      <?php foreach ($recentMsgs as $m): ?>
      <a class="mini-item <?= $m['is_read'] ? '' : 'is-new' ?>" href="inbox.php?view=<?= (int) $m['id'] ?>">
        <span class="admin-avatar"><?= h(initials($m['name'])) ?></span>
        <div class="mini-item__body">
          <strong><?= h($m['name']) ?></strong>
          <small><?= h(mb_strimwidth((string) $m['message'], 0, 70, '…')) ?></small>
        </div>
        <time><?= h(time_ago($m['created_at'])) ?></time>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head">
      <h2>Latest orders</h2>
      <a class="btn btn--ghost btn--sm" href="orders.php">View all</a>
    </div>
    <?php if (!$recentOrders): ?><p class="empty">No orders yet.</p><?php endif; ?>
    <div class="mini-list">
      <?php foreach ($recentOrders as $o): ?>
      <div class="mini-item">
        <span class="admin-avatar admin-avatar--alt"><?= h(initials($o['name'])) ?></span>
        <div class="mini-item__body">
          <strong><?= h($o['service_name']) ?> · <?= h($o['package']) ?></strong>
          <small><?= h($o['name']) ?> — <?= h($o['contact']) ?></small>
        </div>
        <span class="pill pill--<?= h($o['status']) ?>"><?= h(ucfirst($o['status'])) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel__head"><h2>Quick actions</h2></div>
  <div class="quick-actions">
    <a href="services.php?edit=new" class="quick-action"><?= icon('spark') ?>Add service</a>
    <a href="testimonials.php?edit=new" class="quick-action"><?= icon('message') ?>Add testimonial</a>
    <a href="faqs.php?edit=new" class="quick-action"><?= icon('search') ?>Add FAQ</a>
    <a href="content.php" class="quick-action"><?= icon('brush') ?>Edit homepage text</a>
    <a href="../index.php" target="_blank" class="quick-action"><?= icon('globe') ?>Preview website</a>
  </div>
</div>
<?php admin_footer(); ?>
