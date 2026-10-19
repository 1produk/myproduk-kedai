<?php
require __DIR__ . '/../config/config.php';

if (!isset($_POST['id'])) exit;

$id = (int)$_POST['id'];

$pdo = new PDO($dsn, $db_user, $db_pass, $options);

$stmt = $pdo->prepare("update products set clicks = clicks + 1 where id = ?");
$stmt->execute([$id]);
