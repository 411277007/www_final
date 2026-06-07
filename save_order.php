<?php
// 設定回傳格式為 JSON
header('Content-Type: application/json; charset=utf-8');

// === 資料庫連線設定 ===
$db_host = 'localhost';
$db_name = 'easygo_db';  // ⚠️ 請改成你建立的資料庫名稱
$db_user = 'root';          // ⚠️ 你的資料庫帳號
$db_pass = '';              // ⚠️ 你的資料庫密碼

try {
    // 建立 PDO 資料庫連線
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. 接收前端經由 FETCH 傳送過來的 JSON 資料
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // 2. 檢查是否有收到必要資料
    if ($data && !empty($data['name']) && !empty($data['total'])) {
        
        // 🔑 啟動資料庫交易模式，確保主表、明細表要嘛同時成功，要嘛同時失敗
        $pdo->beginTransaction();

        $name       = $data['name'];
        $phone      = $data['phone'] ?? '';
        $address    = $data['address'] ?? '';
        $total      = $data['total'];
        $cart_items = $data['cart_items'] ?? []; // 接收前端傳過來的購物車陣列

        // 3. 先寫入訂單主表 `orders`
        $sql1 = "INSERT INTO orders (customer_name, customer_phone, shipping_address, total_amount) 
                 VALUES (:customer_name, :customer_phone, :shipping_address, :total_amount)";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([
            ':customer_name'    => $name,
            ':customer_phone'   => $phone,
            ':shipping_address' => $address,
            ':total_amount'     => $total
        ]);

        // 取得剛剛主表自動生成的訂單流水號 (id)
        $real_order_id = $pdo->lastInsertId();

        // 4. 用迴圈將購買的每一樣商品逐筆寫入明細表 `order_items`
        if (!empty($cart_items) && is_array($cart_items)) {
            $sql2 = "INSERT INTO order_items (order_id, product_name, price, qty) 
                     VALUES (:order_id, :product_name, :price, :qty)";
            $stmt2 = $pdo->prepare($sql2);

            foreach ($cart_items as $item) {
                $stmt2->execute([
                    ':order_id'     => $real_order_id,
                    ':product_name' => $item['name'],
                    ':price'        => $item['price'],
                    ':qty'          => $item['qty']
                ]);
            }
        }

        // 5. 確認全部寫入無誤，提交交易
        $pdo->commit();

        echo json_encode([
            "status" => "success",
            "message" => "訂單與購買商品明細已成功同步寫入資料庫！",
            "order_id" => $real_order_id
        ]);

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "後端未接收到完整的收件資訊或總額"
        ]);
    }

} catch (Exception $e) {
    // 💥 如果中途有任何一步出錯，則撤銷剛才所有執行的 SQL 動作，維持資料庫乾淨
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        "status" => "error",
        "message" => "資料庫操作失敗: " . $e->getMessage()
    ]);
}
?>