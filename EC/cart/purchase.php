<?php
session_start();
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

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
$getCartItemsSql = "SELECT c.product_id, p.name, c.quantity, p.price, p.stock
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

// 合計金額を計算
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

// 購入ボタンが押された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['h-captcha-response'])) {
        echo '<script>alert("hCaptchaを完了してください。");</script>';
    } else {
        // hCaptchaの検証
        $hCaptchaSecret = 'ES_89c6c49805844ec4a6218042defdf3b5'; // ここにhCaptchaのシークレットキーを入力
        $hCaptchaResponse = $_POST['h-captcha-response'];

        $postData = http_build_query([
            'secret' => $hCaptchaSecret,
            'response' => $hCaptchaResponse
        ]);
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ];
        $context = stream_context_create($opts);
        $response = file_get_contents('https://hcaptcha.com/siteverify', false, $context);
        $result = json_decode($response);

        if (!$result->success) {
            //hCaptchaの検証に失敗しました。とメッセージボックスで表示
            echo '<script>alert("hCaptchaの検証に失敗しました。");</script>';
            //カートのPageへリダイレクト
            header('Location: cartview.php');
        }

        // 在庫を減らす
        $updateStockSql = "UPDATE products SET stock = stock - :quantity WHERE product_id = :product_id";
        $removeFromCartSql = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";

        $conn->beginTransaction();
        try {
            foreach ($cartItems as $item) {
                // 在庫を減らす
                $stmt = $conn->prepare($updateStockSql);
                $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
                $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
                $stmt->execute();

                // カートから削除
                $stmt = $conn->prepare($removeFromCartSql);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
                $stmt->execute();
            }
            $conn->commit();

            // メール送信
            $to = 'example@example.com'; // ここに送信先のメールアドレスを指定
            $subject = '購入内容の確認';
            $message = "以下の商品を購入しました。\n\n";
            foreach ($cartItems as $item) {
                $message .= $item['name'] . " - 数量: " . $item['quantity'] . " - 価格: " . $item['price'] . "円\n";
            }
            $message .= "\n合計金額: " . $totalAmount . "円";
            $headers = 'From: webmaster@example.com' . "\r\n" .
                       'Reply-To: webmaster@example.com' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();

            if (mail($to, $subject, $message, $headers)) {
                header('Location: ./thanks_buy.html');
            } else {
                echo 'メールの送信に失敗しました。';
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            die("購入処理エラー: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>購入ページ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="https://hcaptcha.com/1/api.js" async defer></script>
</head>
<body>
    <div class="container">
        <h2>購入内容の確認</h2>
        <ul class="collection">
            <?php foreach ($cartItems as $item): ?>
                <li class="collection-item">
                    <?= htmlspecialchars($item['name']) ?> - 数量: <?= htmlspecialchars($item['quantity']) ?> - 価格: <?= htmlspecialchars($item['price']) ?>円
                </li>
            <?php endforeach; ?>
        </ul>
        <h3>合計金額: <?= htmlspecialchars($totalAmount) ?>円</h3>
        <form method="post">
            <div class="h-captcha" data-sitekey="7d04e186-a1da-4570-8212-26b6e7fe32a4"></div> <!-- サイトキー -->
            <button type="submit" class="btn">購入する</button>
        </form>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
