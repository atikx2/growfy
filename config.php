<?php
/**
 * =====================================================================
 *  Growfy Agency — Main Configuration
 * =====================================================================
 *
 *  ⚡ cPanel-এ DB credentials সেট করার সঠিক উপায়:
 *  এই ফাইলটা না বদলে একই ফোল্ডারে `config.local.php` নামে ফাইল বানান
 *  (config.local.php.sample ফাইলটা দেখুন)। ওই ফাইলে credentials রাখলে
 *  GitHub থেকে pull করলেও সেটা কখনো overwrite হবে না।
 *
 *  কিছুই না করলে স্বয়ংক্রিয়ভাবে SQLite (data/ ফোল্ডারে) ব্যবহার হবে —
 *  কোনো সেটআপ ছাড়াই সাইট চলবে।
 */

/* ------ server-specific overrides (NOT tracked by git) ------ */
$__local_config = __DIR__ . '/config.local.php';
if (file_exists($__local_config)) {
    require $__local_config;
}
unset($__local_config);

/* -------------------------- DEFAULTS -------------------------- */
if (!defined('DB_DRIVER'))   define('DB_DRIVER', 'auto');        // 'auto' | 'mysql' | 'sqlite'
if (!defined('DB_HOST'))     define('DB_HOST',   'localhost');
if (!defined('DB_NAME'))     define('DB_NAME',   '');            // যেমন: username_growfy
if (!defined('DB_USER'))     define('DB_USER',   '');
if (!defined('DB_PASS'))     define('DB_PASS',   '');
if (!defined('DB_PREFIX'))   define('DB_PREFIX', 'gf_');

if (!defined('GROWFY_ENV'))  define('GROWFY_ENV', 'production'); // 'production' | 'development'
if (!defined('APP_TZ'))      define('APP_TZ', 'Asia/Dhaka');
if (!defined('UPLOAD_MAX_MB')) define('UPLOAD_MAX_MB', 3);

/* =====================================================================
 *  SYSTEM — আর কিছু বদলাতে হবে না
 *  ===================================================================== */
date_default_timezone_set(APP_TZ);
if (GROWFY_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}
