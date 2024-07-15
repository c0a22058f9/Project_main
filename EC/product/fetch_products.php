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

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT product_id, name, price, description, stock, image1, image2, image3 FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bindParam(1, $product_id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    echo json_encode($product);
} else {
    error_log("商品が見つかりません。");
    echo json_encode(null);
}
?>
