<?php
/** Admin login with basic rate limiting. */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // rate limit: 5 attempts per 10 minutes (session based)
    $now = time();
    $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'] ?? [], fn($t) => $t > $now - 600);
    if (count($_SESSION['login_attempts']) >= 5) {
        $error = 'Too many attempts. Please wait a few minutes and try again.';
    } else {
        $username = field('username', 60);
        $password = (string) ($_POST['password'] ?? '');
        $user = $username ? q_one('admins', 'username = ?', [$username]) : null;

        if ($user && password_verify($password, $user['pass_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $user['id'];
            unset($_SESSION['login_attempts']);
            db()->prepare('UPDATE ' . DB_PREFIX . 'admins SET last_login = ' . (db_driver() === 'mysql' ? 'NOW()' : "datetime('now')") . ' WHERE id = ?')
                ->execute([(int) $user['id']]);
            redirect('index.php');
        }
        $_SESSION['login_attempts'][] = $now;
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login · <?= h(c('site_name')) ?></title>
<meta name="robots" content="noindex,nofollow">
<link rel="icon" type="image/svg+xml" href="../favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<?php $cssV = file_exists(dirname(__DIR__) . '/assets/css/admin.css') ? filemtime(dirname(__DIR__) . '/assets/css/admin.css') : '1'; ?>
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= h((string) $cssV) ?>">
</head>
<body class="login-page">
  <div class="login-card">
    <div class="login-brand">
      <span class="brand__mark"><?php include dirname(__DIR__) . '/includes/logo.php'; ?></span>
      <div>
        <strong><?= h(c('site_name')) ?></strong>
        <small>Admin Panel</small>
      </div>
    </div>
    <h1>Welcome back</h1>
    <p class="login-sub">Sign in to manage your website.</p>
    <?php if ($error): ?><div class="alert alert--error"><?= h($error) ?></div><?php endif; ?>
    <?php foreach (flash_get() as $fl): ?><div class="alert alert--<?= h($fl['type']) ?>"><?= h($fl['msg']) ?></div><?php endforeach; ?>
    <form method="post" class="login-form">
      <?= csrf_field() ?>
      <div class="form-field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus autocomplete="username" placeholder="admin">
      </div>
      <div class="form-field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••••">
      </div>
      <button type="submit" class="btn btn--primary btn--block btn--lg">Sign In</button>
    </form>
    <a class="login-back" href="../index.php">← Back to website</a>
  </div>
</body>
</html>
