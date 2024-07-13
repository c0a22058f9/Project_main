<?php
ini_set('display_errors', "On");
error_reporting(E_ALL);
session_start();
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

//画像ディレクトリ
$uploadDir = '../商品ページ例/';

// データベース接続情報の定義
define('DSN', 'mysql:host=' . $servername . ';dbname=' . $dbname . ';charset=utf8');
define('DB_USER', $username);
define('DB_PASS', $password);

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

// user_idを元に商品と個数を取得するクエリ
$getCartItemsSql = "SELECT c.product_id, p.name, c.quantity
                   FROM cart c
                   INNER JOIN products p ON c.product_id = p.product_id
                   WHERE c.user_id = :user_id";

try {
    $stmt = $conn->prepare($getCartItemsSql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("カートアイテム取得エラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <!-- Materialize CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カートビュー</title>
    <style>
        html, body {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1 0 auto;
        }
        .product {
            display: flex;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            padding: 10px;
        }
        .product img {
            max-width: 100px;
        }
        .product-info {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav-wrapper">
                <a href="#" class="brand-logo">ショッピングサイトへようこそ</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <li><a href="main.html" class="waves-effect waves-light btn">ショッピングへ</a></li>
                    <li><a href="./login/login.html" class="waves-effect waves-light btn">ログイン</a></li>
                    <li><a href="./login/register.html" class="waves-effect waves-light btn">新規登録</a></li>
                </ul>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="container">
            <h2>カート</h2>
            <?php
            // 合計金額を計算
            // 商品詳細を取得するクエリ
            $getProductSql = "SELECT * FROM products WHERE product_id = :product_id";
            
            // 合計金額を計算
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $stmt = $conn->prepare($getProductSql);
                $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalAmount += $product['price'] * $item['quantity'];
            }
            ?>
            <div class="row">
                <div class="col s12">
                    <h3>合計金額: <?= htmlspecialchars($totalAmount) ?>円</h3>
                </div>
            </div>
            <div class="row">
                <div class="col s12">
                    <?php
                    // 商品詳細を取得するクエリ
                    $getProductSql = "SELECT * FROM products WHERE product_id = :product_id";

                    if (empty($cartItems)): ?>
                        <p>カートに商品がありません。</p>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                            $stmt = $conn->prepare($getProductSql);
                            $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
                            $stmt->execute();
                            $product = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <div class="product">
                                <img src="<?=$uploadDir . htmlspecialchars($product['image1']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="product-info">
                                    <h2><?= htmlspecialchars($product['name']) ?></h2>
                                    <p>価格: <?= htmlspecialchars($product['price']) ?>円</p>
                                    <p>数量: <?= htmlspecialchars($item['quantity']) ?></p>
                                    <p>説明: <?= htmlspecialchars($product['description']) ?></p>
                                    <form action="removeFromCart.php" method="post" onsubmit="return confirm('取り消しますか？');">
                                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']) ?>">
                                        <button type="submit" class="btn">取り消し</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="page-footer">
        <div class="container">
            <div class="row">
                <div class="col l6 s12">
                    <h5 class="white-text">Your Shopping Site</h5>
                    <p class="grey-text text-lighten-4">ここにサイトの詳細情報を記載します。</p>
                </div>
            </div>
        </div>
        <div class="container">
            &copy; 2022 Your Shopping Site. All rights reserved.
        </div>
    </footer>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
