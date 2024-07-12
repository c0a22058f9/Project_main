<?php
// エラーを表示
ini_set('display_errors', "On");
error_reporting(E_ALL);
session_start();
require '../login/database_config.php'; // データベース接続情報を含むファイル

// データベース接続
$pdo = new PDO(DSN, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// データベースが存在しない場合に作成し、そのデータベースを使用
$dbname = 'ecdatabase';
$pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
$pdo->exec("USE $dbname");

// 商品テーブル作成
$createProductsTable = "CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    stock INT NOT NULL,
    image1 VARCHAR(255),
    image2 VARCHAR(255),
    image3 VARCHAR(255)
)";
$pdo->exec($createProductsTable);

// タグテーブル作成
$createTagsTable = "CREATE TABLE IF NOT EXISTS products_tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    tag VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
)";
$pdo->exec($createTagsTable);

// POSTデータを受け取る
$name = $_POST['name'] ?? '';
$price = $_POST['price'] ?? '';
$description = $_POST['description'] ?? '';
$stock = $_POST['stock'] ?? '';
$tags = $_POST['tags'] ?? '';
$image1 = $_FILES['image1']['name'] ?? '';
$image2 = $_FILES['image2']['name'] ?? '';
$image3 = $_FILES['image3']['name'] ?? '';

// データ検証（簡易的な例）
if (empty($name) || empty($price) || empty($description) || empty($stock)) {
    // 必須フィールドが空の場合はエラー
    die('必須フィールドが入力されていません。');
}

try {
    // 画像をアップロード
    $uploadDir = '/uploads/';
    $uploadedImage1 = $uploadDir . basename($_FILES['image1']['name']);
    $uploadedImage2 = $uploadDir . basename($_FILES['image2']['name']);
    $uploadedImage3 = $uploadDir . basename($_FILES['image3']['name']);
    move_uploaded_file($_FILES['image1']['tmp_name'], './' . $uploadedImage1);
    move_uploaded_file($_FILES['image2']['tmp_name'], './' . $uploadedImage2);
    move_uploaded_file($_FILES['image3']['tmp_name'], './' . $uploadedImage3);

    // SQL文を準備
    $sql = "INSERT INTO products (name, price, description, stock, image1, image2, image3) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    // SQL文を実行
    $stmt->execute([$name, $price, $description, $stock, $uploadedImage1, $uploadedImage2, $uploadedImage3]);

    // 商品登録後の処理
    $productId = $pdo->lastInsertId(); // 最後に挿入された行のIDを取得

    // タグを登録
    if (!empty($tags)) {
        $tagsArray = explode(',', $tags);
        foreach ($tagsArray as $tag) {
            $tag = trim($tag);
            $sqlTag = "INSERT INTO products_tags (product_id, tag) VALUES (?, ?)";
            $stmtTag = $pdo->prepare($sqlTag);
            $stmtTag->execute([$productId, $tag]);
        }
    }

    // 完了メッセージを変数に格納
    $output = '商品が正常に登録されました。';
} catch (PDOException $e) {
    die('データベースエラー: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!--$outputに格納されたメッセージを表示-->
    <title>商品登録</title>
</head>
<body>
    <nav>
        <div class="nav-wrapper">
            <a href="#" class="brand-logo center">商品登録完了</a>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col s12">
                <p class="flow-text"><?php echo $output; ?></p>
                <!--メインページへのリンク-->
                <button onclick="location.href='../index.php'">メインページへ</button>
            </div>
        </div>
    </div>
</body>
</html>
