<?php
require_once __DIR__ . '/bootstrap.php';

if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    return;
}

$redirect = isset($_SERVER['REQUEST_URI']) ? urlencode($_SERVER['REQUEST_URI']) : '';
set_flash('error', 'Please log in to access the admin console.');
header('Location: login.php' . ($redirect ? '?redirect=' . $redirect : ''));
exit;
