<?php
// login.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

// 接收前端傳來的 JSON 資料
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($username) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "請輸入帳號和密碼！"]);
    exit;
}

try {
    // 1. 從資料庫撈取該帳號的資料
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. 驗證帳號是否存在，以及加密密碼是否相符
    if ($user && password_verify($password, $user['password'])) {
        // 登入成功，回傳帳號給前端
        echo json_encode([
            "status" => "success",
            "message" => "登入成功！",
            "username" => $user['username']
        ]);
    } else {
        // 帳號不存在或密碼錯誤
        echo json_encode(["status" => "error", "message" => "帳號或密碼錯誤，請重新確認！"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "系統錯誤: " . $e->getMessage()]);
}
?>