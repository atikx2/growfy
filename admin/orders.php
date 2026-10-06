<?php
/** Order inquiries management */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();
$table = DB_PREFIX . 'orders';
$statuses = ['new' => 'New', 'contacted' => 'Contacted', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = post('_act');
    if ($act === 'status') {
        $st = post('status');
        if (isset($statuses[$st])) {
            db()->prepare("UPDATE $table SET status = ? WHERE id = ?")->execute([$st, (int) post('id')]);
            flash_set('success', 'Order status updated.');
        }
    }
    if ($act === 'delete') {
        db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'Order deleted.');
    }
    redirect('orders.php' . (isset($_GET['filter']) ? '?filter=' . urlencode((string) $_GET['filter']) : ''));
}

$filter = $_GET['filter'] ?? 'all';
$where = isset($statuses[$filter]) ? "status = '" . $filter . "'" : '1=1';
$items = q_all('orders', $where, 'id DESC');

admin_header('Orders', 'orders.php', $user);
?>
<div class="panel">
  <div class="panel__head">
    <h2>Order inquiries <span class="count-badge"><?= count($items) ?></span></h2>
    <div class="filters">
      <?php foreach (array_merge(['all' => 'All'], $statuses) as $k => $label): ?>
        <a class="filter-chip <?= $filter === $k ? 'is-active' : '' ?>" href="orders.php?filter=<?= h($k) ?>"><?= h($label) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>#</th><th>Service / Package</th><th>Client</th><th>Contact / Link</th><th>Date</th><th>Status</th><th class="ta-r">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td class="dim">#<?= (int) $it['id'] ?></td>
          <td><strong><?= h($it['service_name']) ?></strong><br><small class="dim"><?= h($it['package']) ?> package</small></td>
          <td><?= h($it['name']) ?><?php if ($it['notes']): ?><br><small class="dim" title="<?= h($it['notes']) ?>">📝 <?= h(mb_strimwidth((string) $it['notes'], 0, 40, '…')) ?></small><?php endif; ?></td>
          <td>
            <small><?= h($it['contact']) ?></small>
            <?php if ($it['link']): ?><br><a class="link" href="<?= h($it['link']) ?>" target="_blank" rel="noopener"><small><?= h(mb_strimwidth((string) $it['link'], 0, 38, '…')) ?></small></a><?php endif; ?>
          </td>
          <td><small class="dim"><?= h(time_ago($it['created_at'])) ?></small></td>
          <td>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="_act" value="status">
              <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
              <select name="status" class="status-sel status-sel--<?= h($it['status']) ?>" data-autosubmit>
                <?php foreach ($statuses as $k => $label): ?>
                  <option value="<?= h($k) ?>" <?= $it['status'] === $k ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td class="ta-r">
            <form method="post" class="inline" data-confirm="Delete this order permanently?">
              <?= csrf_field() ?>
              <input type="hidden" name="_act" value="delete">
              <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
              <button class="icon-btn icon-btn--danger" title="Delete"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="7" class="empty">No orders yet — they arrive when visitors use the “Order Now” buttons.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php admin_footer(); ?>
