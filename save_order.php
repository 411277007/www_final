<?php
// 允許跨域（若有需要）並設定回傳格式為 JSON
header('Content-Type: application/json; charset=utf-8');

// 1. 接收前端經由 FETCH 傳送過來的 JSON 資料
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 2. 檢查是否有收到資料
if ($data && !empty($data['name']) && !empty($data['total'])) {
    
    /* 💡 這裡未來可以串接資料庫（MySQL）：
       例如：INSERT INTO orders (username, receiver_name, phone, address, total_price) ...
    */

    // 隨機產生一個 5 位數的訂單編號供前端顯示
    $random_order_id = rand(10000, 99999);

    // 🎯 關鍵：必須回傳 status 為 success 的 JSON 字串
    echo json_encode([
        "status" => "success",
        "message" => "訂單已成功寫入系統",
        "order_id" => $random_order_id
    ]);

} else {
    // 如果資料遺失或解析失敗，回傳錯誤
    echo json_encode([
        "status" => "error",
        "message" => "後端未接收到完整的收件資訊或總額"
    ]);
}
?>