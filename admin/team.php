<?php
/** Team members CRUD with photo upload + socials */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();
$table = DB_PREFIX . 'team_members';

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
            redirect('team.php' . ($id ? '?edit=' . $id : '?edit=new'));
        }
        $data = [
            'name'       => field('name', 120),
            'role'       => field('role', 120, false),
            'bio'        => field('bio', 400, false),
            'avatar'     => $avatar,
            'email'      => field('email', 150, false),
            'phone'      => field('phone', 60, false),
            'facebook'   => field('facebook', 255, false),
            'twitter'    => field('twitter', 255, false),
            'linkedin'   => field('linkedin', 255, false),
            'instagram'  => field('instagram', 255, false),
            'sort_order' => (int) post('sort_order'),
            'active'     => isset($_POST['active']) ? 1 : 0,
        ];
        if ($data['name'] === '' || $data['role'] === '') {
            flash_set('error', 'Name and role are required.');
        } elseif ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Please enter a valid email address (or leave it empty).');
        } else {
            if ($id > 0) {
                db()->prepare("UPDATE $table SET name=?, role=?, bio=?, avatar=?, email=?, phone=?, facebook=?, twitter=?, linkedin=?, instagram=?, sort_order=?, active=? WHERE id=?")
                    ->execute([...array_values($data), $id]);
                flash_set('success', 'Team member updated.');
            } else {
                db()->prepare("INSERT INTO $table (name, role, bio, avatar, email, phone, facebook, twitter, linkedin, instagram, sort_order, active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
                    ->execute(array_values($data));
                flash_set('success', 'Team member added — now live on team.php.');
            }
        }
    }
    if ($act === 'delete') {
        db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'Team member deleted.');
    }
    redirect('team.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $_GET['edit'] === 'new'
        ? ['id' => 0, 'name' => '', 'role' => '', 'bio' => '', 'avatar' => '', 'email' => '', 'phone' => '',
           'facebook' => '', 'twitter' => '', 'linkedin' => '', 'instagram' => '',
           'sort_order' => (q_count('team_members') + 1) * 10, 'active' => 1]
        : q_one('team_members', 'id = ?', [(int) $_GET['edit']]);
}
$items = q_all('team_members');

