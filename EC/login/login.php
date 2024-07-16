<?php
// エラーを表示
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
        // セッションIDを取得
        $session_id = session_id();
    
        // sessionsテーブルにセッションIDが既に存在するか確認
        $check_sql = "SELECT * FROM sessions WHERE session_id = :session_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
        $check_stmt->execute();
    
        if ($check_stmt->rowCount() > 0) {
            // 既存のセッションIDが存在する場合
            $existing_session = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing_session['user_id'] != $_SESSION['user_id']) {
                // ユーザーが異なる場合、古いセッションIDのレコードを削除して新たに保存
                $delete_sql = "DELETE FROM sessions WHERE session_id = :session_id";
                $delete_stmt = $pdo->prepare($delete_sql);
                $delete_stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
                $delete_stmt->execute();
    
                $insert_sql = "INSERT INTO sessions (session_id, user_id) VALUES (:session_id, :user_id)";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $insert_stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
                $insert_stmt->execute();
            }
            // 一致する場合は何もしない
        } else {
            // 新しいセッションIDをsessionsテーブルに挿入
            $insert_sql = "INSERT INTO sessions (session_id, user_id) VALUES (:session_id, :user_id)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $insert_stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
            $insert_stmt->execute();
        }
    
        header('Location: ../main.html'); // メインページへリダイレクト
        exit;
    } else {
        die('ログイン情報が正しくありません。');
    }
} catch (PDOException $e) {
    die('エラー: ' . $e->getMessage());
}
?>
