<?php
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

// データベース接続
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product_id = (int)$_GET['id'];

$sql = "SELECT username, comment, created_at FROM reviews WHERE product_id='$product_id' ORDER BY created_at DESC";
$result = $conn->query($sql);

$reviews = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // htmlspecialchars_decodeを使用してエスケープを解除
        $reviews[] = [
            'username' => $row['username'],
            'comment' => $row['comment'],
            'created_at' => $row['created_at']
        ];
    }
}
echo json_encode($reviews);

$conn->close();
?>