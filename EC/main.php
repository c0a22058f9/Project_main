<?php
// エラーメッセージ表示設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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
$user_id = null; // 初期化
try {
    $stmt = $conn->prepare($getUserIdSql);
    $stmt->execute([$session_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $user_id = $user['user_id'];
    }
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

// おすすめ商品を取得
$getRecommendationsSql = "
    SELECT DISTINCT p.product_id, p.name, p.price, p.description, p.image1
    FROM products p
    INNER JOIN products_tags pt ON p.product_id = pt.product_id
    INNER JOIN tags t ON pt.tag = t.tag
    WHERE t.user_id = ?
    ORDER BY RAND()
    LIMIT 3
";
try {
    $stmt = $conn->prepare($getRecommendationsSql);
    $stmt->execute([$user_id]);
    $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasRecommendations = !empty($recommendations);
} catch (PDOException $e) {
    die("おすすめ商品取得エラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ショッピングサイトへようこそ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="nav-wrapper">
                <a href="main.html" class="brand-logo black-text nav-font">Le Lien Luxe</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <li><a href="./cart/cart.html" class="waves-effect waves-light btn">商品カート</a></li>
                    <li><a href="./login/login.html" class="waves-effect waves-light btn">ログイン</a></li>
                    <li><a href="./login/register.html" class="waves-effect waves-light btn">新規登録</a></li>
                </ul>
            </div>
            <div class="nav-content">
                <ul class="tabs tabs-transparent">
                    <li class="tab"><a href="#all">すべての商品</a></li>
                    <li class="tab"><a href="#new">新着商品</a></li>
                    <li class="tab"><a href="#popular">人気商品</a></li>
                    <li class="tab"><a href="#sale">セール商品</a></li>
                </ul>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="container padding">
            <!-- Search Form -->
            <form action="search.php" method="GET">
                <div class="input-field search">
                    <input id="search" type="text" name="query" required>
                    <label for="search">商品を検索</label>
                <button type="submit" class="waves-effect waves-light btn">検索</button>
                </div>
            </form>

            <h2 class="center-align">おすすめ商品</h2>
            <!-- Recommendation Carousel -->
            <div class="carousel carousel-slider center slide-image">
                <div class="carousel-item" href="./product/product.html?id=1">
                    <img src="./shoulderbag.png" alt="商品1">
                    <h2 class="white-text">商品1</h2>
                    <p class="white-text">おすすめ商品1</p>
                </div>
                <div class="carousel-item" href="./product/product.html?id=2">
                    <img src="./clutchbag.png" alt="商品2">
                    <h2 class="white-text">商品2</h2>
                    <p class="white-text">おすすめ商品2</p>
                </div>
                <div class="carousel-item" href="./product/product.html?id=3">
                    <img src="./briefcase.png" alt="商品3">
                    <h2 class="white-text">商品3</h2>
                    <p class="white-text">おすすめ商品3</p>
                </div>
                <div class="carousel-item" href="./product/product.html?id=4">
                    <img src="./shoulderbag.png" alt="商品4">
                    <h2 class="white-text">商品4</h2>
                    <p class="white-text">おすすめ商品4</p>
                </div>
            </div>

            <div class="row">
                <!-- Card 1 -->
                <div class="col s12 m6 l3">
                    <div class="card hoverable">
                        <div class="card-image">
                            <img src="./shoulderbag.png" alt="ショルダーバッグ">
                        </div>
                        <div class="card-content">
                            <span class="card-title">ショルダーバッグ</span>
                        </div>
                        <div class="card-action">
                            <a href="./product/product.html?id=1">ショルダーバッグの他の商品を見る</a>
                        </div>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="col s12 m6 l3">
                    <div class="card hoverable">
                        <div class="card-image">
                            <img src="./clutchbag.png" alt="クラッチバッグ">
                        </div>
                        <div class="card-content">
                            <span class="card-title">クラッチバッグ</span>
                        </div>
                        <div class="card-action">
                            <a href="./product/product.html?id=2">クラッチバッグの他の商品を見る</a>
                        </div>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col s12 m6 l3">
                    <div class="card hoverable">
                        <div class="card-image">
                            <img src="./briefcase.png" alt="ビジネスバッグ">
                        </div>
                        <div class="card-content">
                            <span class="card-title">ビジネスバッグ</span>
                        </div>
                        <div class="card-action">
                            <a href="./product/product.html?id=3">ビジネスバッグの他の商品を見る</a>
                        </div>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="col s12 m6 l3">
                    <div class="card hoverable">
                        <div class="card-image">
                            <img src="./image2.jpg" alt="商品4">
                        </div>
                        <div class="card-content">
                            <span class="card-title">商品4</span>
                            <p>この商品は高評価を得ています。</p>
                        </div>
                        <div class="card-action">
                            <a href="./product/product.html?id=4">詳細を見る</a>
                        </div>
                    </div>
                </div>
                <!-- 追加のカードはここに続けて追加できます -->
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <div class="row">
                <div class="col l6 s12">
                    <h5 class="white-text">Le Lien Luxe</h5>
                    <p class="grey-text text-lighten-4">洗練されたあなたのための特別な一品を</p>
                </div>
                <div class="col l4 offset-l2 s12">
                    <ul>
                        <li><a class="grey-text text-lighten-3" href="./cart/cart.html">商品カート</a></li>
                        <li><a class="grey-text text-lighten-3" href="./login/login.html">ログイン</a></li>
                        <li><a class="grey-text text-lighten-3" href="./login/register.html">新規登録</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <div class="container">
                &copy; 2024 Shopping Site. All rights reserved.
            </div>
        </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="scripts.js"></script>
</body>
</html>