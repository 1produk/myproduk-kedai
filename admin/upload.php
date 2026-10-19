<?php
header('Content-Type: application/json');

// KESELAMATAN: semak session login
require __DIR__ . '/../config/config.php';
if (empty($_SESSION['is_admin'])) {
    echo json_encode(['status' => 'error', 'msg' => 'tidak dibenarkan']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode([
        'status' => 'error',
        'msg' => 'no file uploaded'
    ]);
    exit;
}

$file = $_FILES['file'];

$allowed = ['image/jpeg','image/png','image/gif','image/webp'];
$max = 3 * 1024 * 1024; // 3mb

if ($file['error'] !== 0) {
    echo json_encode(['status'=>'error','msg'=>'upload error']);
    exit;
}
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['status'=>'error','msg'=>'invalid file type']);
    exit;
}
if ($file['size'] > $max) {
    echo json_encode(['status'=>'error','msg'=>'file too large']);
    exit;
}

// create folder if not exists
$folder = __DIR__ . '/../uploads';
if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_ext = ['jpg','jpeg','png','gif','webp'];
if (!in_array($ext, $allowed_ext)) {
    echo json_encode(['status'=>'error','msg'=>'invalid file extension']);
    exit;
}

$filename = 'img_'.time().'_'.rand(1000,9999).'.'.$ext;
$target_path = $folder.'/'.$filename;

// url path (relative)
$url_path = '../uploads/'.$filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    echo json_encode([
        'status' => 'success',
        'url' => $url_path,
        'filename' => $filename
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'msg' => 'failed to move uploaded file'
    ]);
}
