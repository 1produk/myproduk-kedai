<?php
require __DIR__ . '/../config/config.php';

if (empty($_SESSION['is_admin'])) {
    header('location: login.php');
    exit;
}

if (!empty($_POST['filename'])) {
    // BETUL: path dari /admin/ ke /uploads/ ialah ../uploads/
    $file = __DIR__ . '/../uploads/' . basename($_POST['filename']);

    if (file_exists($file)) {
        unlink($file);
        echo "<script>alert('Fail dipadam.'); window.history.back();</script>";
        exit;
    }
}

echo "<script>alert('Gagal memadam fail.'); window.history.back();</script>";
