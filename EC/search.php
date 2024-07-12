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
$uploadDir = './商品ページ例';
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
</head>
<body>
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
</body>
</html>