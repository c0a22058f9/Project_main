<?php
// エラーを表示しない
ini_set('display_errors', "Off");
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
    // 商品がカートに追加された後に、購入完了ページを表示し、数秒後に元のページに戻る
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="1;URL=<?php echo $_SERVER['HTTP_REFERER']; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ご購入ありがとうございます</title>
        <!-- Materialize CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .container {
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="blue-text text-darken-1">商品をカートに追加しました。</h1>
            <p class="flow-text">数秒後に自動で元のページにリダイレクトします。</p>
            <div class="preloader-wrapper active">
                <div class="spinner-layer spinner-blue-only">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Materialize JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    </body>
    </html>
    <?php
    exit(); // リダイレクト後にスクリプトの実行を終了する
} catch (PDOException $e) {
    die("実行エラー: " . $e->getMessage());
}
?>
