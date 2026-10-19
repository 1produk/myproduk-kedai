<?php
// install.php (jalankan sekali jika ingin tambah 9 product slot)
require __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    // if no products, insert 9 blank slots
    $stmt = $pdo->query("select count(*) as c from products");
    $c = $stmt->fetch()['c'];
    if ($c == 0) {
        $insert = $pdo->prepare("insert into products (position, name, price, discount, discount_type, coupon, image_url, wa_message, details) values (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        for ($i=0;$i<9;$i++){
            $insert->execute([
                $i,
                "produk ".($i+1),
                "0.00",
                "0.00",
                "percent",
                "",
                "",
                "nama produk : {name}\nharga : {price}\ndiskaun : {discount}\nharga selepas diskaun : {selepasdiskaun}\nkupon : {coupon}\n\nsila isi nama anda (pembeli)\nnama : \nalamat : \nno tel : \nemail : ",
                "tiada maklumat tambahan."
            ]);
        }
        echo "ok: 9 produk dimasukkan.\n";
    } else {
        echo "produk sedia ada: {$c}\n";
    }
} catch (Exception $e) {
    echo "error: ".$e->getMessage();
}
