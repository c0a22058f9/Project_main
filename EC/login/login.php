<?php
session_start();
require 'database_config.php'; // データベース接続情報を含むファイル

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    die('メールアドレスとパスワードを入力してください。');
}

try {
    $pdo = new PDO(DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // ログイン成功
        $_SESSION['user_id'] = $user['id'];
        header('Location: ../index.html'); // メインページへリダイレクト
        exit;
    } else {
        die('ログイン情報が正しくありません。');
    }
} catch (PDOException $e) {
    die('データベースエラー: ' . $e->getMessage());
}
?>