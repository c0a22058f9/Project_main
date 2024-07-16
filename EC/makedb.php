<?php
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

// MySQLに接続
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// テーブル作成のSQLクエリ
$sql1 = "CREATE TABLE IF NOT EXISTS cart (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    PRIMARY KEY (user_id, product_id)
)";

$sql2 = "CREATE TABLE IF NOT EXISTS fingerprints (
    fingerprint_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    fingerprint VARCHAR(5000)
)";

$sql3 = "CREATE TABLE IF NOT EXISTS product_tags (
    tag_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    tag VARCHAR(255) NOT NULL
)";

$sql4 = "CREATE TABLE IF NOT EXISTS products (
    product_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    stock INT NOT NULL,
    image1 VARCHAR(255),
    image2 VARCHAR(255),
    image3 VARCHAR(255)
)";

$sql5 = "CREATE TABLE IF NOT EXISTS products_tags (
    tag_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    tag VARCHAR(255) NOT NULL
)";

$sql6 = "CREATE TABLE IF NOT EXISTS sessions (
    session_id VARCHAR(128) NOT NULL PRIMARY KEY,
    user_id INT
)";

$sql7 = "CREATE TABLE IF NOT EXISTS tags (
    tag_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tag VARCHAR(255)
)";

$sql8 = "CREATE TABLE IF NOT EXISTS users (
    user_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    last_name VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    birthday DATE NOT NULL,
    address VARCHAR(255),
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
)";

// クエリの実行
if ($conn->query($sql1) === TRUE &&
    $conn->query($sql2) === TRUE &&
    $conn->query($sql3) === TRUE &&
    $conn->query($sql4) === TRUE &&
    $conn->query($sql5) === TRUE &&
    $conn->query($sql6) === TRUE &&
    $conn->query($sql7) === TRUE &&
    $conn->query($sql8) === TRUE) {
    echo "テーブルが正常に作成または既存でした";
} else {
    echo "エラー: " . $conn->error;
}

// MySQL接続のクローズ
$conn->close();
//リダイレクト
header('Location: ./main.php');
?>
