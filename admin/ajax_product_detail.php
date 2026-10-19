<?php
require __DIR__ . '/../config/config.php';

// NOTA: fail ini dipanggil dari halaman awam (index.php) untuk popup
// "Detail Produk", jadi ia TIDAK memerlukan session admin.
// Data yang di-return hanya maklumat produk yang memang sudah
// dipaparkan secara terbuka di grid (nama, harga, diskaun, details).

$pdo = new PDO($dsn, $db_user, $db_pass, $options);

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    select name, details, wa_message, price, discount, discount_type, coupon
    from products
    where id = ?
    limit 1
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// kira harga selepas diskaun
$price = (float)($data['price'] ?? 0);
$discount = (float)($data['discount'] ?? 0);

if (($data['discount_type'] ?? '') === 'percent') {
    $price_after = $price - ($price * $discount / 100);
} else {
    $price_after = max($price - $discount, 0);
}

header('content-type: application/json; charset=utf-8');
echo json_encode([
    'name' => $data['name'] ?? '',
    'detail' => $data['details'] ?? '',
    'wa_message' => $data['wa_message'] ?? '',
    'price' => 'MYR' . number_format($price, 2),
    'discount' => $data['discount'] ?? '',
    'selepasdiskaun' => 'MYR' . number_format($price_after, 2),
    'coupon' => $data['coupon'] ?? ''
], JSON_UNESCAPED_UNICODE);