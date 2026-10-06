<?php
/** FAQ CRUD */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();
$table = DB_PREFIX . 'faqs';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = post('_act');
    if ($act === 'save') {
        $id = (int) post('id');
        $data = [
            'question'   => field('question', 255),
            'answer'     => field('answer', 3000),
            'sort_order' => (int) post('sort_order'),
            'active'     => isset($_POST['active']) ? 1 : 0,
        ];
        if ($data['question'] === '' || $data['answer'] === '') {
            flash_set('error', 'Question and answer are required.');
        } else {
            if ($id > 0) {
                db()->prepare("UPDATE $table SET question=?, answer=?, sort_order=?, active=? WHERE id=?")
                    ->execute([...array_values($data), $id]);
                flash_set('success', 'FAQ updated.');
            } else {
                db()->prepare("INSERT INTO $table (question, answer, sort_order, active) VALUES (?,?,?,?)")
                    ->execute(array_values($data));
                flash_set('success', 'FAQ added.');
            }
        }
    }
    if ($act === 'delete') {
        db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'FAQ deleted.');
    }
    redirect('faqs.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = $_GET['edit'] === 'new'
        ? ['id' => 0, 'question' => '', 'answer' => '', 'sort_order' => (q_count('faqs') + 1) * 10, 'active' => 1]
        : q_one('faqs', 'id = ?', [(int) $_GET['edit']]);
}
$items = q_all('faqs');

admin_header('FAQs', 'faqs.php', $user);
?>
<div class="panel">
  <div class="panel__head">
    <h2><?= $edit ? ($edit['id'] ? 'Edit FAQ' : 'Add FAQ') : 'Frequently asked questions' ?></h2>
    <?php if (!$edit): ?><a class="btn btn--primary btn--sm" href="faqs.php?edit=new">+ Add FAQ</a><?php endif; ?>
  </div>
  <?php if ($edit): ?>
  <form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="_act" value="save">
    <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
    <div class="form-grid">
      <div class="form-field form-field--full"><label>Question *</label><input type="text" name="question" required maxlength="255" value="<?= h($edit['question']) ?>"></div>
      <div class="form-field form-field--full"><label>Answer *</label><textarea name="answer" rows="4" required maxlength="3000"><?= h($edit['answer']) ?></textarea></div>
      <div class="form-field"><label>Sort order</label><input type="number" name="sort_order" value="<?= (int) $edit['sort_order'] ?>"></div>
      <label class="switch form-field--full"><input type="checkbox" name="active" <?= $edit['active'] ? 'checked' : '' ?>><span class="switch__track"></span> Visible on website</label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary">Save FAQ</button>
      <a class="btn btn--ghost" href="faqs.php">Cancel</a>
    </div>
  </form>
  <?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Question</th><th>Order</th><th>Status</th><th class="ta-r">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><strong><?= h($it['question']) ?></strong><br><small class="dim"><?= h(mb_strimwidth((string) $it['answer'], 0, 90, '…')) ?></small></td>
          <td><?= (int) $it['sort_order'] ?></td>
          <td><?= $it['active'] ? '<span class="pill pill--completed">Live</span>' : '<span class="pill pill--cancelled">Hidden</span>' ?></td>
          <td class="ta-r">
            <a class="icon-btn" href="faqs.php?edit=<?= (int) $it['id'] ?>" title="Edit"><?= icon('edit') ?></a>
            <form method="post" class="inline" data-confirm="Delete this FAQ?">
              <?= csrf_field() ?>
              <input type="hidden" name="_act" value="delete">
              <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
              <button class="icon-btn icon-btn--danger" title="Delete"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="4" class="empty">No FAQs yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php admin_footer(); ?>
