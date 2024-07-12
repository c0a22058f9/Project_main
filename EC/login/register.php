<?php
session_start();
require 'database_config.php'; // データベース接続情報を含むファイル

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
    // データベース接続
    $pdo = new PDO(DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
