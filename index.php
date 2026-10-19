<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/config/config.php';

$pdo = new PDO($dsn, $db_user, $db_pass, $options);

// SETTINGS
$stmt = $pdo->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
$settings = $stmt->fetch();

// PRODUCTS ranking top 3 automatik
$stmt = $pdo->query("
    select * from products
where is_active = 1
order by clicks desc, position asc
limit 9
");

$products = $stmt->fetchAll();

function esc($s){
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'utf-8');
}

$banner_img = $settings['company_banner'] ?? '';
$wa_main = preg_replace('/\D+/', '', $settings['whatsapp'] ?? '');

// ── META: auto-detect domain & URL semasa dari server ──
// Ini penting — Facebook scraper mesti dapat URL yang sama dengan domain sebenar
$_proto    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host     = $_SERVER['HTTP_HOST'];                        // contoh: mykedai.com
$_path     = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // contoh: /kedaiv2
$site_base = $_proto . '://' . $_host . $_path;           // https://mykedai.com/kedaiv2
$current_url = $site_base . '/';

// ── Gambar OG: guna gambar produk pertama (aktif) ──
// Ambil image_url dari DB — boleh jadi full URL atau path relatif
$og_img_raw = !empty($products[0]['image_url']) ? $products[0]['image_url'] : '';

if ($og_img_raw && strpos($og_img_raw, 'http') === 0) {
    // Sudah full URL — guna terus
    $og_img_url = $og_img_raw;
} elseif ($og_img_raw) {
    // Path relatif → tukar ke full URL
    $og_img_clean = ltrim(str_replace(['../', './'], '', $og_img_raw), '/');
    $og_img_url   = $site_base . '/' . $og_img_clean;
} else {
    $og_img_url = $site_base . '/uploads/no-image.png';
}

// ── Detect jenis MIME gambar dari extension ──
$_ext     = strtolower(pathinfo($og_img_url, PATHINFO_EXTENSION));
$_mime_map = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
               'png' => 'image/png',  'webp'  => 'image/webp',
               'gif' => 'image/gif'];
$og_img_type = $_mime_map[$_ext] ?? 'image/jpeg';

$og_title       = $settings['company_name'] ?? 'Kedai Mini Online';
$og_description = ($settings['company_name'] ?? 'Kedai Mini Online') . ' — Pilih produk berkualiti dengan harga terbaik. Dapatkan tawaran eksklusif sekarang!';
?>
<!doctype html>
<html lang="ms">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<!-- ═══════════════════════════════════════════
     SEO — Asas
════════════════════════════════════════════ -->
<title><?php echo esc($og_title); ?> | Kedai Mini Online</title>
<meta name="description"    content="<?php echo esc($og_description); ?>">
<meta name="robots"         content="index, follow">
<link rel="canonical"       href="<?php echo $site_base; ?>/">

<!-- ═══════════════════════════════════════════
     Open Graph — Facebook, WhatsApp, Telegram
     PENTING: og:url MESTI sama dengan domain sebenar laman
════════════════════════════════════════════ -->
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="<?php echo esc($og_title); ?>">
<meta property="og:title"       content="<?php echo esc($og_title); ?>">
<meta property="og:description" content="<?php echo esc($og_description); ?>">
<meta property="og:url"         content="<?php echo esc($current_url); ?>">
<meta property="og:locale"      content="ms_MY">

<!-- Gambar thumbnail
     · Saiz minimum: 200×200px (Facebook akan tolak yang lebih kecil)
     · Saiz optimum: 1200×630px (nisbah 1.91:1)
     · Width/Height TIDAK disertakan — biar Facebook ukur sendiri
       (hardcode nilai salah lebih teruk dari tiada nilai)          -->
<meta property="og:image"            content="<?php echo esc($og_img_url); ?>">
<meta property="og:image:secure_url" content="<?php echo esc($og_img_url); ?>">
<meta property="og:image:type"       content="<?php echo $og_img_type; ?>">
<meta property="og:image:alt"        content="<?php echo esc($og_title); ?>">

<!-- ═══════════════════════════════════════════
     Twitter / X Card — summary_large_image
