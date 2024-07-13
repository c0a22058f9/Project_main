<?php
ini_set('display_errors', "On");
error_reporting(E_ALL);
session_start();
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";
define('DSN', 'mysql:host=' . $servername . ';dbname=' . $dbname);
define('DB_USER', $username);
define('DB_PASS', $password);

// セッションIDのチェック
$session_id = session_id();
if (empty($session_id)) {
    die('セッションが存在しないか、無効です。');
}

// データベース接続
try {
    $conn = new PDO(DSN, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("接続失敗: " . $e->getMessage());
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

// ユーザー情報を取得
$getUserInfoSql = "SELECT last_name, first_name, birthday, address, email FROM users WHERE user_id = ?";
try {
    $stmt = $conn->prepare($getUserInfoSql);
    $stmt->execute([$user_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$userInfo) {
        die('ユーザー情報が見つかりません。');
    }
} catch (PDOException $e) {
    die("ユーザー情報取得エラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ユーザー情報</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
</head>
<body>
    <nav>
        <div class="nav-wrapper">
            <a href="#" class="brand-logo center">ユーザー情報</a>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col s12">
                <h4>ユーザー情報</h4>
                <p><strong>氏名:</strong> <?php echo htmlspecialchars($userInfo['last_name'] . ' ' . $userInfo['first_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>生年月日:</strong> <?php echo htmlspecialchars($userInfo['birthday'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>住所:</strong> <?php echo htmlspecialchars($userInfo['address'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>メールアドレス:</strong> <?php echo htmlspecialchars($userInfo['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                <button onclick="location.href='update_user_info.php'">編集</button>
                <button onclick="location.href='../index.php'">メインページへ</button>
            </div>
        </div>
    </div>
</body>
</html>
