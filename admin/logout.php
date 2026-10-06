<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
unset($_SESSION['admin_id']);
session_regenerate_id(true);
flash_set('success', 'You have been logged out.');
redirect('login.php');
