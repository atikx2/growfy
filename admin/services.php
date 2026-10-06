<?php
/** Services CRUD */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();
$table = DB_PREFIX . 'services';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = post('_act');

    if ($act === 'save') {
        $id = (int) post('id');
        $data = [
            'title'       => field('title', 150),
            'tag'         => field('tag', 60, false),
            'platform'    => field('platform', 60, false),
            'icon'        => in_array(post('icon'), icon_names(), true) ? post('icon') : 'spark',
            'description' => field('description', 2000, false),
            'price_from'  => field('price_from', 60, false),
            'sort_order'  => (int) post('sort_order'),
            'active'      => isset($_POST['active']) ? 1 : 0,
        ];
        if ($data['title'] === '') {
            flash_set('error', 'Title is required.');
        } else {
            if ($id > 0) {
                db()->prepare("UPDATE $table SET title=?, tag=?, platform=?, icon=?, description=?, price_from=?, sort_order=?, active=? WHERE id=?")
                    ->execute([...array_values($data), $id]);
                flash_set('success', 'Service updated.');
            } else {
                db()->prepare("INSERT INTO $table (title, tag, platform, icon, description, price_from, sort_order, active) VALUES (?,?,?,?,?,?,?,?)")
                    ->execute(array_values($data));
                flash_set('success', 'Service added.');
            }
        }
    }

    if ($act === 'delete') {
        db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'Service deleted.');
    }

    redirect('services.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $_GET['edit'] === 'new'
        ? ['id' => 0, 'title' => '', 'tag' => '', 'platform' => '', 'icon' => 'spark', 'description' => '', 'price_from' => '', 'sort_order' => (q_count('services') + 1) * 10, 'active' => 1]
        : q_one('services', 'id = ?', [(int) $_GET['edit']]);
}
$items = q_all('services');

admin_header('Services', 'services.php', $user);
?>
<div class="panel">
  <div class="panel__head">
    <h2><?= $edit ? ($edit['id'] ? 'Edit service' : 'Add new service') : 'All services' ?></h2>
    <?php if (!$edit): ?><a class="btn btn--primary btn--sm" href="services.php?edit=new">+ Add service</a><?php endif; ?>
  </div>

  <?php if ($edit): ?>
  <form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="_act" value="save">
    <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
    <div class="form-grid">
      <div class="form-field">
        <label>Title *</label>
        <input type="text" name="title" required maxlength="150" value="<?= h($edit['title']) ?>" placeholder="Instagram">
      </div>
      <div class="form-field">
        <label>Badge tag</label>
        <input type="text" name="tag" maxlength="60" value="<?= h($edit['tag']) ?>" placeholder="BEST SELLER / HOT / TRENDING">
      </div>
      <div class="form-field">
        <label>Platform (color family)</label>
        <input type="text" name="platform" maxlength="60" value="<?= h($edit['platform']) ?>" placeholder="instagram / youtube / facebook…">
      </div>
      <div class="form-field">
        <label>Icon</label>
        <?php admin_icon_select('icon', $edit['icon']); ?>
      </div>
      <div class="form-field">
        <label>Starting price</label>
        <input type="text" name="price_from" maxlength="60" value="<?= h($edit['price_from']) ?>" placeholder="$29">
      </div>
      <div class="form-field">
        <label>Sort order</label>
        <input type="number" name="sort_order" value="<?= (int) $edit['sort_order'] ?>">
      </div>
      <div class="form-field form-field--full">
        <label>Description</label>
        <textarea name="description" rows="3" maxlength="2000"><?= h($edit['description']) ?></textarea>
      </div>
      <label class="switch form-field--full">
        <input type="checkbox" name="active" <?= $edit['active'] ? 'checked' : '' ?>>
        <span class="switch__track"></span> Visible on website
      </label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary">Save service</button>
      <a class="btn btn--ghost" href="services.php">Cancel</a>
    </div>
  </form>

  <?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Service</th><th>Tag</th><th>Price from</th><th>Order</th><th>Status</th><th class="ta-r">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td>
            <div class="cell-main">
              <span class="cell-icon"><?= icon($it['icon']) ?></span>
              <div><strong><?= h($it['title']) ?></strong><small><?= h(mb_strimwidth((string) $it['description'], 0, 60, '…')) ?></small></div>
            </div>
          </td>
          <td><?= $it['tag'] ? '<span class="chip">' . h($it['tag']) . '</span>' : '—' ?></td>
          <td><?= h($it['price_from'] ?: '—') ?></td>
          <td><?= (int) $it['sort_order'] ?></td>
          <td><?= $it['active'] ? '<span class="pill pill--completed">Live</span>' : '<span class="pill pill--cancelled">Hidden</span>' ?></td>
          <td class="ta-r">
            <a class="icon-btn" href="services.php?edit=<?= (int) $it['id'] ?>" title="Edit"><?= icon('edit') ?></a>
            <form method="post" class="inline" data-confirm="Delete “<?= h($it['title']) ?>”?">
              <?= csrf_field() ?>
              <input type="hidden" name="_act" value="delete">
              <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
              <button class="icon-btn icon-btn--danger" title="Delete"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="6" class="empty">No services yet — add your first one.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php admin_footer(); ?>
