<?php
// save.php
require __DIR__ . '/../config/config.php';
// session_start() sudah dipanggil dalam config.php

if (empty($_SESSION['is_admin'])) {
    header('location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location: admin.php');
    exit;
}

$pdo = new PDO($dsn, $db_user, $db_pass, $options);

$show_btn_whatsapp = (int)($_POST['show_btn_whatsapp'] ?? 0);
$show_btn_produk = (int)($_POST['show_btn_produk'] ?? 0);


// ========================
// sanitize settings asal
// ========================
$company_name   = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
$company_banner = isset($_POST['company_banner']) ? trim($_POST['company_banner']) : '';
$whatsapp       = isset($_POST['whatsapp']) ? preg_replace('/[^\d+]/', '', $_POST['whatsapp']) : '';
$copyright_text = isset($_POST['copyright_text']) ? trim($_POST['copyright_text']) : '';

// ========================
// sanitize butiran syarikat tambahan
// ========================
$extra = $_POST['extra_company'] ?? [];

$extra_company_name     = isset($extra['name']) ? trim($extra['name']) : '';
$extra_company_address  = isset($extra['address']) ? trim($extra['address']) : '';
$extra_company_phone    = isset($extra['phone']) ? trim($extra['phone']) : '';
$extra_company_whatsapp = isset($extra['whatsapp'])
    ? preg_replace('/[^\d+]/', '', $extra['whatsapp'])
    : '';

$pdo->beginTransaction();

try {

    // ========================
    // simpan settings (1 row sahaja)
    // ========================
    $pdo->exec("delete from settings");

$stmt = $pdo->prepare("
    insert into settings (
        name,
        company_name,
        company_banner,
        whatsapp,
        copyright_text,
        extra_company_name,
        extra_company_address,
        extra_company_phone,
        extra_company_whatsapp,
        show_btn_whatsapp,
        show_btn_produk
    ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");


$stmt->execute([
    'default',
    $company_name,
    $company_banner,
    $whatsapp,
    $copyright_text,
    $extra_company_name,
    $extra_company_address,
    $extra_company_phone,
    $extra_company_whatsapp,
    $show_btn_whatsapp,
    $show_btn_produk
]);


    // ========================
    // products
    // ========================
    $products = isset($_POST['products']) && is_array($_POST['products'])
        ? $_POST['products']
        : [];

    $update = $pdo->prepare("
        update products set
            name = ?,
            price = ?,
            discount = ?,
            discount_type = ?,
            coupon = ?,
            image_url = ?,
            wa_message = ?,
            details = ?,
            is_active = ?
        where id = ?
    ");

    $insert = $pdo->prepare("
        insert into products (
            position,
            name,
            price,
            discount,
            discount_type,
            coupon,
            image_url,
            wa_message,
            details,
            is_active
        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($products as $pos => $p) {

        $id            = isset($p['id']) ? (int)$p['id'] : 0;
        $name          = isset($p['name']) ? trim($p['name']) : '';
        $price         = isset($p['price']) ? floatval(str_replace([',',' '],['',''], $p['price'])) : 0.00;
        $discount      = isset($p['discount']) ? floatval(str_replace([',',' '],['',''], $p['discount'])) : 0.00;
        $discount_type = (isset($p['discount_type']) && $p['discount_type'] === 'rm') ? 'rm' : 'percent';
        $coupon        = isset($p['coupon']) ? trim($p['coupon']) : '';
        $image         = isset($p['image_url']) ? trim($p['image_url']) : '';
        $wa_message    = isset($p['wa_message']) ? trim($p['wa_message']) : '';
        $details       = isset($p['details']) ? trim($p['details']) : '';
        $is_active     = isset($p['is_active']) ? (int)$p['is_active'] : 0;

        if ($id > 0) {
            // update produk sedia ada
            $update->execute([
                $name,
                $price,
                $discount,
                $discount_type,
                $coupon,
                $image,
                $wa_message,
                $details,
                $is_active,
                $id
            ]);
        } else {
            // insert produk baru
            $insert->execute([
                $pos,
                $name,
                $price,
                $discount,
                $discount_type,
                $coupon,
                $image,
                $wa_message,
                $details,
                $is_active
            ]);
        }
    }

    $pdo->commit();
    header('location: admin.php?slp_saved=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die('error: ' . $e->getMessage());
}
