<?php

// config.php
// ubah mengikut server anda
$db_host = 'localhost';
$db_name = 'u638314811_kedaidb';
$db_user = 'u638314811_kedaidb';
$db_pass = '@Lelangit123';
$dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$admin_user = 'admin';
// password di-hash menggunakan password_hash() - lebih selamat
// untuk tukar password: jalankan php -r "echo password_hash('password_baru', PASSWORD_DEFAULT);"
$admin_pass_hash = password_hash('lelangit123', PASSWORD_DEFAULT);




// session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
