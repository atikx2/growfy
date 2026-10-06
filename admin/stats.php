<?php
/** Counters / stats CRUD */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();
$table = DB_PREFIX . 'stats';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = post('_act');
    if ($act === 'save') {
        $id = (int) post('id');
        $data = [
            'value'      => field('value', 20),
            'suffix'     => field('suffix', 10, false),
            'label'      => field('label', 150),
            'sublabel'   => field('sublabel', 150, false),
            'icon'       => in_array(post('icon'), icon_names(), true) ? post('icon') : 'users',
            'sort_order' => (int) post('sort_order'),
            'active'     => isset($_POST['active']) ? 1 : 0,
        ];
        if ($data['value'] === '' || $data['label'] === '') {
            flash_set('error', 'Value and label are required.');
        } else {
            if ($id > 0) {
                db()->prepare("UPDATE $table SET value=?, suffix=?, label=?, sublabel=?, icon=?, sort_order=?, active=? WHERE id=?")
                    ->execute([...array_values($data), $id]);
                flash_set('success', 'Counter updated.');
            } else {
                db()->prepare("INSERT INTO $table (value, suffix, label, sublabel, icon, sort_order, active) VALUES (?,?,?,?,?,?,?)")
                    ->execute(array_values($data));
                flash_set('success', 'Counter added.');
            }
        }
    }
    if ($act === 'delete') {
        db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'Counter deleted.');
    }
    redirect('stats.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $_GET['edit'] === 'new'
        ? ['id' => 0, 'value' => '', 'suffix' => '+', 'label' => '', 'sublabel' => '', 'icon' => 'users', 'sort_order' => (q_count('stats') + 1) * 10, 'active' => 1]
        : q_one('stats', 'id = ?', [(int) $_GET['edit']]);
}
$items = q_all('stats');

admin_header('Counters', 'stats.php', $user);
?>
<div class="panel">
  <div class="panel__head">
    <h2><?= $edit ? ($edit['id'] ? 'Edit counter' : 'Add counter') : 'Animated counters' ?></h2>
    <?php if (!$edit): ?><a class="btn btn--primary btn--sm" href="stats.php?edit=new">+ Add counter</a><?php endif; ?>
  </div>
  <?php if ($edit): ?>
  <form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="_act" value="save">
    <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
    <div class="form-grid">
      <div class="form-field"><label>Value * (number)</label><input type="text" name="value" required maxlength="20" value="<?= h($edit['value']) ?>" placeholder="10"></div>
      <div class="form-field"><label>Suffix</label><input type="text" name="suffix" maxlength="10" value="<?= h($edit['suffix']) ?>" placeholder="k+ / % / +"></div>
      <div class="form-field"><label>Label *</label><input type="text" name="label" required maxlength="150" value="<?= h($edit['label']) ?>" placeholder="Happy Clients"></div>
      <div class="form-field"><label>Sub-label</label><input type="text" name="sublabel" maxlength="150" value="<?= h($edit['sublabel']) ?>" placeholder="Across 30+ countries"></div>
      <div class="form-field"><label>Icon</label><?php admin_icon_select('icon', $edit['icon']); ?></div>
      <div class="form-field"><label>Sort order</label><input type="number" name="sort_order" value="<?= (int) $edit['sort_order'] ?>"></div>
      <label class="switch form-field--full"><input type="checkbox" name="active" <?= $edit['active'] ? 'checked' : '' ?>><span class="switch__track"></span> Visible on website</label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary">Save counter</button>
      <a class="btn btn--ghost" href="stats.php">Cancel</a>
    </div>
  </form>
  <?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Counter</th><th>Label</th><th>Order</th><th>Status</th><th class="ta-r">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><div class="cell-main"><span class="cell-icon"><?= icon($it['icon']) ?></span><strong class="counter-cell"><?= h($it['value']) ?><?= h($it['suffix']) ?></strong></div></td>
          <td><?= h($it['label']) ?><br><small class="dim"><?= h($it['sublabel']) ?></small></td>
          <td><?= (int) $it['sort_order'] ?></td>
          <td><?= $it['active'] ? '<span class="pill pill--completed">Live</span>' : '<span class="pill pill--cancelled">Hidden</span>' ?></td>
          <td class="ta-r">
            <a class="icon-btn" href="stats.php?edit=<?= (int) $it['id'] ?>" title="Edit"><?= icon('edit') ?></a>
            <form method="post" class="inline" data-confirm="Delete this counter?">
              <?= csrf_field() ?>
              <input type="hidden" name="_act" value="delete">
              <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
              <button class="icon-btn icon-btn--danger" title="Delete"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="5" class="empty">No counters yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php admin_footer(); ?>
