<?php
header('Content-Type: application/json; charset=utf-8');

// === 資料庫連線設定 ===
$db_host = 'localhost';
$db_name = 'easygo_db';  // ⚠️ 請改成你建立的資料庫名稱
$db_user = 'root';          // ⚠️ 你的資料庫帳號
$db_pass = '';              // ⚠️ 你的資料庫密碼

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. 接收前端經由 FETCH 傳送過來的 JSON 資料
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // 2. 檢查是否有收到必要資料
    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception("帳號與密碼皆為必填項目！");
    }

    $email = trim($data['email']);
    $password = $data['password'];

    // 3. 檢查帳號（username）是否已經存在於資料庫中
    $checkSql = "SELECT COUNT(*) FROM users WHERE username = :username";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':username' => $email]);
    
    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception("該帳號已被註冊！");
    }

    // 4. 將密碼進行安全雜湊加密
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 5. 🎯 寫入資料庫：完全對應你的 users 表欄位 (username, password)
    // (id 與 created_at 資料庫會自動生成，不用手動寫入)
    $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username' => $email,
        ':password' => $hashedPassword
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "註冊成功！帳號已成功寫入資料庫。"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>