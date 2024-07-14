<?php
// データベース接続情報
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

// セッションの開始
session_start();

// データベース接続
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
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

// 商品IDを取得
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 商品情報を取得
$sql = "SELECT product_id, name, price, description, stock, image1, image2, image3 FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bindParam(1, $product_id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// タグを取得してユーザーのタグデータベースに保存
$getTagsSql = "SELECT tag FROM products_tags WHERE product_id = ?";
try {
    $stmt = $conn->prepare($getTagsSql);
    $stmt->execute([$product_id]);
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tags as $tag) {
        // タグが既に存在するかチェック
        $checkTagSql = "SELECT COUNT(*) FROM tags WHERE user_id = ? AND tag = ?";
        $checkStmt = $conn->prepare($checkTagSql);
        $checkStmt->execute([$user_id, $tag['tag']]);
        $tagCount = $checkStmt->fetchColumn();

        if ($tagCount == 0) {
            // タグが存在しない場合のみ挿入
            $insertTagSql = "INSERT INTO tags (user_id, tag) VALUES (?, ?)";
            $stmt = $conn->prepare($insertTagSql);
            $stmt->execute([$user_id, $tag['tag']]);
        }
    }
} catch (PDOException $e) {
    die("タグ取得・保存エラー: " . $e->getMessage());
}

// JSON形式で商品情報を返す
echo json_encode($product);
?>
