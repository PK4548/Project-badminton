<?php
date_default_timezone_set('Asia/Bangkok');

$host = 'localhost';
$db = 'badminton_booking'; // ชื่อฐานข้อมูลที่คุณสร้างใน HeidiSQL
$user = 'root';
$pass = ''; // รหัสผ่าน MySQL ของคุณ
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

define('LINE_LOGIN_CHANNEL_ID', '2009504091');
define('LINE_LOGIN_CHANNEL_SECRET', '7f4ebac16b08f8b70ab9ab7c0fe47643');
define('LINE_LOGIN_CALLBACK_URL', 'http://localhost/term-project-main/term-projectv/line_callback.php');
define('LINE_MESSAGING_CHANNEL_TOKEN', 'nmeuPuYA/wyPhvzQ9E1iOvJgwn6bgdjdMa57I/eb6db2C9/AiwvrKfARoQ1BdF+Ub7b+o0FsAqf9gmSlvALy99WwifJt9GCDjPKq5QYU4uC/lU0Rly+x9LkMc7OGzK6jZ1wVCFtoST9B47VaHcLOGwdB04t89/1O/w1cDnyilFU='); // ใส่ Token เต็มๆ ของคุณที่นี่
?>