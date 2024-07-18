<?php
ini_set('display_errors', "On");
error_reporting(E_ALL);
session_start();

$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

function OpenCon() {
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function CloseCon($conn) {
    $conn->close();
}

$conn = OpenCon();
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$fingerprint = isset($_GET['fingerprint']) ? $_GET['fingerprint'] : null;

if ($product_id > 0) {
    // Fetch product details from the database
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    if (!$stmt) {
        echo json_encode(['error' => 'データベースエラー']);
        exit;
    }
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $product = $result->fetch_assoc();
        if ($product) {
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

            echo json_encode($response);
        } else {
            echo json_encode(['error' => '商品が見つかりません']);
        }
    } else {
        echo json_encode(['error' => 'データベースクエリに失敗しました']);
    }
} else {
    echo json_encode(['error' => '無効な商品ID']);
}

$user_id = null;
if ($fingerprint) {
    // fingerprintに基づいてユーザーIDを検索
    $stmt = $conn->prepare("SELECT user_id FROM fingerprints WHERE fingerprint = ?");
    $stmt->bind_param("s", $fingerprint);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
    } else {
        // ユーザーIDが見つからない場合、新しいfingerprintを追加
        $stmt = $conn->prepare("INSERT INTO fingerprints (fingerprint) VALUES (?)");
        $stmt->bind_param("s", $fingerprint);
        $stmt->execute();
        $user_id = $stmt->insert_id;
    }
}

if ($user_id) {
    // Fetch tags associated with the product_id
    $stmt = $conn->prepare("SELECT tag FROM products_tags WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tag = $row['tag'];

        // Check if the tag already exists for the user
        $check_stmt = $conn->prepare("SELECT * FROM tags WHERE user_id = ? AND tag = ?");
        $check_stmt->bind_param("is", $user_id, $tag);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows == 0) {
            // If the tag doesn't exist, insert it
            $insert_stmt = $conn->prepare("INSERT INTO tags (user_id, tag) VALUES (?, ?)");
            $insert_stmt->bind_param("is", $user_id, $tag);
            $insert_stmt->execute();
        }
    }
}

CloseCon($conn);
?>
