<?php
// エラーを表示
ini_set('display_errors', "On");
error_reporting(E_ALL);

// データベース接続情報を含むファイルを読み込む
require './login/database_config.php';

// データベース接続
$pdo = new PDO(DSN, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//画像のアップロード先ディレクトリ
$uploadDir = './商品ページ例/';
//商品ページ
$merchandise = './商品ページ例/merchandise.html';

// 検索クエリを取得
$query = $_GET['query'] ?? '';

// 検索クエリが空でない場合、データベースから商品情報を検索
if (!empty($query)) {
	$sql = "SELECT * FROM products WHERE name LIKE :query OR description LIKE :query";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':query', '%' . $query . '%');
	$stmt->execute();
	$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
	$products = [];
}

// HTMLで検索結果を表示
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>検索結果</title>
	<!-- Materialize CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
	<style>
        body {
            font-family: "SimSun";
            background-image: url("/Project_main/EC/image/background.jpg");
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
            background-image: url(/Project_main/EC/image/nav-background.jpg);
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
            font-weight:bold;
        }
        .card .card-image img {
            height: 200px;
            object-fit: cover;
        }

        footer {
            background-color: #828e9c !important;
        }

        footer h5, footer p {
            color: black;
        }
        .disabled {
            pointer-events: none;
            opacity: 0.5;
        }
		/* 共通のボタンスタイル */
    	.waves-effect.waves-light.btn {
    	  background-color: #6a6e78; /* 変更したい色にここを変更 */
    	  color: #fff;
    	}

        /* ホバー時のスタイル */
    	.waves-effect.waves-light.btn:hover {
    	  background-color: #4a4e58; /* ホバー時の色を変更 */
    	  color: #fff;
    	}
    </style>

</head>
<body>
<header>
        <nav>
            <div class="nav-wrapper">
                <a href="/Project_main/EC/main.php" class="brand-logo black-text nav-font">Le Lien Luxe</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <?php if (!empty($user_id)) : ?>
                        <li><a href="/Project_main/EC/cart/cartview.php" class="waves-effect waves-light btn">商品カート</a></li>
                        <li><a href="/Project_main/EC/userinfo/userinfo.php" class="waves-effect waves-light btn">ユーザページ</a></li>
                    <?php else : ?>
                        <li><a href="" class="waves-effect waves-light btn disabled">商品カート</a></li>
                        <li><a href="" class="waves-effect waves-light btn disabled">ユーザページ</a></li>
                    <?php endif; ?>
                    <li><a href="/Project_main/EC/login/login.html" class="waves-effect waves-light btn">ログイン</a></li>
                    <li><a href="/Project_main/EC/login/register.html" class="waves-effect waves-light btn">新規登録</a></li>
                </ul>
            </div>
        </nav>
    </header>
	<div class="container">
		<h2>検索結果</h2>
		<?php if (empty($products)): ?>
			<p>商品が見つかりませんでした。</p>
		<?php else: ?>
			<div class="row">
				<?php foreach ($products as $product): ?>
					<div class="col s12 m4">
						<div class="card">
							<div class="card-image">
								<img src="<?php echo htmlspecialchars($uploadDir . $product['image1']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
							</div>
							<span class="card-title"><?php echo htmlspecialchars($product['name']); ?></span>
							<div class="card-content">
								<p><?php echo htmlspecialchars($product['description']); ?></p>
							</div>
							<div class="card-action">
								<a href="<?php echo htmlspecialchars($merchandise . '?id=' . $product['product_id']); ?>">詳細を見る</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<!-- Materialize JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
	<footer class="page-footer">
        <div class="container">
            <div class="row">
                <div class="col l6 s12">
                    <h5 class="black-text">Le Lien Luxe</h5>
                    <p class="black-text text-lighten-4">洗練されたあなたのための特別な一品を</p>
                </div>
                <div class="col l4 offset-l2 s12">
                    <ul>
                        <li><a class="black-text text-lighten-3" href="/Project_main/EC/cart/cartview.php">商品カート</a></li>
                        <li><a class="black-text text-lighten-3" href="/Project_main/EC/login/login.html">ログイン</a></li>
                        <li><a class="black-text text-lighten-3" href="/Project_main/EC/login/register.html">新規登録</a></li>
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
</body>
</html>