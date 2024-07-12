<!DOCTYPE html>
<html lang="ja">
<head>
	<!-- Materialize CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>ショッピングサイトへようこそ</title>
</head>
<body>
	<header>
		<nav>
			<div class="nav-wrapper">
				<a href="#" class="brand-logo">ショッピングサイトへようこそ</a>
				<ul id="nav-mobile" class="right hide-on-med-and-down">
					<li><a href="main.php" class="waves-effect waves-light btn">ショッピングへ</a></li>
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
				<div class="carousel-item red white-text" href="#one!">
					<h2>1</h2>
					<p class="white-text">カーセル1</p>
				</div>
				<div class="carousel-item amber white-text" href="#two!">
					<h2>2</h2>
					<p class="white-text">カーセル2</p>
				</div>
				<div class="carousel-item green white-text" href="#three!">
					<h2>3</h2>
					<p class="white-text">カーセル3</p>
				</div>
				<div class="carousel-item blue white-text" href="#four!">
					<h2>4</h2>
					<p class="white-text">カーセル4</p>
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