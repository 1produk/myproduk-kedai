<?php
require __DIR__ . '/../config/config.php';
$pdo = new PDO($dsn, $db_user, $db_pass, $options);

// SETTINGS
$stmt = $pdo->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
$settings = $stmt->fetch();

// PRODUCTS
$stmt = $pdo->query("SELECT * FROM products ORDER BY position ASC");
$products = $stmt->fetchAll();

function esc($s){
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'utf-8');
}

$banner_img = $settings['company_banner'] ?? '';
$wa_main = preg_replace('/\D+/', '', $settings['whatsapp'] ?? '');
?>
<!doctype html>
<html lang="ms">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo esc($settings['company_name'] ?? 'mykedai'); ?></title>

<link rel="stylesheet" href="assets/style.css">

<style>
body{margin:0;font-family:poppins,sans-serif}

/* ================= HERO ================= */
.hero-banner{
    position:relative;height:90vh;min-height:480px;
    overflow:hidden;border-bottom-left-radius:40px;border-bottom-right-radius:40px;
}
.hero-banner img{width:100%;height:100%;object-fit:cover}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.25))}
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
    max-height:260px;
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
.btn{padding:8px 14px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block}
.btn-primary{padding:10px 18px;background:#25d366;color:#fff;border-radius:6px;text-decoration:none;font-weight:600}

/* ================= HARGA ================= */
.price-original{text-decoration:line-through;color:#999;font-size:14px}
.price-save{font-size:13px;color:#0a7a3d;font-weight:600}
.price-after-wrap{margin:12px 0;text-align:center}
.price-after-label{font-size:14px;color:#666}
.price-after-value{
    font-size:28px;font-weight:900;color:#800000;background:#fff0f0;
    padding:8px 16px;border-radius:10px;display:inline-block;
    transition:.25s ease;
}
.slp-product:hover .price-after-value{
    transform:scale(1.06);
    box-shadow:0 6px 18px rgba(128,0,0,.25);
}

/* ================= MODAL ================= */
#slpDetailModal{
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);
    z-index:9999;
}
#slpDetailContent{
    background:#fff;
    max-width:520px;width:95%;
    height:85vh;
    margin:6vh auto;
    padding:20px;
    border-radius:10px;
    position:relative;
    display:flex;flex-direction:column;
}
#slpDetailClose{
    position:absolute;top:8px;right:10px;
    background:none;border:0;font-size:22px;cursor:pointer;
}
.modal-price-sticky{
    position:sticky;top:0;background:#fff;padding:10px 0;
    border-bottom:1px solid #eee;
    text-align:center;z-index:2;
}

/* ================= BODY SCROLL (GAMBAR + DETAIL) ================= */
#slpDetailBody{
    flex:1 1 auto;
    overflow-y:auto;
    padding-right:6px;
}
#slpDetailImg{
    max-width:100%;
    max-height:260px;
    object-fit:contain;
    border-radius:8px;
    margin:12px 0;
}
#slpDetailText{
    white-space:pre-line;
}
#modalBeliBtn{
    display:inline-block;
    margin-top:12px;
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
</style>
</head>

<body>
    
    

<!-- HERO -->
<?php if($banner_img): ?>
<div class="hero-banner">
    <img src="<?php echo esc($banner_img); ?>">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1><?php echo esc($settings['company_name']); ?></h1>
        <p>Produk pilihan terbaik dengan harga berpatutan</p>
        <div class="hero-actions">
            <?php if($wa_main): ?>
            <a href="https://wa.me/<?php echo $wa_main; ?>" class="hero-btn primary" target="_blank">WhatsApp</a>
            <?php endif; ?>
            <a href="#produk" class="hero-btn secondary">Lihat Produk</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ADMIN -->
<a href="admin.php"
style="position:fixed;top:15px;right:15px;background:#000;color:#fff;
padding:10px 18px;border-radius:6px;text-decoration:none;z-index:99999;">
 ☰
</a>

<div style="text-align:center;margin:20px 0;">
    <h1></h1>
    <p style="font-size:32px; font-weight:400; margin:12px 0;">
        “...Kualiti yang diutamakan...”
    </p>
</div>

<div id="produk" class="slp-grid">
<?php foreach($products as $p):

$price = (float)$p['price'];
$discount = (float)$p['discount'];
$price_after = ($p['discount_type']==='percent')
    ? $price - ($price*$discount/100)
    : max($price-$discount,0);
$save = $price - $price_after;

$price_f = "MYR".number_format($price,2);
$price_after_f = "MYR".number_format($price_after,2);
$img = $p['image_url'] ?: 'uploads/no-image.png';

// WhatsApp link grid
$wa_text = trim($p['wa_message']) ?: "nama produk : {name}\n*harga selepas diskaun : {selepasdiskaun}*";
$wa_link = "https://wa.me/$wa_main?text=".rawurlencode(
    str_replace(['{name}','{selepasdiskaun}'], [$p['name'],$price_after_f], $wa_text)
);
?>
<div class="slp-product">
    <img src="<?php echo esc($img); ?>">
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
    <a href="<?php echo esc($wa_link); ?>" class="btn-primary" target="_blank">Beli</a>
</div>
<?php endforeach; ?>
</div>

<!-- BUTIRAN SYARIKAT -->
<?php if(!empty($settings['extra_company_name']) || !empty($settings['extra_company_address']) || !empty($settings['extra_company_phone'])): ?>
<div class="company-details">
    <h3><?php echo esc($settings['extra_company_name']); ?></h3>
    <p><?php echo esc($settings['extra_company_address']); ?></p>
    <p><?php echo esc($settings['extra_company_phone']); ?></p>
</div>
<?php endif; ?>

<p style="margin-top:40px;font-size:14px;color:#777;text-align:center;">
&copy; <?php echo esc($settings['copyright_text'] ?? 'myproduk.com'); ?>
</p>

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
        fetch('ajax_product_detail.php?id=' + btn.dataset.id)
        .then(r => r.json())
        .then(d => {
            document.getElementById('slpDetailText').innerText =
                d.detail || 'tiada maklumat lanjut';

            const wa_main = '<?php echo $wa_main; ?>';
            const wa_template = d.wa_message || 
                "nama produk: {name}\n*harga selepas diskaun: {selepasdiskaun}*";

            const wa_link = "https://wa.me/" + wa_main + "?text=" +
                encodeURIComponent(
                    wa_template
                        .replace("{name}", d.name)
                        .replace("{selepasdiskaun}", btn.dataset.price)
                );

            document.getElementById('modalBeliBtn').href = wa_link;
        })
        .catch(() => {
            document.getElementById('slpDetailText').innerText =
                'ralat memuatkan maklumat';
            document.getElementById('modalBeliBtn').href = '#';
        });

        // copy link
        document.getElementById('copyLinkBtn').onclick = () => {
            const url = window.location.origin +
                "/pakej/produk.php?id=" + btn.dataset.id;

            navigator.clipboard.writeText(url)
                .then(() => alert('Link produk disalin. Sila kongsikan dengan saudara-mara atau rakan anda. Kami menanti anda di sana.'))
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



</body>
</html>
