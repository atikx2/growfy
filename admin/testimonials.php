<?php
/** Testimonials CRUD with avatar upload */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();
$table = DB_PREFIX . 'testimonials';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = post('_act');
    if ($act === 'save') {
        $id = (int) post('id');
        $avatar = field('avatar', 255, false);
        try {
            $uploaded = handle_image_upload('avatar_file');
            if ($uploaded) $avatar = $uploaded;
        } catch (RuntimeException $e) {
            flash_set('error', $e->getMessage());
            redirect('testimonials.php' . ($id ? '?edit=' . $id : '?edit=new'));
        }
        $data = [
            'name'       => field('name', 120),
            'role'       => field('role', 120, false),
            'avatar'     => $avatar,
            'quote'      => field('quote', 1000),
            'stars'      => max(1, min(5, (int) post('stars') ?: 5)),
            'sort_order' => (int) post('sort_order'),
            'active'     => isset($_POST['active']) ? 1 : 0,
        ];
        if ($data['name'] === '' || $data['quote'] === '') {
            flash_set('error', 'Name and quote are required.');
        } else {
            if ($id > 0) {
                db()->prepare("UPDATE $table SET name=?, role=?, avatar=?, quote=?, stars=?, sort_order=?, active=? WHERE id=?")
                    ->execute([...array_values($data), $id]);
                flash_set('success', 'Testimonial updated.');
            } else {
                db()->prepare("INSERT INTO $table (name, role, avatar, quote, stars, sort_order, active) VALUES (?,?,?,?,?,?,?)")
                    ->execute(array_values($data));
                flash_set('success', 'Testimonial added.');
            }
        }
    }
    if ($act === 'delete') {
        db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'Testimonial deleted.');
    }
    redirect('testimonials.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $_GET['edit'] === 'new'
        ? ['id' => 0, 'name' => '', 'role' => '', 'avatar' => '', 'quote' => '', 'stars' => 5, 'sort_order' => (q_count('testimonials') + 1) * 10, 'active' => 1]
        : q_one('testimonials', 'id = ?', [(int) $_GET['edit']]);
}
$items = q_all('testimonials');

admin_header('Testimonials', 'testimonials.php', $user);
?>
<div class="panel">
  <div class="panel__head">
    <h2><?= $edit ? ($edit['id'] ? 'Edit testimonial' : 'Add testimonial') : 'Client testimonials' ?></h2>
    <?php if (!$edit): ?><a class="btn btn--primary btn--sm" href="testimonials.php?edit=new">+ Add testimonial</a><?php endif; ?>
  </div>
  <?php if ($edit): ?>
  <form method="post" class="admin-form" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="_act" value="save">
    <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
    <input type="hidden" name="avatar" id="avatarPath" value="<?= h($edit['avatar']) ?>">
    <div class="form-grid">
      <div class="form-field"><label>Name *</label><input type="text" name="name" required maxlength="120" value="<?= h($edit['name']) ?>"></div>
      <div class="form-field"><label>Role / Title</label><input type="text" name="role" maxlength="120" value="<?= h($edit['role']) ?>" placeholder="CEO, Music Producer…"></div>
      <div class="form-field">
        <label>Photo (optional — upload)</label>
        <div class="upload-row">
          <span class="upload-preview" id="uploadPreview">
            <?php if ($edit['avatar']): ?><img src="../<?= h(ltrim($edit['avatar'], './')) ?>" alt=""><?php else: ?><?= icon('users') ?><?php endif; ?>
          </span>
          <input type="file" name="avatar_file" id="avatarFile" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>
        <small class="hint">Square JPG/PNG/WEBP, max <?= UPLOAD_MAX_MB ?>MB. Empty = auto initials avatar.</small>
      </div>
      <div class="form-field">
        <label>Stars (1–5)</label>
        <select name="stars">
          <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= (int) $edit['stars'] === $i ? 'selected' : '' ?>><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option><?php endfor; ?>
        </select>
      </div>
      <div class="form-field form-field--full"><label>Quote *</label><textarea name="quote" rows="3" required maxlength="1000"><?= h($edit['quote']) ?></textarea></div>
      <div class="form-field"><label>Sort order</label><input type="number" name="sort_order" value="<?= (int) $edit['sort_order'] ?>"></div>
      <label class="switch form-field--full"><input type="checkbox" name="active" <?= $edit['active'] ? 'checked' : '' ?>><span class="switch__track"></span> Visible on website</label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary">Save testimonial</button>
      <a class="btn btn--ghost" href="testimonials.php">Cancel</a>
    </div>
  </form>
  <?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Client</th><th>Rating</th><th>Quote</th><th>Status</th><th class="ta-r">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td>
            <div class="cell-main">
              <?php if ($it['avatar']): ?><img class="cell-avatar" src="../<?= h(ltrim($it['avatar'], './')) ?>" alt=""><?php else: ?><span class="cell-avatar cell-avatar--initials"><?= h(initials($it['name'])) ?></span><?php endif; ?>
              <div><strong><?= h($it['name']) ?></strong><small><?= h($it['role']) ?></small></div>
            </div>
          </td>
          <td><span class="dim"><?= stars((int) $it['stars']) ?></span></td>
          <td><small class="dim"><?= h(mb_strimwidth((string) $it['quote'], 0, 70, '…')) ?></small></td>
          <td><?= $it['active'] ? '<span class="pill pill--completed">Live</span>' : '<span class="pill pill--cancelled">Hidden</span>' ?></td>
          <td class="ta-r">
            <a class="icon-btn" href="testimonials.php?edit=<?= (int) $it['id'] ?>" title="Edit"><?= icon('edit') ?></a>
            <form method="post" class="inline" data-confirm="Delete this testimonial?">
              <?= csrf_field() ?>
              <input type="hidden" name="_act" value="delete">
              <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
              <button class="icon-btn icon-btn--danger" title="Delete"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="5" class="empty">No testimonials yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php admin_footer(); ?>
