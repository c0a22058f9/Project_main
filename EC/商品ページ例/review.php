<?php
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

// エラーハンドリングのための設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// フォームデータの取得
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['name']) && isset($_POST['review']) && isset($_POST['product_id'])) {
        $name = $_POST['name'];
        $review = $_POST['review'];
        $product_id = (int)$_POST['product_id']; // 整数型にキャスト

        // SQLクエリの実行
        $sql = "INSERT INTO reviews (product_id, username, comment) VALUES ('$product_id', '$name', '$review')";

        if ($conn->query($sql) === TRUE) {
            // リダイレクト先のURLを設定
            $redirectUrl = "merchandise.html?id=$product_id";
            header("Location: $redirectUrl");
            exit();
        } else {
            echo "エラー: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "すべてのフィールドを入力してください";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['product_id'])) {
        $product_id = (int)$_GET['product_id'];
        $sql = "SELECT username, comment, created_at FROM reviews WHERE product_id = '$product_id' ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $reviews = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $reviews[] = $row;
            }
            echo json_encode($reviews);
        } else {
            echo json_encode([]);
        }
        exit();
    }
} else {
    echo "無効なリクエスト方法です";
}

$conn->close();
?>