════════════════════════════════════════════ -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?php echo esc($og_title); ?>">
<meta name="twitter:description" content="<?php echo esc($og_description); ?>">
<meta name="twitter:image"       content="<?php echo esc($og_img_url); ?>">
<meta name="twitter:image:alt"   content="<?php echo esc($og_title); ?>">
<!-- Jika ada akaun Twitter/X: -->
<!-- <meta name="twitter:site" content="@namatwitter"> -->

<link rel="stylesheet" href="assets/css/style.css">

<style>
body{margin:0;font-family:poppins,sans-serif}

/* ================= HERO ================= */
.hero-banner{
    position:relative;height:90vh;min-height:480px;
    overflow:hidden;border-bottom-left-radius:40px;border-bottom-right-radius:40px;
}
.hero-banner img{width:100%;height:100%;object-fit:cover}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(rgba(0,0,0,.0),rgba(0,0,0,.0))}
.hero-content{
    position:absolute;inset:0;display:flex;flex-direction:column;
    justify-content:center;align-items:center;text-align:center;color:#fff;padding:20px;
}
.hero-content h1{font-size:clamp(30px,4vw,48px);margin-bottom:14px}
.hero-content p{font-size:18px;max-width:720px;margin-bottom:22px}
.hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.hero-btn{padding:14px 28px;border-radius:30px;font-weight:700;text-decoration:none}
.hero-btn.primary{background:#25d366;color:#fff}
.hero-btn.secondary{border:2px solid #fff;color:#fff}
.hero-btn.secondary-dark{border:2px solid #222;color:#222}
.hero-btn{transition:.2s ease;}
.hero-btn:hover{transform:translateY(-2px);filter:brightness(1.05)}

/* ================= HERO FALLBACK (tiada banner) ================= */
.hero-fallback{
    text-align:center;padding:70px 20px 50px;
    background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;
    border-bottom-left-radius:40px;border-bottom-right-radius:40px;
}
.hero-fallback h1{font-size:clamp(28px,4vw,44px);margin:0 0 12px}
.hero-fallback p{font-size:17px;max-width:600px;margin:0 auto 22px;opacity:.9}
.hero-fallback .hero-actions{display:flex;gap:12px;flex-wrap:wrap;justify-content:center}

/* ================= TRUST STRIP ================= */
.trust-strip{
    display:flex;flex-wrap:wrap;justify-content:center;gap:12px 28px;
    max-width:1100px;margin:26px auto 6px;padding:16px 20px;
}
.trust-item{
    display:flex;align-items:center;gap:8px;
    font-size:14px;font-weight:600;color:#333;
    background:#fff;border:1px solid #eee;border-radius:30px;
    padding:8px 16px;box-shadow:0 2px 8px rgba(0,0,0,.04);
}
.trust-ico{font-size:16px}
@media(max-width:576px){
    .trust-strip{gap:8px;padding:10px}
    .trust-item{font-size:12.5px;padding:7px 12px}
}

/* ================= GRID 3x3 ================= */
.slp-grid{
    display:grid;grid-template-columns:repeat(3,1fr);
    gap:20px;max-width:1200px;margin:30px auto;padding:0 15px;
}
@media(max-width:992px){.slp-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:576px){.slp-grid{grid-template-columns:1fr}}

.slp-product{
    border:1px solid #eee;border-radius:10px;padding:15px;background:#fafafa;
    transition:.25s;text-align: center; 
}
.slp-product:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}
.slp-product img{
    width:100%;height:350px;object-fit:cover;border-radius:10px;
}

/* ================= BODY SCROLL (GAMBAR + DETAIL) ================= */
#slpDetailBody{
    flex:1 1 auto;
    overflow-y:auto;
    padding: 0 12px;
    display: flex;
    flex-direction: column;
    align-items: center;  /* semua item center */
    text-align: center;   /* teks center */
}

#slpDetailImg{
    max-width:100%;
    max-height:700px; /* lebih besar dari 260px */
    width:auto;       /* biar ikut ratio sebenar */
    height:auto;      /* biar ikut ratio sebenar */
    object-fit:contain;
    border-radius:8px;
    margin:12px 0;
}

#slpDetailText{
    white-space:pre-line;
    margin:12px 0;
}

