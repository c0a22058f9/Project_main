<?php
// エラーを表示
ini_set('display_errors', "On");
error_reporting(E_ALL);
session_start();
define('DSN', 'mysql:host=localhost;dbname=ecdatabase;charset=utf8');
define('DB_USER', 'user1');
define('DB_PASS', 'passwordA1!');

// データベース接続
try {
    $conn = new PDO(DSN, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("接続失敗: " . $e->getMessage());
}

// セッションIDのチェック
$session_id = session_id();
if (empty($session_id)) {
    die('セッションが存在しないか、無効です。');
}

// セッションIDからユーザーIDを取得
$getUserIdSql = "SELECT user_id FROM sessions WHERE session_id = ?";
try {
    $stmt = $conn->prepare($getUserIdSql);
    $stmt->execute([$session_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die('有効なユーザーが見つかりません。');
    }
    $user_id = $user['user_id'];
} catch (PDOException $e) {
    die("ユーザーID取得エラー: " . $e->getMessage());
}

// デバッグ用: セッションIDとユーザーIDの確認
echo "セッションID: " . $session_id . "<br>";
echo "ユーザーID: " . $user_id . "<br>";

// cartテーブルが存在しない場合に自動で作成（quantityカラムを追加）
$createCartTable = "
    CREATE TABLE IF NOT EXISTS cart (
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        PRIMARY KEY (user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    )
";
try {
    $conn->exec($createCartTable);
} catch (PDOException $e) {
    die("テーブル作成エラー: " . $e->getMessage());
}

// POSTから商品IDと個数を取得
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1; // デフォルト値を1に設定

// 商品IDと個数をcartテーブルに保存
$insertCartSql = "
    INSERT INTO cart (user_id, product_id, quantity)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
";
try {
    $stmt = $conn->prepare($insertCartSql);
    $stmt->execute([$user_id, $product_id, $quantity]);
    echo "商品がカートに追加されました。";
} catch (PDOException $e) {
    die("実行エラー: " . $e->getMessage());
}
?>
