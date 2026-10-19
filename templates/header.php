<?php
// header.php - template header untuk halaman biasa (bukan WordPress)
// Dipanggil: require __DIR__ . '/../templates/header.php';
?>
<!doctype html>
<html lang="ms">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if (!empty($page_title)): ?>
<title><?php echo htmlspecialchars($page_title); ?></title>
<?php endif; ?>
<link rel="stylesheet" href="<?php echo $base_path ?? ''; ?>assets/css/style.css">
</head>
<body>