#modalBeliBtn,
#copyLinkBtn{
    display:inline-block;
    margin:8px 0; /* spacing antara button */
}




/* ================= BUTTON ================= */
.btn,.btn-primary{
    padding:10px 20px;border-radius:8px;text-decoration:none;
    font-weight:600;font-size:14.5px;display:inline-block;
    border:none;cursor:pointer;transition:.2s ease;
}
.btn{background:#eef1f5;color:#222}
.btn:hover{background:#e2e6ec;transform:translateY(-1px)}
.btn-primary{background:#25d366;color:#fff;box-shadow:0 3px 10px rgba(37,211,102,.3)}
.btn-primary:hover{background:#1fb956;transform:translateY(-1px)}

/* stack buttons full-width nicely on small screens */
@media(max-width:576px){
    .slp-product .btn,.slp-product .btn-primary{width:100%;box-sizing:border-box}
}

/* ================= HARGA ================= */
.price-original{text-decoration:line-through;color:#999;font-size:14px}
.price-save{font-size:13px;color:#0a7a3d;font-weight:600}
.price-after-wrap{margin:12px 0;text-align:center}
.price-after-label{font-size:14px;color:#666}
.price-after-value{
    font-size:28px;font-weight:600;color:#c0392b;background:#fff0f0;
    padding:8px 16px;border-radius:10px;display:inline-block;
    transition:.25s ease;
}
.slp-product:hover .price-after-value{
    transform:scale(1.06);
    box-shadow:0 6px 18px rgba(128,0,0,.25);
}

/* ================= MODAL DETAIL PRODUK ================= */
#slpDetailModal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
}

#slpDetailContent {
    background: #fff;
    max-width: 520px;
    width: 95%;
    max-height: 90vh;
    margin: 5vh auto;
    padding: 20px;
    border-radius: 12px;
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

#slpDetailClose {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 28px;
    font-weight: bold;
    color: #333;
    background: rgba(255,255,255,0.8);
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    line-height: 36px;
    text-align: center;
    cursor: pointer;
    z-index: 9999;
}

#slpDetailBody {
    flex: 1 1 auto;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 10px;
    gap: 12px;
}

#slpDetailImg {
    width: 100%;
    max-height: 60vh; /* besar ikut screen */
    object-fit: contain;
    border-radius: 8px;
}

#slpDetailText {
    white-space: pre-line;
    margin: 8px 0;
    font-size: 16px;
}

.modal-price-sticky {
    position: sticky;
    top: 0;
    background: #fff;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    text-align: center;
    z-index: 2;
}

/* BUTTONS kekal saiz asal */
#modalBeliBtn, #copyLinkBtn {
    display: inline-block;
    margin-top: 10px;
    /* jangan ubah padding & width, ikut style asal */
}

/* responsive mobile */
@media(max-width:576px){
    #slpDetailContent {
        width: 95%;
        max-height: 85vh;
        padding: 15px;
    }
    #slpDetailImg {
        max-height: 50vh;
    }
    #slpDetailText {
        font-size: 14px;
    }
    /* jangan ubah button, kekalkan saiz asal */
}

/* ================= FLOAT ================= */
.floating-wa{
    position:fixed;right:18px;bottom:18px;background:#25d366;color:#fff;
    padding:14px 18px;border-radius:50px;text-decoration:none;font-weight:600;
    z-index:9999;
}

/* ================= BUTIRAN SYARIKAT ================= */
.company-details{
    max-width:900px;margin:40px auto 20px;padding:20px;background:#f9f9f9;border-radius:10px;text-align:center;
}
.company-details p{white-space:pre-line;margin:6px 0;}

.slp-product{
    position:relative;
}

.hot-badge{
    position:absolute;
    top:12px;
    right:12px;
    background:#e63946;
    color:#fff;
    padding:6px 10px;
    font-size:12px;
    font-weight:700;
    border-radius:20px;
    box-shadow:0 4px 10px rgba(0,0,0,.25);
}


/* ================= FOOTER ================= */
.site-footer{
    background:#0f172a;
    color:#ccc;
    margin-top:50px;
}

.site-footer-inner{
    max-width:1100px;
    margin:auto;
    padding:30px 20px;
    text-align:center;
}

