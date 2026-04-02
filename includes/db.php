<?php
// includes/db.php
require_once __DIR__ . '/config.php';

$conn = mysqli_init();
if (!$conn) {
    error_log('[Uni-DMS] mysqli_init failed');
    http_response_code(500);
    die('A database error occurred. Please try again later.');
}

// Local XAMPP uses localhost and an empty password, so we rely on the constants loaded via config.php
$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASSWORD;
$db_name = DB_NAME;
$db_port = is_numeric(DB_PORT) ? (int) DB_PORT : 3306;

if (!mysqli_real_connect($conn, $db_host, $db_user, $db_pass, $db_name, $db_port)) {
    error_log('[Uni-DMS] DB connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    die('Unable to connect to the database. Please try again later.');
}

mysqli_set_charset($conn, 'utf8mb4');