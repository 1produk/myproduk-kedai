<?php
require __DIR__ . '/../config/config.php';

$test_password = '12345'; // <-- pastikan sama dengan password sebenar

echo "<pre>";
echo "admin_user dalam config: {$admin_user}\n";
echo "admin_pass (hash): {$admin_pass}\n\n";

if (password_verify($test_password, $admin_pass)) {
    echo "OK → password '12345' MATCH dengan hash!\n";
} else {
    echo "X → password '12345' TIDAK match dengan hash.\n";
}
