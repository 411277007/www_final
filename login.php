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

    // 2. 檢查欄位
    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception("帳號與密碼皆為必填項目！");
    }

    $email = trim($data['email']);
    $password = $data['password'];

    // 3. 從資料庫中搜尋該帳號 (username)
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. 驗證帳號是否存在，以及密碼是否正確
    // 💡 password_verify 會自動解密資料庫裡的 hash 密碼並與明文密碼比對
    if ($user && password_verify($password, $user['password'])) {
        
        // 登入成功，回傳 success
        echo json_encode([
            "status" => "success",
            "message" => "登入成功！",
            "username" => $user['username']
        ]);
        
    } else {
        // 為了安全，通常不具體說明是帳號錯還是密碼錯
        throw new Exception("帳號或密碼錯誤！");
    }

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>