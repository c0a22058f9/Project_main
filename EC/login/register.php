<?php
//エラーを表示する
ini_set('display_errors', "On");
error_reporting(E_ALL);
session_start();
require '../login/database_config.php'; // データベース接続情報を含むファイル

// データベース接続
$pdo = new PDO(DSN, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// データベースが存在しない場合に作成し、そのデータベースを使用
$dbname = 'ecdatabase';
$pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
$pdo->exec("USE $dbname");

// ユーザーテーブル作成
$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    birthday DATE NOT NULL,
    address VARCHAR(255),
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
)";
$pdo->exec($createUsersTable);

// タグテーブル作成
$createTagsTable = "CREATE TABLE IF NOT EXISTS tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tag VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";
$pdo->exec($createTagsTable);

// 指紋テーブル作成
$createFingerprintsTable = "CREATE TABLE IF NOT EXISTS fingerprints (
    fingerprint_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fingerprint VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";
$pdo->exec($createFingerprintsTable);

// ユーザセッションID保存テーブル作成
$createUserSessionsTable = "CREATE TABLE IF NOT EXISTS user_sessions (
    user_session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

// POSTデータを受け取る
$lastName = $_POST['last_name'] ?? '';
$firstName = $_POST['first_name'] ?? '';
$birthday = $_POST['birthday'] ?? '';
$zipcode = $_POST['zipcode'] ?? '';
$prefecture = $_POST['prefecture'] ?? '';
$city = $_POST['city'] ?? '';
$street = $_POST['street'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// データ検証（簡易的な例）
if (empty($lastName) || empty($firstName) || empty($email) || empty($password)) {
    // 必須フィールドが空の場合はエラー
    die('必須フィールドが入力されていません。');
}

// パスワードをハッシュ化
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    // SQL文を準備
    $address = $zipcode . ' ' . $prefecture . ' ' . $city . ' ' . $street;
    $sql = "INSERT INTO users (last_name, first_name, birthday, address, email, password_hash) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    // SQL文を実行
    $stmt->execute([$lastName, $firstName, $birthday, $address, $email, $passwordHash]);

    // ユーザー登録後の処理
    $userId = $pdo->lastInsertId(); // 最後に挿入された行のIDを取得

    // 確認メールを送信
    $subject = 'アカウントの確認';
    // 入力された情報の確認メールを送信
    $message = "以下の情報で登録しました。\n\n";
    $message .= "氏名: {$lastName} {$firstName}\n";
    $message .= "メールアドレス: {$email}\n\n";

    $headers = array(
        'From' => 'test@test',
        'Reply-To' => 'test@test',
        'X-Mailer' => 'PHP/' . phpversion()
    );
    mail($email, $subject, $message, $headers);

    // 完了メッセージを変数に格納
    $output = '登録が完了しました。';
} catch (PDOException $e) {
    die('データベースエラー: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!--$outputに格納されたメッセージを表示-->
    <title>登録</title>
</head>
<body>
    <nav>
        <div class="nav-wrapper">
            <a href="#" class="brand-logo center">登録完了</a>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col s12">
                <p class="flow-text"><?php echo $output; ?></p>
                <!--メインページへのリンク-->
                <button onclick="location.href='../index.php'">メインページへ</button>
            </div>
        </div>
    </div>
</body>
</html>
