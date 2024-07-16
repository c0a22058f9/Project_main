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

// user_idからemailを取得
$getUserEmailSql = "SELECT email FROM users WHERE user_id = ?";
try {
    $stmt = $conn->prepare($getUserEmailSql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die('有効なメールアドレスが見つかりません。');
    }
    $email = $user['email'];
} catch (PDOException $e) {
    die("メールアドレス取得エラー: " . $e->getMessage());
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
            // hCaptchaの検証に失敗しました。とメッセージボックスで表示
            echo '<script>alert("hCaptchaの検証に失敗しました。");</script>';
            // カートのPageへリダイレクト
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
            $to = $email; // ここに取得したメールアドレスを使用
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
    <!-- Materialize CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .receipt {
            font-family: 'Courier New', Courier, monospace;
            background: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 80%;
            margin: auto;
            margin-top: 50px;
        }
        .receipt-header, .receipt-footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .receipt-items {
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .receipt-item {
            display: flex;
            justify-content: space-between;
        }
    </style>
    <!-- hCaptcha -->
    <script src="https://hcaptcha.com/1/api.js" async defer></script>
</head>
<body class="grey lighten-4">
    <!-- Navbar -->
    <nav>
        <div class="nav-wrapper teal darken-1">
            <div class="container">
                <a href="#" class="brand-logo">購入確認ページ</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="center-align">購入内容の確認</h2>
        <div class="receipt z-depth-2">
            <div class="receipt-header">
                <h4>レシート</h4>
            </div>
            <div class="receipt-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="receipt-item">
                        <span><?= htmlspecialchars($item['name']) ?> - 数量: <?= htmlspecialchars($item['quantity']) ?></span>
                        <span><?= htmlspecialchars($item['price']) ?>円</span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="receipt-footer">
                <h5 class="center-align">合計金額: <?= htmlspecialchars($totalAmount) ?>円</h5>
            </div>
        </div>
        <form method="post" class="center-align">
            <div class="h-captcha" data-sitekey="7d04e186-a1da-4570-8212-26b6e7fe32a4"></div> <!-- hCaptchaのサイトキー -->
            <button type="submit" class="btn waves-effect waves-light">購入する</button>
        </form>
    </div>
    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>

