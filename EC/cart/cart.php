<?php
//エラーを表示
ini_set('display_errors', "On");
error_reporting(E_ALL);
session_start();
require '../Common_components/database_config.php'; // データベース接続情報を含むファイル

// セッションIDのチェック
if (!isset($_SESSION['user_id']) || empty(session_id())) {
	die('セッションが存在しないか、無効です。');
}

// cartテーブルが存在しない場合に自動で作成
$createCartTable = "CREATE TABLE IF NOT EXISTS cart (
	cart_id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	product_id INT NOT NULL,
	session_id VARCHAR(255) NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (product_id) REFERENCES products(product_id)
)";
$conn->query($createCartTable);

// POSTから商品IDを取得
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$user_id = $_SESSION['user_id'];
$session_id = session_id();

// 商品IDをcartテーブルに保存
$insertCartSql = "INSERT INTO cart (user_id, product_id, session_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insertCartSql);
$stmt->bind_param("iis", $user_id, $product_id, $session_id);
$stmt->execute();

$conn->close();

echo "商品がカートに追加されました。";
?>
