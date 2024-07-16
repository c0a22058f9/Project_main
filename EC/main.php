<?php
// エラーを表示
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

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
    <style>
        body {
            font-family: "SimSun";
            background-image: url("./image/background.jpg");
            background-color:rgba(255,255,255,0.8);
            background-blend-mode:lighten;
        }
        .carousel .carousel-item {
            height: 300px;
        }
        .carousel-item img {
            height: 100%;
            object-fit: cover;
        }
        .nav-wrapper {
            background-image: url(./image/nav-background.jpg);
        }
        .nav-content {
            background-color: #919191;
            max-width: 1280px;
            margin: 0 auto;
            width: 70%;
            text-align: center;
        }
        .padding {
            padding-top: 30px;
        }
        .nav-font {
            font-family: "SimSun";
            margin-left: 100px;
            font-weight:bold
        }
        .card .card-image img {
            height: 200px;
            object-fit: cover;
        }
        footer {
            background-color: #232f3e;
        }
        footer h5, footer p {
            color: white;
        }
        .disabled {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav-wrapper">
                <a class="brand-logo black-text nav-font">ショッピングサイト</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <?php if (!empty($user_id)) : ?>
                        <li><a href="./cart/cartview.php" class="waves-effect waves-light btn">商品カート</a></li>
                        <li><a href="./userinfo/userinfo.php" class="waves-effect waves-light btn">ユーザページ</a></li>
                    <?php else : ?>
                        <li><a href="" class="waves-effect waves-light btn disabled">商品カート</a></li>
                        <li><a href="" class="waves-effect waves-light btn disabled">ユーザページ</a></li>
                    <?php endif; ?>
                    <li><a href="./login/login.html" class="waves-effect waves-light btn">ログイン</a></li>
                    <li><a href="./login/register.html" class="waves-effect waves-light btn">新規登録</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div class="container">
            <h2>Featured Products</h2>
            <!-- Search Form -->
            <form action="search.php" method="GET">
                <div class="input-field">
                    <input id="search" type="text" name="query" required>
                    <label for="search">商品を検索</label>
                </div>
                <button type="submit" class="waves-effect waves-light btn">検索</button>
            </form>
            <!-- Recommendation Carousel -->
            <div class="carousel carousel-slider center">
                <div class="carousel-item" href="./image1.jpg">
                    <img src="./image1.jpg" alt="商品1">
                    <h2 class="white-text">商品1</h2>
                    <p class="white-text">おすすめ商品1</p>
                </div>
                <div class="carousel-item" href="./image2.jpg">
                    <img src="./image2.jpg" alt="商品2">
                    <h2 class="white-text">商品2</h2>
                    <p class="white-text">おすすめ商品2</p>
                </div>
                <div class="carousel-item" href="./image3.jpg">
                    <img src="./image3.jpg" alt="商品3">
                    <h2 class="white-text">商品3</h2>
                    <p class="white-text">おすすめ商品3</p>
                </div>
                <div class="carousel-item" href="./image4.jpg">
                    <img src="./image4.jpg" alt="商品4">
                    <h2 class="white-text">商品4</h2>
                    <p class="white-text">おすすめ商品4</p>
                </div>
            </div>
            <!-- Products Display -->
            <div class="row">
                <?php
                require './login/database_config.php';
                $uploadDir = './商品ページ例';
                $merchandise = './商品ページ例/merchandise.html';
                $pdo = new PDO(DSN, DB_USER, DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec("USE ecdatabase");
                $stmt = $pdo->query("SELECT * FROM products");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="col s4">';
                    echo '<div class="card">';
                    echo '<div class="card-image">';
                    echo '<img src="' . $uploadDir . '/' . $row['image1'] . '" alt="' . $row['name'] . '">';
                    echo '</div>';
                    echo '<span class="card-title">' . $row['name'] . '</span>';
                    echo '<div class="card-content">';
                    echo $row['description'];
                    echo '</div>';
                    echo '<div class="card-action">';
                    echo '<a href="' . $merchandise . '?id=' . $row['product_id'] . '">詳細を見る</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>

            <!-- Recommendations Display -->
            <?php if ($hasRecommendations) : ?>
            <h2>おすすめ商品</h2>
            <div class="row">
                <?php
                foreach ($recommendations as $rec) {
                    echo '<div class="col s4">';
                    echo '<div class="card">';
                    echo '<div class="card-image">';
                    echo '<img src="' . $uploadDir . '/' . $rec['image1'] . '" alt="' . $rec['name'] . '">';
                    echo '</div>';
                    echo '<span class="card-title">' . $rec['name'] . '</span>';
                    echo '<div class="card-content">';
                    echo $rec['description'];
                    echo '</div>';
                    echo '<div class="card-action">';
                    echo '<a href="' . $merchandise . '?id=' . $rec['product_id'] . '">詳細を見る</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <footer class="page-footer">
        <div class="container">
            <div class="row">
                <div class="col l6 s12">
                    <h5 class="white-text">Your Shopping Site</h5>
                    <p class="grey-text text-lighten-4">ここにサイトの詳細情報を記載します。</p>
                </div>
                <div class="col l4 offset-l2 s12">
                    <h5 class="white-text">リンク</h5>
                    <ul>
                        <li><a class="grey-text text-lighten-3" href="main.html">ショッピング</a></li>
                        <li><a class="grey-text text-lighten-3" href="./login/login.html">ログイン</a></li>
                        <li><a class="grey-text text-lighten-3" href="./login/register.html">新規登録</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <div class="container">
                &copy; 2022 Your Shopping Site. All rights reserved.
            </div>
        </div>
    </footer>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.carousel');
        var instances = M.Carousel.init(elems, {
            fullWidth: true,
            indicators: true
        });
        setInterval(function() {
            var instance = M.Carousel.getInstance(elems[0]);
            instance.next();
        }, 5000);
    });
    </script>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
