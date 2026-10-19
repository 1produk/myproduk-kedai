<?php
require __DIR__ . '/../config/config.php';

$errors = [];
$data = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// ambil data
$data['nama']    = trim($_POST['nama'] ?? '');
$data['email']   = trim($_POST['email'] ?? '');
$data['telefon'] = trim($_POST['telefon'] ?? '');
$data['negeri']  = $_POST['negeri'] ?? '';
$data['suburl']  = trim($_POST['suburl'] ?? '');

$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// validasi
if ($data['nama'] === '') $errors[] = 'nama wajib diisi';
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'email tidak sah';
if (!preg_match('/^[a-z0-9-]+$/', $data['suburl'])) $errors[] = 'url ejen tidak sah';

if (strlen($password) < 6) $errors[] = 'kata laluan minimum 6 aksara';
if ($password !== $confirm) $errors[] = 'kata laluan tidak sepadan';

if (!isset($_POST['agree'])) $errors[] = 'sila setuju dengan syarat';

// ===== HASH PASSWORD =====
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// upload gambar
if (!empty($_FILES['profile']['name'])) {
    if ($_FILES['profile']['size'] > MAX_FILE_SIZE) {
        $errors[] = 'gambar melebihi 2mb';
    }

    if (!in_array($_FILES['profile']['type'], $ALLOWED_IMAGE)) {
        $errors[] = 'format gambar tidak dibenarkan';
    }

    if (!$errors) {
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }
        $ext = pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $ext;
        move_uploaded_file($_FILES['profile']['tmp_name'], UPLOAD_DIR . $filename);
    }
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $data;
    header('Location: register.php');
    exit;
}

// =======================
// contoh simpan (sementara)
// =======================
// nanti boleh simpan ke database
// password yang disimpan ialah $hashedPassword

// contoh debug (buang bila production)
// echo $hashedPassword; exit;

header('Location: login.php?register=success');
exit;
