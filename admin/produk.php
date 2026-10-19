<?php
require __DIR__ . '/../config/config.php';
$pdo = new PDO($dsn,$db_user,$db_pass,$options);

// SETTINGS
$stmt2 = $pdo->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
$settings = $stmt2->fetch();
$wa_main = preg_replace('/\D+/', '', $settings['whatsapp'] ?? '');

// GET PRODUCT ID
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$p = $stmt->fetch();

if(!$p){
    echo "<h2>Produk tidak ditemui</h2>";
    exit;
}

// harga selepas diskaun
$price = (float)$p['price'];
$discount = (float)$p['discount'];
$price_after = ($p['discount_type']==='percent')
    ? $price - ($price*$discount/100)
    : max($price-$discount,0);

$price_after_f    = "RM".number_format($price_after,2);
$price_original_f = "RM".number_format($price,2);

$img      = $p['image_url'] ?: '../uploads/no-image.png';
$home_url = dirname(dirname($_SERVER['SCRIPT_NAME'])) . "/index.php";

// ── META: auto-detect domain & URL semasa dari server ──
$_proto    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host     = $_SERVER['HTTP_HOST'];
$_path     = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'); // naik satu level dari /admin
$site_base   = $_proto . '://' . $_host . $_path;
$current_url = $site_base . '/admin/produk.php?id=' . $id;

// ── Gambar produk → pastikan full URL ──
$img_raw = $p['image_url'] ?: '';
if ($img_raw && strpos($img_raw, 'http') === 0) {
    // Sudah full URL
    $og_img = $img_raw;
} elseif ($img_raw) {
    // Path relatif → tukar ke full URL
    $img_clean = ltrim(str_replace(['../', './'], '', $img_raw), '/');
    $og_img    = $site_base . '/' . $img_clean;
} else {
    $og_img = $site_base . '/uploads/no-image.png';
}

// ── Detect jenis MIME gambar dari extension ──
$_ext      = strtolower(pathinfo($og_img, PATHINFO_EXTENSION));
$_mime_map = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
               'png' => 'image/png',  'webp'  => 'image/webp',
               'gif' => 'image/gif'];
$og_img_type = $_mime_map[$_ext] ?? 'image/jpeg';

// Deskripsi: guna details produk (potong 160 aksara)
$details_raw    = trim($p['details'] ?? '');
$og_description = $details_raw
    ? mb_substr(strip_tags($details_raw), 0, 160)
    : $p['name'] . ' — Harga selepas diskaun: ' . $price_after_f . '. Hubungi kami melalui WhatsApp sekarang!';

// Tajuk OG
$og_title = $p['name'] . ' | ' . ($settings['company_name'] ?? 'Kedai Mini Online');
?>
<!doctype html>
<html lang="ms">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- ═══════════════════════════════════════════
     SEO — Asas
════════════════════════════════════════════ -->
<title><?php echo htmlspecialchars($og_title); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($og_description); ?>">
<meta name="robots"      content="index, follow">
<link rel="canonical"    href="<?php echo htmlspecialchars($current_url); ?>">

<!-- ═══════════════════════════════════════════
     Open Graph — Facebook, WhatsApp, Telegram
     PENTING: og:url MESTI sama dengan domain sebenar laman
════════════════════════════════════════════ -->
<meta property="og:type"        content="product">
<meta property="og:site_name"   content="<?php echo htmlspecialchars($settings['company_name'] ?? 'Kedai Mini Online'); ?>">
<meta property="og:title"       content="<?php echo htmlspecialchars($og_title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($og_description); ?>">
<meta property="og:url"         content="<?php echo htmlspecialchars($current_url); ?>">
<meta property="og:locale"      content="ms_MY">

<!-- Gambar thumbnail
     · Saiz minimum: 200×200px (Facebook akan tolak yang lebih kecil)
     · Saiz optimum: 1200×630px (nisbah 1.91:1)
     · Width/Height TIDAK disertakan — biar Facebook ukur sendiri  -->
<meta property="og:image"            content="<?php echo htmlspecialchars($og_img); ?>">
<meta property="og:image:secure_url" content="<?php echo htmlspecialchars($og_img); ?>">
<meta property="og:image:type"       content="<?php echo $og_img_type; ?>">
<meta property="og:image:alt"        content="<?php echo htmlspecialchars($p['name']); ?>">

