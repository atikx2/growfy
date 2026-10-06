<?php
/** Contact messages inbox */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();
$table = DB_PREFIX . 'messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = post('_act');
    if ($act === 'delete') {
        db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'Message deleted.');
    }
    if ($act === 'toggle') {
        db()->prepare("UPDATE $table SET is_read = 1 - is_read WHERE id = ?")->execute([(int) post('id')]);
        flash_set('success', 'Status updated.');
    }
    redirect('inbox.php' . (post('view') ? '?view=' . (int) post('view') : ''));
}

$view = null;
if (isset($_GET['view'])) {
    $view = q_one('messages', 'id = ?', [(int) $_GET['view']]);
    if ($view && !$view['is_read']) {
        db()->prepare("UPDATE $table SET is_read = 1 WHERE id = ?")->execute([(int) $view['id']]);
        $view['is_read'] = 1;
    }
}
$items = q_all('messages', '1=1', 'id DESC');

admin_header('Inbox', 'inbox.php', $user);
?>
<div class="two-col two-col--inbox">
  <div class="panel">
    <div class="panel__head"><h2>Messages <span class="count-badge"><?= count($items) ?></span></h2></div>
    <div class="mini-list mini-list--tall">
      <?php foreach ($items as $m): ?>
      <a class="mini-item <?= $m['is_read'] ? '' : 'is-new' ?> <?= $view && (int) $view['id'] === (int) $m['id'] ? 'is-active' : '' ?>" href="inbox.php?view=<?= (int) $m['id'] ?>">
        <span class="admin-avatar"><?= h(initials($m['name'])) ?></span>
        <div class="mini-item__body">
          <strong><?= h($m['name']) ?> <?= $m['is_read'] ? '' : '<span class="dot-new"></span>' ?></strong>
          <small><?= h(mb_strimwidth((string) $m['message'], 0, 60, '…')) ?></small>
        </div>
        <time><?= h(time_ago($m['created_at'])) ?></time>
      </a>
      <?php endforeach; ?>
      <?php if (!$items): ?><p class="empty empty--pad">No messages yet. They will appear here when visitors use the contact form.</p><?php endif; ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h2><?= $view ? 'Conversation' : 'Select a message' ?></h2></div>
    <?php if ($view): ?>
    <div class="msg-view">
      <div class="msg-view__head">
        <span class="admin-avatar admin-avatar--lg"><?= h(initials($view['name'])) ?></span>
        <div>
          <strong><?= h($view['name']) ?></strong>
          <div class="msg-view__meta">
            <?php if ($view['email']): ?><a href="mailto:<?= h($view['email']) ?>"><?= icon('mail') ?><?= h($view['email']) ?></a><?php endif; ?>
            <?php if ($view['phone']): ?><a href="tel:<?= h(preg_replace('/[^+\d]/', '', $view['phone'])) ?>"><?= icon('phone') ?><?= h($view['phone']) ?></a><?php endif; ?>
            <span><?= icon('clock') ?><?= h($view['created_at']) ?></span>
          </div>
        </div>
      </div>
      <?php if ($view['subject']): ?><h3 class="msg-view__subject"><?= h($view['subject']) ?></h3><?php endif; ?>
      <p class="msg-view__body"><?= nl2br(h($view['message'])) ?></p>
      <div class="form-actions">
        <?php if ($view['email']): ?><a class="btn btn--primary btn--sm" href="mailto:<?= h($view['email']) ?>?subject=Re:%20<?= rawurlencode($view['subject'] ?: 'Your inquiry') ?>"><?= icon('mail') ?> Reply by email</a><?php endif; ?>
        <form method="post" class="inline">
          <?= csrf_field() ?>
          <input type="hidden" name="_act" value="toggle">
          <input type="hidden" name="id" value="<?= (int) $view['id'] ?>">
          <input type="hidden" name="view" value="<?= (int) $view['id'] ?>">
          <button class="btn btn--ghost btn--sm"><?= $view['is_read'] ? 'Mark unread' : 'Mark read' ?></button>
        </form>
        <form method="post" class="inline" data-confirm="Delete this message permanently?">
          <?= csrf_field() ?>
          <input type="hidden" name="_act" value="delete">
          <input type="hidden" name="id" value="<?= (int) $view['id'] ?>">
          <button class="btn btn--danger btn--sm">Delete</button>
        </form>
      </div>
    </div>
    <?php else: ?>
      <p class="empty empty--pad">Choose a conversation from the left to read it here.</p>
    <?php endif; ?>
  </div>
</div>
<?php admin_footer(); ?>
