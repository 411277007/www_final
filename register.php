<?php
// register.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

// 接收前端傳來的 JSON 資料
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($username) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "帳號或密碼不能為空！"]);
    exit;
}

try {
    // 1. 檢查帳號是否已經被註冊過
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "該帳號已被註冊，請換一個！"]);
        exit;
    }

    // 2. 將密碼安全加密 (不存明文)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 3. 寫入資料庫
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashedPassword]);

    echo json_encode(["status" => "success", "message" => "註冊成功！歡迎加入。"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "系統錯誤: " . $e->getMessage()]);
}
?>