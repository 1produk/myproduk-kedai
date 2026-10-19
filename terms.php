<?php
require __DIR__ . '/config/config.php';
$pdo = new PDO($dsn, $db_user, $db_pass, $options);
$stmt = $pdo->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
$settings = $stmt->fetch();

function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'utf-8'); }

$company = $settings['company_name'] ?? 'Kedai Mini Online';
$wa_main = preg_replace('/\D+/', '', $settings['whatsapp'] ?? '');
?>
<!doctype html>
<html lang="ms">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Terma &amp; Syarat | <?php echo esc($company); ?></title>
<meta name="robots" content="index, follow">
<link rel="stylesheet" href="assets/css/style.css">
<style>
body{margin:0;font-family:poppins,sans-serif;background:#f6f7fb;color:#222}
.legal-wrap{max-width:800px;margin:0 auto;padding:40px 20px 60px}
.legal-header{text-align:center;margin-bottom:30px}
.legal-header a{color:#25d366;text-decoration:none;font-weight:600;font-size:14px}
.legal-header h1{font-size:clamp(24px,4vw,32px);margin:14px 0 4px}
.legal-header p{color:#777;font-size:14px}
.legal-card{background:#fff;border:1px solid #eee;border-radius:12px;padding:26px}
.legal-card h2{font-size:18px;margin:26px 0 8px}
.legal-card h2:first-child{margin-top:0}
.legal-card p,.legal-card li{font-size:15px;line-height:1.7;color:#333}
.legal-card ul{padding-left:20px}
.back-home{display:inline-block;margin-top:26px;color:#25d366;text-decoration:none;font-weight:600}
@media(max-width:576px){.legal-card{padding:18px}}
</style>
</head>
<body>
<div class="legal-wrap">
    <div class="legal-header">
        <a href="index.php">&larr; Kembali ke Laman Utama</a>
        <h1>Terma &amp; Syarat</h1>
        <p>Kemas kini terakhir: <?php echo date('d M Y'); ?></p>
    </div>

    <div class="legal-card">
        <h2>1. Penerimaan Terma</h2>
        <p>Dengan melayari dan membuat pembelian di laman <?php echo esc($company); ?>, anda bersetuju untuk terikat dengan terma dan syarat yang dinyatakan di sini.</p>

        <h2>2. Pesanan &amp; Pembayaran</h2>
        <ul>
            <li>Semua tempahan dibuat melalui WhatsApp dan disahkan secara manual oleh pihak kami.</li>
            <li>Harga yang dipaparkan adalah dalam Ringgit Malaysia (MYR) dan boleh berubah tanpa notis awal.</li>
            <li>Pesanan dianggap sah selepas pembayaran diterima dan disahkan.</li>
        </ul>

        <h2>3. Penghantaran</h2>
        <p>Tempoh penghantaran bergantung kepada lokasi dan kaedah penghantaran yang dipilih. Sebarang kelewatan di luar kawalan kami (contoh: kurier, cuaca) tidak menjadi tanggungjawab pihak kedai.</p>

        <h2>4. Pemulangan &amp; Pertukaran</h2>
        <p>Sila hubungi kami dalam tempoh 24 jam selepas menerima produk sekiranya terdapat sebarang masalah (kerosakan/kesilapan barang) untuk proses pertukaran.</p>

        <h2>5. Pertanyaan</h2>
        <p>Sebarang pertanyaan berkaitan terma ini boleh dihubungi<?php if($wa_main): ?> melalui <a href="https://wa.me/<?php echo $wa_main; ?>" target="_blank">WhatsApp</a><?php endif; ?>.</p>
    </div>

    <div style="text-align:center">
        <a href="index.php" class="back-home">&larr; Kembali ke Laman Utama</a>
    </div>
</div>
</body>
</html>
