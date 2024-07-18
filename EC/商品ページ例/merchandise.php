<?php
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

// エラーハンドリングのための設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

// fingerprintの取得または生成
$fingerprint = isset($_GET['fingerprint']) ? $_GET['fingerprint'] : null;
if (!$fingerprint) {
    echo "Fingerprint not found";
    exit;
}

// fingerprintに基づいてユーザーIDを検索
$user_id = null;
$stmt = $conn->prepare("SELECT user_id FROM fingerprints WHERE fingerprint = ?");
$stmt->bind_param("s", $fingerprint);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
} else {
    $stmt = $conn->prepare("INSERT INTO fingerprints (fingerprint) VALUES (?)");
    $stmt->bind_param("s", $fingerprint);
    $stmt->execute();
    $user_id = $stmt->insert_id;
}

$product_id = isset($_GET['id']) ? $_GET['id'] : '0';
$product_id = $conn->real_escape_string($product_id);

$sql = "SELECT * FROM products WHERE product_id = '$product_id'";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

$sql_reviews = "SELECT username, comment, created_at FROM reviews WHERE product_id = '$product_id' ORDER BY created_at DESC";
$result_reviews = $conn->query($sql_reviews);
$reviews = $result_reviews->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品詳細</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="../styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .thumbnail-container {
            float: left;
            width: 10%;
        }
        .main-image-container {
            float: left;
            width: 50%;
            overflow: hidden;
        }
        .main-image-container img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }
        .thumbnail-container img {
            cursor: pointer;
            width: 100%;
            margin-bottom: 10px;
            border: 2px solid transparent;
        }
        .thumbnail-container img.selected {
            border: 2px solid red;
        }
        .Product-Description {
            float: left;
            width: 40%;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="nav-wrapper">
                <a href="../main.php" class="brand-logo black-text nav-font">Le Lélian Luxe</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <li><a href="../cart/cartview.php" class="waves-effect waves-light btn">商品カート</a></li>
                    <li><a href="../userinfo/userinfo.php" class="waves-effect waves-light btn">ユーザページ</a></li>
                    <li><a href="../login/login.html" class="waves-effect waves-light btn">ログイン</a></li>
                    <li><a href="../login/register.html" class="waves-effect waves-light btn">新規登録</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <div class="container">
        <h2>商品詳細</h2>
        <hr>
        <div id="product-container">
            <?php if ($product): ?>
                <div class="thumbnail-container">
                    <img src="<?= $product['image1'] ?>" alt="商品画像1" class="selected">
                    <img src="<?= $product['image2'] ?>" alt="商品画像2">
                    <img src="<?= $product['image3'] ?>" alt="商品画像3">
                </div>
                <div class="main-image-container">
                    <img id="main-image" src="<?= $product['image1'] ?>" alt="商品画像">
                </div>
                <div class="Product-Description">
                    <h3><?= $product['name'] ?></h3>
                    <hr>
                    <h4>価格: <?= $product['price'] ?>円</h4>
                    <hr>
                    <p>商品説明：<?= $product['description'] ?></p>
                    <hr>
                    <p>在庫数：<?= $product['stock'] ?></p>
                    <hr>
                    <form action="../cart/cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                        <label for="quantity">個数：</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button class="btn waves-effect waves-light" type="submit" name="action">カートに追加
                            <i class="material-icons right"></i>
                        </button>
                    </form>
                </div>
                <div class="cart">
                    <a href="../cart/cartview.php" class="waves-effect waves-light btn">カートを見る</a>
                </div>
            <?php else: ?>
                <p>商品が見つかりませんでした。</p>
            <?php endif; ?>
        </div>
        <div style="clear: both;"></div>
        <div class="review-container">
            <hr>
            <h3>レビュー</h3>
            <div class="search">
                <form id="review-form" action="submit_review.php" method="post">
                    <input type="hidden" id="product_id" name="product_id" value="<?= $product_id ?>">
                    <input type="hidden" id="fingerprint" name="fingerprint" value="<?= htmlspecialchars($fingerprint, ENT_QUOTES, 'UTF-8') ?>">
                    <input id="name" type="text" name="name" placeholder="名前を入力してください" required>
                    <input id="review" type="text" name="review" placeholder="レビューを入力してください" required>
                    <button class="btn waves-effect waves-light" type="submit" name="action">レビューを投稿
                        <i class="material-icons right"></i>
                    </button>
                </form>
            </div>
            <hr>
            <div class="review">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div>
                            <strong><?= $review['username'] ?></strong>: <?= $review['comment']?> (<?= date('Y-m-d H:i:s', strtotime($review['created_at'])) ?>)
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>レビューがありません</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const thumbnails = document.querySelectorAll('.thumbnail-container img');
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    document.getElementById('main-image').src = this.src;
                    thumbnails.forEach(img => img.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });

            // レビューの表示
            const reviews = <?php echo json_encode($reviews); ?>;
            console.log(reviews); // コンソールにレビューを表示
            const reviewContainer = document.querySelector('.review');
            if (reviews.length > 0) {
                reviewContainer.innerHTML = reviews.map(review => `
                    <div>
                        <strong>${review.username}</strong>: ${review.comment} (${new Date(review.created_at).toLocaleString()})
                    </div>
                `).join('');
            } else {
                reviewContainer.innerHTML = '<p>レビューがありません</p>';
            }
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
