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
<title>Dasar Privasi | <?php echo esc($company); ?></title>
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
        <h1>Dasar Privasi</h1>
        <p>Kemas kini terakhir: <?php echo date('d M Y'); ?></p>
    </div>

    <div class="legal-card">
        <h2>1. Pengenalan</h2>
        <p><?php echo esc($company); ?> menghormati privasi setiap pelawat dan pelanggan. Dasar ini menerangkan bagaimana maklumat anda dikumpul, digunakan dan dilindungi apabila anda melayari laman web ini.</p>

        <h2>2. Maklumat Yang Dikumpul</h2>
        <ul>
            <li>Maklumat yang anda berikan sendiri semasa membuat tempahan melalui WhatsApp (contoh: nama, alamat, nombor telefon).</li>
            <li>Data teknikal am seperti jenis peranti dan aktiviti melayari laman (untuk tujuan penambahbaikan laman sahaja).</li>
        </ul>

        <h2>3. Penggunaan Maklumat</h2>
        <p>Maklumat yang dikumpul hanya digunakan untuk memproses tempahan, menghubungi pelanggan berkaitan pesanan, dan menambah baik perkhidmatan kami. Kami tidak menjual atau berkongsi maklumat peribadi anda dengan pihak ketiga tanpa kebenaran.</p>

        <h2>4. Perkongsian Data</h2>
        <p>Maklumat tempahan hanya digunakan secara dalaman untuk memproses dan menghantar produk kepada anda.</p>

        <h2>5. Hubungi Kami</h2>
        <p>Jika anda mempunyai sebarang pertanyaan mengenai dasar privasi ini, sila hubungi kami<?php if($wa_main): ?> melalui <a href="https://wa.me/<?php echo $wa_main; ?>" target="_blank">WhatsApp</a><?php endif; ?>.</p>
    </div>

    <div style="text-align:center">
        <a href="index.php" class="back-home">&larr; Kembali ke Laman Utama</a>
    </div>
</div>
</body>
</html>
