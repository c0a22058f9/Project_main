<?php
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

$sql = "SELECT id, name FROM products WHERE name LIKE ?";
$stmt = $conn->prepare($sql);
$searchQuery = '%' . $query . '%';
$stmt->bind_param("s", $searchQuery);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($suggestions);
?>
