<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db_config.php';

// 獲取請求方法 (GET 或 POST)
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // 【讀取資料】
    $email = $_GET['email'] ?? '';
    if (empty($email)) {
        echo json_encode(["status" => "error", "message" => "缺少 Email 參數"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, email, name, gender, phone, birthday FROM users_profile WHERE email = ?");
    $stmt->execute([$email]);
    $userProfile = $stmt->fetch();

    if ($userProfile) {
        echo json_encode(["status" => "success", "data" => $userProfile]);
    } else {
        echo json_encode(["status" => "empty", "message" => "尚未建立個人資料"]);
    }
} 
elseif ($method === 'POST') {
    // 【儲存/更新資料】
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || empty($data['email']) || empty($data['name'])) {
        echo json_encode(["status" => "error", "message" => "欄位資料不完整"]);
        exit;
    }

    try {
        // 💡 解決關鍵：將 INSERT 用的參數與 UPDATE 用的參數名稱分開 (加上 up_ 前綴)
        $sql = "INSERT INTO users_profile (email, name, gender, phone, birthday) 
                VALUES (:ins_email, :ins_name, :ins_gender, :ins_phone, :ins_birthday)
                ON DUPLICATE KEY UPDATE 
                name = :up_name, 
                gender = :up_gender, 
                phone = :up_phone, 
                birthday = :up_birthday";
        
        $stmt = $pdo->prepare($sql);
        
        // 🎯 在 execute 裡面，把每一個宣告的參數都明確對應好
        $stmt->execute([
            // INSERT 區塊的參數
            ':ins_email'    => $data['email'],
            ':ins_name'     => $data['name'],
            ':ins_gender'   => $data['gender'],
            ':ins_phone'    => $data['phone'],
            ':ins_birthday' => $data['birthday'],
            
            // UPDATE 區塊的參數
            ':up_name'      => $data['name'],
            ':up_gender'    => $data['gender'],
            ':up_phone'     => $data['phone'],
            ':up_birthday'  => $data['birthday']
        ]);

        // 重新查詢更新後的資料（以便獲取資料庫自動生成的 ID）
        $stmtGet = $pdo->prepare("SELECT id FROM users_profile WHERE email = ?");
        $stmtGet->execute([$data['email']]);
        $savedUser = $stmtGet->fetch();

        echo json_encode([
            "status" => "success", 
            "message" => "個人資料已儲存至資料庫",
            "db_id" => $savedUser['id']
        ]);

    } catch (PDOException $e) {
        // 如果還有其他錯誤，會在這裡噴出詳細原因
        echo json_encode(["status" => "error", "message" => "資料庫寫入失敗: " . $e->getMessage()]);
    }
}
?>