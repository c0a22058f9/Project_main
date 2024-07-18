<?php
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// fingerprintの取得
$fingerprint = isset($_POST['fingerprint']) ? $_POST['fingerprint'] : null;
if (!$fingerprint) {
    echo json_encode(['success' => false, 'error' => 'Fingerprint not found']);
    exit;
}

// fingerprintに基づいてユーザーIDを検索
$user_id = null;
$stmt = $conn->prepare("SELECT user_id FROM fingerprints WHERE fingerprint = ?");
$stmt->bind_param("s", $fingerprint);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$username = isset($_POST['name']) ? $_POST['name'] : '';
$comment = isset($_POST['review']) ? $_POST['review'] : '';

if ($product_id > 0 && !empty($username) && !empty($comment)) {
    $stmt = $conn->prepare("INSERT INTO reviews (product_id, username, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $product_id, $username, $comment);
    if ($stmt->execute()) {
        header("Location: merchandise.php?id=$product_id&fingerprint=" . urlencode($fingerprint));
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to insert review']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}

$conn->close();
?>
