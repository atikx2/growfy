<?php
/**
 * =====================================================================
 *  Growfy Agency — Main Configuration
 * =====================================================================
 *  cPanel-এ MySQL ব্যবহার করতে নিচের ৪টি মান বদলান (DB_NAME, DB_USER,
 *  DB_PASS, DB_HOST)। কিছু না দিলে স্বয়ংক্রিয়ভাবে SQLite (data/ ফোল্ডারে
 *  ফাইল-ভিত্তিক ডাটাবেস) ব্যবহার হবে — কোনো সেটআপ ছাড়াই চলবে।
 *
 *  For cPanel MySQL: create a database + user in cPanel > MySQL Databases,
 *  put the credentials below. Tables are created automatically on first run.
 */

/* ----------------------------- DATABASE ----------------------------- */
define('DB_DRIVER', 'auto');        // 'auto' | 'mysql' | 'sqlite'
define('DB_HOST',   'localhost');   // MySQL host (cPanel এ সাধারণত localhost)
define('DB_NAME',   '');            // যেমন: username_growfy
define('DB_USER',   '');            // যেমন: username_growfy
define('DB_PASS',   '');            // MySQL password
define('DB_PREFIX', 'gf_');         // টেবিল প্রিফিক্স (ইচ্ছা করলে বদলান)

/* ------------------------------ GENERAL ----------------------------- */
define('GROWFY_ENV', 'production'); // 'production' | 'development'
define('APP_TZ', 'Asia/Dhaka');     // timezone
define('UPLOAD_MAX_MB', 3);         // admin image upload limit (MB)

/* =====================================================================
 *  আর কিছু বদলাতে হবে না — নিচের অংশ সিস্টেমের।
 *  ===================================================================== */
date_default_timezone_set(APP_TZ);
if (GROWFY_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}