admin_header('Team Members', 'team.php', $user);
?>
<div class="panel">
  <div class="panel__head">
    <h2><?= $edit ? ($edit['id'] ? 'Edit team member' : 'Add team member') : 'Team members (' . count($items) . ')' ?></h2>
    <div class="panel__actions">
      <?php if (!$edit): ?><a class="btn btn--primary btn--sm" href="team.php?edit=new">+ Add member</a><?php endif; ?>
      <a class="btn btn--ghost btn--sm" href="../team.php" target="_blank">View team page ↗</a>
    </div>
  </div>
  <?php if ($edit): ?>
  <form method="post" class="admin-form" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="_act" value="save">
    <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
    <input type="hidden" name="avatar" id="avatarPath" value="<?= h($edit['avatar']) ?>">
    <div class="form-grid">
      <div class="form-field"><label>Full name *</label><input type="text" name="name" required maxlength="120" value="<?= h($edit['name']) ?>" placeholder="e.g. John Carter"></div>
      <div class="form-field"><label>Role / Designation *</label><input type="text" name="role" required maxlength="120" value="<?= h($edit['role']) ?>" placeholder="e.g. Head of Growth"></div>
      <div class="form-field">
        <label>Photo (upload — shows big on the team page)</label>
        <div class="upload-row">
          <span class="upload-preview" id="uploadPreview">
            <?php if ($edit['avatar']): ?><img src="../<?= h(ltrim($edit['avatar'], './')) ?>" alt=""><?php else: ?><?= icon('users') ?><?php endif; ?>
          </span>
          <input type="file" name="avatar_file" id="avatarFile" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>
        <small class="hint">Square or 4:5 JPG/PNG/WEBP, max <?= UPLOAD_MAX_MB ?>MB. Empty = auto initials avatar.</small>
      </div>
      <div class="form-field form-field--full">
        <label>Short bio (max 400 chars)</label>
        <textarea name="bio" rows="3" maxlength="400" placeholder="2–3 punchy sentences about what this person does for clients…"><?= h($edit['bio']) ?></textarea>
      </div>
      <div class="form-field"><label>Email (optional)</label><input type="email" name="email" maxlength="150" value="<?= h($edit['email']) ?>" placeholder="name@growfy.info"></div>
      <div class="form-field"><label>Phone (optional)</label><input type="text" name="phone" maxlength="60" value="<?= h($edit['phone']) ?>" placeholder="+880 1X XXX XXX"></div>
      <div class="form-field"><label>Facebook URL</label><input type="text" name="facebook" maxlength="255" value="<?= h($edit['facebook']) ?>" placeholder="https://facebook.com/…"></div>
      <div class="form-field"><label>X / Twitter URL</label><input type="text" name="twitter" maxlength="255" value="<?= h($edit['twitter']) ?>" placeholder="https://x.com/…"></div>
      <div class="form-field"><label>LinkedIn URL</label><input type="text" name="linkedin" maxlength="255" value="<?= h($edit['linkedin']) ?>" placeholder="https://linkedin.com/in/…"></div>
      <div class="form-field"><label>Instagram URL</label><input type="text" name="instagram" maxlength="255" value="<?= h($edit['instagram']) ?>" placeholder="https://instagram.com/…"></div>
      <div class="form-field"><label>Sort order</label><input type="number" name="sort_order" value="<?= (int) $edit['sort_order'] ?>"></div>
      <label class="switch form-field--full"><input type="checkbox" name="active" <?= $edit['active'] ? 'checked' : '' ?>><span class="switch__track"></span> Visible on team page</label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary"><?= $edit['id'] ? 'Save changes' : 'Add member' ?></button>
      <a class="btn btn--ghost" href="team.php">Cancel</a>
    </div>
  </form>
  <?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Member</th><th>Contact</th><th>Socials</th><th>Status</th><th class="ta-r">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it):
        $socCount = count(array_filter([$it['facebook'], $it['twitter'], $it['linkedin'], $it['instagram']]));
      ?>
        <tr>
          <td>
            <div class="cell-main">
              <?php if ($it['avatar']): ?>
                <img class="cell-avatar" src="../<?= h(ltrim($it['avatar'], './')) ?>" alt="">
              <?php else: ?>
                <span class="cell-avatar cell-avatar--initials"><?= h(initials($it['name'])) ?></span>
              <?php endif; ?>
              <div><strong><?= h($it['name']) ?></strong><small><?= h($it['role']) ?></small></div>
            </div>
          </td>
          <td>
            <?php if ($it['email'] || $it['phone']): ?>
              <small class="dim"><?= h($it['email']) ?><?= ($it['email'] && $it['phone']) ? ' · ' : '' ?><?= h($it['phone']) ?></small>
            <?php else: ?><small class="dim">—</small><?php endif; ?>
          </td>
          <td><?= $socCount ? '<span class="pill pill--shipped">' . $socCount . ' linked</span>' : '<small class="dim">—</small>' ?></td>
          <td><?= $it['active'] ? '<span class="pill pill--completed">Live</span>' : '<span class="pill pill--cancelled">Hidden</span>' ?></td>
          <td class="ta-r">
            <a class="icon-btn" href="team.php?edit=<?= (int) $it['id'] ?>" title="Edit"><?= icon('edit') ?></a>
            <form method="post" class="inline" data-confirm="Delete this team member?">
              <?= csrf_field() ?>
              <input type="hidden" name="_act" value="delete">
              <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
              <button class="icon-btn icon-btn--danger" title="Delete"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="5" class="empty">No team members yet — click “+ Add member”.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php admin_footer(); ?>
