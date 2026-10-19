<?php
// logout.php
require __DIR__ . '/../config/config.php';
// session_start() sudah ada dalam config.php
session_unset();
session_destroy();
header('location: login.php');
exit;
