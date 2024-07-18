<?php
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

// データベース接続
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// reviewsテーブルが存在しない場合に作成
$createTableSQL = "
    CREATE TABLE IF NOT EXISTS `reviews` (
        `review_id` INT NOT NULL AUTO_INCREMENT,
        `product_id` INT NOT NULL,
        `username` VARCHAR(255) NOT NULL,
        `comment` TEXT,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`review_id`),
        KEY `product_id` (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
";

if ($conn->query($createTableSQL) === FALSE) {
    die("テーブル作成エラー: " . $conn->error);
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// 初期化
$reviews = [];

// レビューを取得
$sql_reviews = "SELECT username, comment, created_at FROM reviews WHERE product_id = ? ORDER BY created_at DESC";
$stmt_reviews = $conn->prepare($sql_reviews);
if ($stmt_reviews) {
    $stmt_reviews->bind_param("i", $product_id);
    $stmt_reviews->execute();
    $reviews = $stmt_reviews->get_result()->fetch_all(MYSQLI_ASSOC);
}

$response = [
    'product' => $product,
    'reviews' => $reviews
];

$conn->close();

echo json_encode($response);
?>
