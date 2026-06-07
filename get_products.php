<?php
// get_products.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 允許跨域請求

// 修正：改成 PHP 正確的引入語法 require_once
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 將資料庫的 description 欄位對應回你前端的 desc 命名
    $formattedProducts = array_map(function($p) {
        return [
            'id' => (int)$p['id'],
            'name' => $p['name'],
            'price' => (int)$p['price'],
            'category' => $p['category'],
            'img' => $p['img'],
            'desc' => $p['description'] // 完美對接前端的 p.desc
        ];
    }, $products);

    echo json_encode(["status" => "success", "data" => $formattedProducts], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "無法獲取商品: " . $e->getMessage()]);
}
?>