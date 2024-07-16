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
} catch (PDOException $e) {
    error_log("接続失敗: " . $e->getMessage());
    die("接続失敗: " . $e->getMessage());
}

$product_id = $_GET['product_id'];

error_log("Received product_id: $product_id"); // デバッグ用ログ

// SQLインジェクション脆弱性を意図的に持たせるために、クエリを直接実行
$getReviewsSql = "SELECT r.*, CONCAT(u.last_name, ' ', u.first_name) AS username FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = '$product_id'";
try {
    $stmt = $conn->query($getReviewsSql); // クエリを直接実行
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($reviews === false) {
        throw new Exception("レビューの取得に失敗しました。");
    }
    error_log("Fetched reviews: " . print_r($reviews, true)); // デバッグ用ログ
    echo json_encode($reviews);
} catch (PDOException $e) {
    error_log('レビュー取得エラー: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'レビュー取得エラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
