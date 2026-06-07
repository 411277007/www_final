<?php
// db.php
$host = '127.0.0.1';
$dbname = 'easygo_db';
$user = 'root'; // 請替換為你的 MySQL 帳號
$pass = '';     // 請替換為你的 MySQL 密碼

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => "資料庫連線失敗: " . $e->getMessage()]));
}
?>