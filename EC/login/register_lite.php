<?php
session_start();
require 'lite_database_config.php'; // データベース接続情報を含むファイル
// フォームからのデータを取得
$last_name = $_POST['last_name'];
$first_name = $_POST['first_name'];
$birthday = $_POST['birthday'];
$zipcode = $_POST['zipcode']; // HTMLフォームにはaddressフィールドがないため、zipcodeを使用
$prefecture = $_POST['prefecture'];
$city = $_POST['city'];
$street = $_POST['street'];
$address = $prefecture . $city . $street; // addressを組み立てる
$email = $_POST['email'];
$password = $_POST['password'];
// 入力データの検証
if (empty($last_name) || empty($first_name) || empty($birthday) || empty($address) || empty($email) || empty($password)) {
    die('すべてのフィールドを入力してください');
}
// パスワードをハッシュ化
$password_hash = password_hash($password, PASSWORD_BCRYPT);
try {
    // データベースに接続
    $pdo = new PDO(DSN);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // SQLクエリを準備
    $stmt = $pdo->prepare('INSERT INTO Users (last_name, first_name, birthday, address, email, password_hash) VALUES (:last_name, :first_name, :birthday, :address, :email, :password_hash)');
    // パラメータをバインド
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':birthday', $birthday);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $password_hash);
    // クエリを実行
    $stmt->execute();
    echo '新規登録が完了しました';
} catch (PDOException $e) {
    echo 'エラー: ' . $e->getMessage();
}
?>