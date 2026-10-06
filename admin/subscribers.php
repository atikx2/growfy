<?php
/** Newsletter subscribers */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();
$table = DB_PREFIX . 'subscribers';

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="subscribers-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Email', 'Subscribed at']);
    foreach (q_all('subscribers', '1=1', 'id DESC') as $s) {
        fputcsv($out, [$s['email'], $s['created_at']]);
    }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (post('_act') === 'delete') {
        db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'Subscriber removed.');
    }
    redirect('subscribers.php');
}

$items = q_all('subscribers', '1=1', 'id DESC');

admin_header('Subscribers', 'subscribers.php', $user);
?>
<div class="panel">
  <div class="panel__head">
    <h2>Newsletter subscribers <span class="count-badge"><?= count($items) ?></span></h2>
    <a class="btn btn--ghost btn--sm" href="subscribers.php?export=1"><?= icon('download') ?> Export CSV</a>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Email</th><th>Subscribed</th><th class="ta-r">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><div class="cell-main"><span class="cell-icon"><?= icon('mail') ?></span><strong><?= h($it['email']) ?></strong></div></td>
          <td><small class="dim"><?= h($it['created_at']) ?></small></td>
          <td class="ta-r">
            <form method="post" class="inline" data-confirm="Remove this subscriber?">
              <?= csrf_field() ?>
              <input type="hidden" name="_act" value="delete">
              <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
              <button class="icon-btn icon-btn--danger" title="Delete"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="3" class="empty">No subscribers yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php admin_footer(); ?>
