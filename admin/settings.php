<?php
/** Site settings + admin password */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();

function settings_groups(): array
{
    return [
        'Brand identity' => [
            ['site_name', 'Site name', 'input'],
            ['site_tagline', 'Tagline', 'input'],
        ],
        'SEO' => [
            ['seo_title', 'Meta title', 'input'],
            ['seo_description', 'Meta description', 'textarea'],
            ['seo_keywords', 'Meta keywords (comma separated)', 'input'],
        ],
        'Contact information' => [
            ['contact_email', 'Email address', 'input'],
            ['contact_phone', 'Phone (display)', 'input'],
            ['whatsapp_number', 'WhatsApp number (digits only, with country code)', 'input'],
            ['contact_address', 'Address / locations', 'input'],
            ['business_hours', 'Working hours', 'input'],
        ],
        'Social links (leave empty to hide)' => [
            ['social_facebook', 'Facebook URL', 'input'],
            ['social_instagram', 'Instagram URL', 'input'],
            ['social_twitter', 'X / Twitter URL', 'input'],
            ['social_linkedin', 'LinkedIn URL', 'input'],
            ['social_youtube', 'YouTube URL', 'input'],
            ['social_tiktok', 'TikTok URL', 'input'],
        ],
        'Footer' => [
            ['footer_about', 'Footer about text', 'textarea'],
            ['footer_copyright', 'Copyright line (after © year)', 'input'],
        ],
    ];
}

$tab = $_GET['tab'] ?? 'general';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $act = post('_act');

    if ($act === 'settings') {
        $pairs = [];
        foreach (settings_groups() as $fields) {
            foreach ($fields as [$key]) {
                $pairs[$key] = mb_substr(trim((string) ($_POST[$key] ?? '')), 0, 1500);
            }
        }
        settings_set($pairs);
        flash_set('success', 'Settings saved.');
        redirect('settings.php');
    }

    if ($act === 'password') {
        $current = (string) ($_POST['current'] ?? '');
        $new     = (string) ($_POST['new'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');
        if (!password_verify($current, $user['pass_hash'])) {
            flash_set('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 8) {
            flash_set('error', 'New password must be at least 8 characters.');
        } elseif ($new !== $confirm) {
            flash_set('error', 'New passwords do not match.');
        } else {
            db()->prepare('UPDATE ' . DB_PREFIX . 'admins SET pass_hash = ?, force_change = 0, display_name = ? WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), field('display_name', 120, false) ?: 'Administrator', (int) $user['id']]);
            flash_set('success', 'Password updated successfully.');
            redirect('settings.php');
        }
        redirect('settings.php?tab=password');
    }
}

// refresh user (display name may have changed)
$user = admin_user() ?: $user;
$mustChange = !empty($user['force_change']);

admin_header('Settings', 'settings.php', $user);

if ($mustChange): ?>
<div class="alert alert--error">
  Your account still uses the <strong>default password</strong>. Set a new one now to secure the admin panel.
</div>
<?php endif; ?>

<div class="tabs">
  <a href="settings.php" class="tab <?= $tab !== 'password' ? 'is-active' : '' ?>">Site settings</a>
  <a href="settings.php?tab=password" class="tab <?= $tab === 'password' ? 'is-active' : '' ?>">Account &amp; password</a>
</div>

<?php if ($tab !== 'password'): ?>
<form method="post" class="admin-form">
  <?= csrf_field() ?>
  <input type="hidden" name="_act" value="settings">
  <?php foreach (settings_groups() as $group => $fields): ?>
  <div class="panel">
    <div class="panel__head"><h2><?= h($group) ?></h2></div>
    <div class="form-grid">
      <?php foreach ($fields as [$key, $label, $type]): ?>
      <div class="form-field <?= $type === 'textarea' ? 'form-field--full' : '' ?>">
        <label><?= h($label) ?></label>
        <?php if ($type === 'textarea'): ?>
          <textarea name="<?= h($key) ?>" rows="3"><?= h(c($key)) ?></textarea>
        <?php else: ?>
          <input type="text" name="<?= h($key) ?>" maxlength="300" value="<?= h(c($key)) ?>">
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="form-actions form-actions--sticky">
    <button type="submit" class="btn btn--primary btn--lg">Save settings</button>
  </div>
</form>
<?php else: ?>
<div class="panel panel--narrow">
  <div class="panel__head"><h2>Change password</h2></div>
  <form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="_act" value="password">
    <div class="form-grid">
      <div class="form-field"><label>Display name</label><input type="text" name="display_name" maxlength="120" value="<?= h($user['display_name'] ?: 'Administrator') ?>"></div>
      <div class="form-field"><label>Current password *</label><input type="password" name="current" required autocomplete="current-password"></div>
      <div class="form-field"><label>New password * (min 8 chars)</label><input type="password" name="new" required minlength="8" autocomplete="new-password"></div>
      <div class="form-field"><label>Repeat new password *</label><input type="password" name="confirm" required minlength="8" autocomplete="new-password"></div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary">Update password</button>
    </div>
  </form>
</div>

<div class="panel panel--narrow">
  <div class="panel__head"><h2>System info</h2></div>
  <div class="sysinfo">
    <div><small>PHP version</small><strong><?= h(PHP_VERSION) ?></strong></div>
    <div><small>Database</small><strong><?= h(db_driver() === 'mysql' ? 'MySQL' : 'SQLite (auto)') ?></strong></div>
    <div><small>Username</small><strong><?= h($user['username']) ?></strong></div>
    <div><small>Last login</small><strong><?= h($user['last_login'] ?: '—') ?></strong></div>
  </div>
</div>
<?php endif; ?>
<?php admin_footer(); ?>
