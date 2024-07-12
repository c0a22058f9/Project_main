<?php
//エラーを表示
ini_set('display_errors', "On");
error_reporting(E_ALL);
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

    // プリペアドステートメントを使用してSQLインジェクションを防ぐ
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // ログイン成功
        $_SESSION['user_id'] = $user['user_id'];

        // セッションIDを生成
        $session_id = session_id();

        // user_sessionsテーブルにセッションIDを保存
        $insert_sql = "INSERT INTO user_sessions (user_id, session_id) VALUES (:user_id, :session_id)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
        $insert_stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
        $insert_stmt->execute();

        header('Location: ../index.html'); // メインページへリダイレクト
        exit;
    } else {
        die('ログイン情報が正しくありません。');
    }
} catch (PDOException $e) {
    die('データベースエラー: ' . $e->getMessage());
}
?>
