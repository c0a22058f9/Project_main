<?php
// データベース接続情報
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

// デバッグ用ログファイル
ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php-error.log");

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // reviewsテーブルが存在しない場合に作成
    $createTableSql = "CREATE TABLE IF NOT EXISTS `reviews` (
        `review_id` int NOT NULL AUTO_INCREMENT,
        `product_id` int NOT NULL,
        `user_id` int NOT NULL,
        `rating` int DEFAULT NULL,
        `comment` text,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`review_id`),
        KEY `product_id` (`product_id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
        CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
        CONSTRAINT `reviews_chk_1` CHECK (((`rating` >= 1) and (`rating` <= 5)))
    ) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
    $conn->exec($createTableSql);
    
} catch (PDOException $e) {
    error_log("接続失敗: " . $e->getMessage());
    die("接続失敗: " . $e->getMessage());
}

session_start();

$session_id = session_id();
if (empty($session_id)) {
    error_log('セッションが存在しないか、無効です。');
    echo json_encode(['success' => false, 'message' => 'セッションが存在しないか、無効です。']);
    exit();
}

// SQLインジェクション脆弱性を意図的に持たせるために、クエリを直接実行
$getUserIdSql = "SELECT user_id FROM sessions WHERE session_id = '$session_id'";
try {
    $stmt = $conn->query($getUserIdSql); // クエリを直接実行
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        error_log('有効なユーザーが見つかりません。');
        echo json_encode(['success' => false, 'message' => '有効なユーザーが見つかりません。']);
        exit();
    }
    $user_id = $user['user_id'];
} catch (PDOException $e) {
    error_log('ユーザーID取得エラー: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'ユーザーID取得エラー: ' . $e->getMessage()]);
    exit();
}

// ユーザー入力を直接SQLクエリに使用
$product_id = $_POST['product_id'];
$rating = $_POST['rating'];
$comment = $_POST['comment'];

error_log("Received product_id: $product_id");

// SQLインジェクション脆弱性を意図的に持たせるために、クエリを直接実行
$checkProductSql = "SELECT * FROM products WHERE product_id = '$product_id'";
try {
    $stmt = $conn->query($checkProductSql); // クエリを直接実行
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        error_log('無効な商品IDです。');
        echo json_encode(['success' => false, 'message' => '無効な商品IDです。']);
        exit();
    }
} catch (PDOException $e) {
    error_log('商品ID確認エラー: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '商品ID確認エラー: ' . $e->getMessage()]);
    exit();
}

// SQLインジェクション脆弱性を意図的に持たせるために、クエリを直接実行
$insertReviewSql = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES ('$product_id', '$user_id', '$rating', \"$comment\")";
try {
    $conn->beginTransaction();
    $conn->exec($insertReviewSql); // クエリを直接実行
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $conn->rollBack();
    error_log('レビュー挿入エラー: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'レビュー挿入エラー: ' . $e->getMessage()]);
}