<!-- Open Graph: maklumat harga produk (Facebook Shops / Catalog) -->
<meta property="product:price:amount"   content="<?php echo number_format($price_after, 2, '.', ''); ?>">
<meta property="product:price:currency" content="MYR">

<!-- ═══════════════════════════════════════════
     Twitter / X Card — summary_large_image
════════════════════════════════════════════ -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?php echo htmlspecialchars($og_title); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($og_description); ?>">
<meta name="twitter:image"       content="<?php echo htmlspecialchars($og_img); ?>">
<meta name="twitter:image:alt"   content="<?php echo htmlspecialchars($p['name']); ?>">
<!-- Jika ada akaun Twitter/X: -->
<!-- <meta name="twitter:site" content="@namatwitter"> -->
<style>
body{
    margin:0;
    font-family:poppins,sans-serif;
    background:#f9f9f9;
    color:#333;
    font-size:18px;
}
html, body{
    height:100%;
}
.container{
    width:95%;
    max-width:800px;
    margin:40px auto;
    padding:24px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 6px 24px rgba(0,0,0,0.1);
    overflow-y:auto;
    display:flex;
    flex-direction:column;
    align-items:center;
    box-sizing:border-box;
    position:relative;
    text-align:center; /* semua text center */
}
.close-btn{
    position:fixed;
    top:12px;
    right:12px;
    background:#800000;
    color:#fff;
    width:44px;
    height:44px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    font-weight:bold;
    z-index:999;
    text-decoration:none;
}
.product-title{
    font-size:32px;
    font-weight:700;
    margin:16px 0;
    line-height:1.3;
}
.product-img{
    width:100%;
    max-width:480px;
    height:auto;
    border-radius:12px;
    margin-bottom:16px;
}
.price-original{
    font-size:18px;
    color:#999;
    text-decoration:line-through;
    margin-bottom:6px;
}
.price-after{
    font-size:28px;
    font-weight:800;
    color:#800000;
    margin-bottom:20px;
}
.details{
    font-size:18px;
    line-height:1.8;
    white-space:pre-line;
    margin-bottom:28px;
    max-width:480px;
    margin-left:auto;
    margin-right:auto;
}
.btn-wa{
    display:block;
    width:100%;
    max-width:480px;
    padding:16px 0;
    background:#25d366;
    color:#fff;
    border-radius:12px;
    text-decoration:none;
    font-weight:700;
    font-size:20px;
    text-align:center;
    transition:background .2s ease;
    margin-bottom:30px;
}
.btn-wa:hover{
    background:#1ebe5d;
}

/* responsive tweaks */
@media (max-width:768px){
    .container{
        width:95%;
        padding:20px;
    }
    .product-title{font-size:28px;}
    .price-after{font-size:24px;}
    .details{font-size:16px;}
    .btn-wa{font-size:18px;padding:14px 0;}
}
@media (max-width:480px){
    .container{
        width:100%;
        margin:0;
        padding:16px;
        border-radius:0;
        height:100%;
    }
    .details{
        max-width:100%;
    }
    .product-img{
        max-width:100%;
    }
}
</style>
</head>
<body>

<a href="<?php echo $home_url; ?>" class="close-btn">&times;</a>

<div class="container">
    <h1 class="product-title"><?php echo htmlspecialchars($p['name']); ?></h1>
    <img class="product-img" src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
    
    <div class="price-original">Harga asal: <?php echo $price_original_f; ?></div>
    <div class="price-after">Harga selepas diskaun: <?php echo $price_after_f; ?></div>
    
    <div class="details"><?php echo nl2br(htmlspecialchars($p['details'] ?? 'Tiada maklumat lanjut')); ?></div>

    <?php if($wa_main):
        if($p['discount_type'] === 'percent'){
            $discount_text = $p['discount'] . "%";
        }else{
            $discount_text = "RM" . number_format($p['discount'],2);
        }
        $coupon_text = $p['coupon'] ?: '-';
        $wa_text = "Nama produk : ".$p['name']."\n".
                   "Harga : ".$price_original_f."\n".
                   "Diskaun : ".$discount_text."\n".
                   "Harga selepas diskaun : ".$price_after_f."\n".
                   "Kupon : ".$coupon_text;
        $wa_link = "https://wa.me/".$wa_main."?text=".rawurlencode($wa_text);
    ?>
    <a href="<?php echo $wa_link; ?>" class="btn-wa" target="_blank">Beli / WhatsApp</a>
    <?php endif; ?>
</div>

</body>
</html>