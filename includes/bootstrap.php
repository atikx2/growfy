<?php
/** Common bootstrap — load once at the top of every entry point. */

define('GROWFY_ROOT', dirname(__DIR__));

require_once GROWFY_ROOT . '/config.php';
require_once GROWFY_ROOT . '/includes/functions.php';
require_once GROWFY_ROOT . '/includes/db.php';

/* Hardened session */
if (session_status() === PHP_SESSION_NONE) {
    /* Make sure the session save path is writable (cPanel default usually is;
       fallback for restricted / embedded runtimes such as the WASM preview). */
    $savePath = session_save_path() ?: sys_get_temp_dir();
    if (!is_dir($savePath) || !is_writable($savePath)) {
        $candidates = [sys_get_temp_dir(), GROWFY_ROOT . '/data/sessions'];
        foreach ($candidates as $cand) {
            if (!is_dir($cand)) {
                @mkdir($cand, 0775, true);
            }
            if (is_dir($cand) && is_writable($cand)) {
                session_save_path($cand);
                break;
            }
        }
    }
    session_name('GROWFYSESSID');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
