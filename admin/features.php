<?php
/** "Why choose us" feature cards CRUD */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();
$table = DB_PREFIX . 'features';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = post('_act');
    if ($act === 'save') {
        $id = (int) post('id');
        $data = [
            'title'       => field('title', 150),
            'description' => field('description', 2000, false),
            'icon'        => in_array(post('icon'), icon_names(), true) ? post('icon') : 'spark',
            'sort_order'  => (int) post('sort_order'),
            'active'      => isset($_POST['active']) ? 1 : 0,
        ];
        if ($data['title'] === '') {
            flash_set('error', 'Title is required.');
        } else {
            if ($id > 0) {
                db()->prepare("UPDATE $table SET title=?, description=?, icon=?, sort_order=?, active=? WHERE id=?")
                    ->execute([...array_values($data), $id]);
                flash_set('success', 'Feature updated.');
            } else {
                db()->prepare("INSERT INTO $table (title, description, icon, sort_order, active) VALUES (?,?,?,?,?)")
                    ->execute(array_values($data));
                flash_set('success', 'Feature added.');
            }
        }
    }
    if ($act === 'delete') {
        db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'Feature deleted.');
    }
    redirect('features.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $_GET['edit'] === 'new'
        ? ['id' => 0, 'title' => '', 'description' => '', 'icon' => 'bolt', 'sort_order' => (q_count('features') + 1) * 10, 'active' => 1]
        : q_one('features', 'id = ?', [(int) $_GET['edit']]);
}
$items = q_all('features');

admin_header('Why Us Cards', 'features.php', $user);
?>
<div class="panel">
  <div class="panel__head">
    <h2><?= $edit ? ($edit['id'] ? 'Edit card' : 'Add new card') : '“Why Choose Growfy” cards' ?></h2>
    <?php if (!$edit): ?><a class="btn btn--primary btn--sm" href="features.php?edit=new">+ Add card</a><?php endif; ?>
  </div>
  <?php if ($edit): ?>
  <form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="_act" value="save">
    <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
    <div class="form-grid">
      <div class="form-field"><label>Title *</label><input type="text" name="title" required maxlength="150" value="<?= h($edit['title']) ?>"></div>
      <div class="form-field"><label>Icon</label><?php admin_icon_select('icon', $edit['icon']); ?></div>
      <div class="form-field"><label>Sort order</label><input type="number" name="sort_order" value="<?= (int) $edit['sort_order'] ?>"></div>
      <div class="form-field form-field--full"><label>Description</label><textarea name="description" rows="3" maxlength="2000"><?= h($edit['description']) ?></textarea></div>
      <label class="switch form-field--full"><input type="checkbox" name="active" <?= $edit['active'] ? 'checked' : '' ?>><span class="switch__track"></span> Visible on website</label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary">Save card</button>
      <a class="btn btn--ghost" href="features.php">Cancel</a>
    </div>
  </form>
  <?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Card</th><th>Order</th><th>Status</th><th class="ta-r">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td>
            <div class="cell-main">
              <span class="cell-icon"><?= icon($it['icon']) ?></span>
              <div><strong><?= h($it['title']) ?></strong><small><?= h(mb_strimwidth((string) $it['description'], 0, 70, '…')) ?></small></div>
            </div>
          </td>
          <td><?= (int) $it['sort_order'] ?></td>
          <td><?= $it['active'] ? '<span class="pill pill--completed">Live</span>' : '<span class="pill pill--cancelled">Hidden</span>' ?></td>
          <td class="ta-r">
            <a class="icon-btn" href="features.php?edit=<?= (int) $it['id'] ?>" title="Edit"><?= icon('edit') ?></a>
            <form method="post" class="inline" data-confirm="Delete this card?">
              <?= csrf_field() ?>
              <input type="hidden" name="_act" value="delete">
              <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
              <button class="icon-btn icon-btn--danger" title="Delete"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="4" class="empty">No cards yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php admin_footer(); ?>
