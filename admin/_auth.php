<?php
/** Admin auth guard — include at the top of every protected admin page. */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

function admin_user(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    return q_one('admins', 'id = ?', [(int) $_SESSION['admin_id']]);
}

function admin_require_login(): array
{
    $user = admin_user();
    if (!$user) {
        redirect('login.php');
    }
    // force password change on first login
    $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if (!empty($user['force_change']) && $current !== 'settings.php' && $current !== 'logout.php') {
        flash_set('error', 'Security: please change the default password first.');
        redirect('settings.php?tab=password');
    }
    return $user;
}