.site-footer h3{
    color:#fff;
    margin-bottom:10px;
}

.site-footer p{
    margin:6px 0;
    font-size:15px;
}

.site-footer-bottom{
    background:#000;
    color:#888;
    text-align:center;
    padding:12px;
    font-size:13px;
}

.site-footer-address{
    white-space: pre-line;
    line-height:1.6;
}

.site-footer-legal{
    text-align:center;padding:6px 12px 0;font-size:13px;
}
.site-footer-legal a{color:#aab3c5;text-decoration:none}
.site-footer-legal a:hover{color:#fff;text-decoration:underline}
.site-footer-bottom .dot,.site-footer-legal .dot{margin:0 8px;opacity:.5}
.admin-link{color:#666 !important;font-size:12px;opacity:.7;text-decoration:none}
.admin-link:hover{opacity:1;color:#999 !important}



</style>
</head>

<body>

<!-- HERO -->
<?php $tagline = 'Produk pilihan, harga berbaloi — terus dari kedai kami untuk anda.'; ?>
<?php if($banner_img): ?>
<div class="hero-banner">
    <img src="<?php echo esc($banner_img); ?>" alt="<?php echo esc($og_title); ?>" loading="eager" fetchpriority="high">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1><?php echo esc($settings['company_name'] ?? 'Kedai Mini Online'); ?></h1>
        <p><?php echo esc($tagline); ?></p>
<div class="hero-actions">

    <?php if(!empty($settings['show_btn_whatsapp']) && $wa_main): ?>
    <a href="https://wa.me/<?php echo $wa_main; ?>" class="hero-btn primary" target="_blank">WhatsApp</a>
    <?php endif; ?>

    <?php if(!empty($settings['show_btn_produk'])): ?>
    <a href="#produk" class="hero-btn secondary">Lihat Produk</a>
    <?php endif; ?>

</div>

    </div>
</div>
<?php else: ?>
<div class="hero-fallback">
    <h1><?php echo esc($settings['company_name'] ?? 'Kedai Mini Online'); ?></h1>
    <p><?php echo esc($tagline); ?></p>
    <div class="hero-actions">
        <?php if(!empty($settings['show_btn_whatsapp']) && $wa_main): ?>
        <a href="https://wa.me/<?php echo $wa_main; ?>" class="hero-btn primary" target="_blank">WhatsApp</a>
        <?php endif; ?>
        <a href="#produk" class="hero-btn secondary-dark">Lihat Produk</a>
    </div>
</div>
<?php endif; ?>

<!-- TRUST BADGES -->
<div class="trust-strip">
    <div class="trust-item"><span class="trust-ico">✅</span>Produk Original</div>
    <div class="trust-item"><span class="trust-ico">🚚</span>Penghantaran Seluruh Malaysia</div>
    <div class="trust-item"><span class="trust-ico">💬</span>WhatsApp Support</div>
    <div class="trust-item"><span class="trust-ico">🔒</span>Pembayaran Selamat</div>
</div>

<div id="produk" class="slp-grid">
    <?php $rank = 0; ?>
<?php foreach($products as $p):?>
    <?php $rank++; ?>
<?php
$price = (float)$p['price'];
$discount = (float)$p['discount'];
$price_after = ($p['discount_type']==='percent')
    ? $price - ($price*$discount/100)
    : max($price-$discount,0);
$save = $price - $price_after;

$price_f = "MYR".number_format($price,2);
$price_after_f = "MYR".number_format($price_after,2);
$img = str_replace('../', '', $p['image_url']);
$img = $img ?: 'uploads/no-image.png';

// WhatsApp link grid
$wa_text = trim($p['wa_message']) ?: "nama produk : {name}\n*harga selepas diskaun : {selepasdiskaun}*";

$msg = $wa_text;

$msg = str_replace('{name}', $p['name'], $msg);
$msg = str_replace('{price}', $price_f, $msg);
$msg = str_replace('{discount}', $p['discount'], $msg);
$msg = str_replace('{selepasdiskaun}', '*'.$price_after_f.'*', $msg);
$msg = str_replace('{coupon}', $p['coupon'], $msg);

$wa_link = "https://wa.me/$wa_main?text=" . rawurlencode($msg);

?>
<div class="slp-product">
    <?php if($rank <= 3): ?>
    <div class="hot-badge">hot 🔥</div>
<?php endif; ?>

    <img src="<?php echo esc($img); ?>" alt="<?php echo esc($p['name']); ?>" <?php echo $rank <= 3 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'; ?>>
    <h3><?php echo esc($p['name']); ?></h3>

    <div class="price-original">Harga asal: <?php echo $price_f; ?></div>
    <div class="price-save">Jimat RM <?php echo number_format($save,2); ?></div>

    <div class="price-after-wrap">
        <div class="price-after-label">Harga selepas diskaun</div>
        <div class="price-after-value"><?php echo $price_after_f; ?></div>
    </div>

    <!-- DETAIL PRODUK -->
    <a href="javascript:void(0)"
       class="btn js-details"
       data-id="<?php echo (int)$p['id']; ?>"
       data-img="<?php echo esc($img); ?>"
       data-price="<?php echo esc($price_after_f); ?>">
       Detail Produk
    </a>
    <br><br>

    <!-- BUTANG BELI GRID -->
    <a href="<?php echo esc($wa_link); ?>"
   target="_blank"
   class="btn-primary"
   onclick="slpTrackClick(<?php echo $p['id']; ?>)">Beli</a>
</div>
<?php endforeach; ?>
</div>

<!-- FOOTER -->
<footer class="site-footer">

    <?php if(
        !empty($settings['extra_company_name']) ||
        !empty($settings['extra_company_address']) ||
        !empty($settings['extra_company_phone'])
    ): ?>
    <div class="site-footer-inner">
        <?php if(!empty($settings['extra_company_name'])): ?>
            <h3><?php echo esc($settings['extra_company_name']); ?></h3>
        <?php endif; ?>

        <?php if(!empty($settings['extra_company_address'])): ?>
    <p class="site-footer-address">
        <?php echo esc($settings['extra_company_address']); ?>
    </p>
<?php endif; ?>


        <?php if(!empty($settings['extra_company_phone'])): ?>
            <p><?php echo esc($settings['extra_company_phone']); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="site-footer-legal">
        <a href="privacy.php">Dasar Privasi</a>
        <span class="dot">&middot;</span>
        <a href="terms.php">Terma &amp; Syarat</a>
    </div>

    <div class="site-footer-bottom">
        &copy; <?php echo date('Y'); ?> <?php echo esc($settings['copyright_text'] ?? 'myproduk.com'); ?>
        | <?php echo esc($settings['company_name'] ?? 'nama syarikat'); ?>
        <span class="dot">&middot;</span>
        <a href="admin/admin.php" class="admin-link" title="Admin">Admin</a>
    </div>

</footer>



<!-- MODAL DETAIL PRODUK -->
<div id="slpDetailModal">
    <div id="slpDetailContent">
        <button id="slpDetailClose">&times;</button>

        <div class="modal-price-sticky">
            <div class="price-after-label">Harga selepas diskaun</div>
            <div class="price-after-value" id="modalPriceAfter"></div>
        </div>

        <div id="slpDetailBody">
            <img id="slpDetailImg" alt="gambar produk">
            <div id="slpDetailText"></div>
            <a href="#" id="modalBeliBtn" class="btn-primary" target="_blank">Beli</a>
            <!-- DALAM MODAL -->
<a href="javascript:void(0)" id="copyLinkBtn" class="btn" style="margin-top:10px;">Share Link</a>

        </div>
    </div>
</div>



<script>
document.querySelectorAll('.js-details').forEach(btn=>{
    btn.onclick = () => {

        // buka modal
        const modal = document.getElementById('slpDetailModal');
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        // set awal
        document.getElementById('slpDetailImg').src = btn.dataset.img;
        document.getElementById('modalPriceAfter').innerText = btn.dataset.price;
        document.getElementById('slpDetailText').innerText = 'memuatkan...';

        // fetch detail
        fetch('admin/ajax_product_detail.php?id=' + btn.dataset.id)
        .then(r => r.json())
.then(d => {
    document.getElementById('slpDetailText').innerText =
        d.detail || 'tiada maklumat lanjut';

    const wa_main = '<?php echo $wa_main; ?>';
    const wa_template = d.wa_message || 
        "nama produk: {name}\n*harga selepas diskaun: {selepasdiskaun}*";

    let msg = wa_template;

    msg = msg.replaceAll("{name}", d.name || "");
    msg = msg.replaceAll("{price}", d.price || "");
    msg = msg.replaceAll("{discount}", d.discount || "");
    msg = msg.replaceAll("{selepasdiskaun}", "*" + (btn.dataset.price || "") + "*");
    msg = msg.replaceAll("{coupon}", d.coupon || "");

    const wa_link = "https://wa.me/" + wa_main + "?text=" +
        encodeURIComponent(msg);

    document.getElementById('modalBeliBtn').href = wa_link;
})

        .catch(() => {
            document.getElementById('slpDetailText').innerText =
                'ralat memuatkan maklumat';
            document.getElementById('modalBeliBtn').href = '#';
        });

        // copy link -WAJIB TUKAR NAMA FOLDER
document.getElementById('copyLinkBtn').onclick = () => {

    const basePath = window.location.pathname
        .substring(0, window.location.pathname.indexOf('/', 1));

    const url = window.location.origin +
        basePath +
        "/admin/produk.php?id=" + btn.dataset.id;

            navigator.clipboard.writeText(url)
                .then(() => alert('Link produk disalin. Sila kongsikan dengan saudara-mara atau rakan anda.'))
                .catch(() => alert('gagal copy link'));
        };
    };
});

// close modal
document.getElementById('slpDetailClose').onclick = () => {
    document.getElementById('slpDetailModal').style.display = 'none';
    document.body.style.overflow = '';
};
document.getElementById('slpDetailModal').onclick = e => {
    if (e.target.id === 'slpDetailModal') {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
};
</script>
   


<!-- FLOATING WHATSAPP -->
<?php if(!empty($settings['extra_company_whatsapp'])):
$wa_float = preg_replace('/\D+/', '', $settings['extra_company_whatsapp']);
?>
<a href="https://wa.me/<?php echo $wa_float; ?>" class="floating-wa" target="_blank">
<!-- ICON WHATSAPP -->
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="#fff">
        <path d="M20.52 3.48a11.953 11.953 0 0 0-16.97 0 11.963 11.963 0 0 0-3.5 8.49c0 2.12.55 4.18 1.6 6.01L0 24l6.17-2.06a11.91 11.91 0 0 0 6.01 1.6c2.83 0 5.73-1.06 7.82-3.15a11.963 11.963 0 0 0 0-16.97zm-8.53 16.54a9.76 9.76 0 0 1-5.25-1.54l-.37-.23-3.67 1.22 1.24-3.58-.24-.38a9.76 9.76 0 0 1-1.54-5.25 9.828 9.828 0 1 1 17 6.93 9.828 9.828 0 0 1-6.94 2.83zm5.08-7.09c-.28-.14-1.66-.82-1.92-.92-.26-.1-.45-.14-.64.14s-.73.92-.9 1.11c-.16.19-.33.21-.61.07-.28-.14-1.18-.43-2.25-1.38-.83-.74-1.39-1.65-1.55-1.92-.16-.28-.02-.43.12-.57.12-.12.28-.33.42-.49.14-.16.19-.28.28-.46.09-.19.05-.35-.02-.49-.07-.14-.64-1.54-.88-2.12-.23-.56-.46-.48-.64-.49-.16-.01-.35-.01-.54-.01-.19 0-.49.07-.75.35s-1 1-.98 2.45c.01 1.44 1.02 2.83 1.16 3.03.14.19 2 3.05 4.85 4.27.68.29 1.21.46 1.62.59.68.22 1.3.19 1.79.12.55-.08 1.66-.68 1.9-1.33.23-.65.23-1.2.16-1.33-.07-.14-.26-.22-.54-.36z"/>
    </svg>
</a>
<?php endif; ?>

<script>
function slpTrackClick(id){
    fetch('auth/track_click.php', {
        method: 'post',
        headers: {'content-type':'application/x-www-form-urlencoded'},
        body: 'id=' + id
    });
}
</script>



</body>
</html>
