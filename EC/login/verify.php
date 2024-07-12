<?php
// database_config.phpからデータベース接続設定
require 'database_config.php';

// クエリパラメータからトークンを取得「/verify.php?$token」でユーザーは受け取る
$token = $_GET['token'] ?? '';

$message = '';

if (!empty($token)) {
    // トークンを検証
    $sql = "SELECT user_id FROM user_verification WHERE token = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $userId = $stmt->fetchColumn();

    if ($userId) {
        // アカウントを有効化
        $sql = "UPDATE users SET verified = 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);

        // トークンを削除
        $sql = "DELETE FROM user_verification WHERE token = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);

        $message = 'アカウントが正常に有効化されました。';
    } else {
        $message = '無効なトークンです。';
    }
} else {
    $message = 'トークンが提供されていません。';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>アカウント有効化</title>
</head>
<body>
    <h1>アカウント有効化</h1>
    <p><?php echo $message; ?></p>
</body>
</html>