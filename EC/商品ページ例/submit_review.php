<?php
// submit_review.php

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $review = $_POST['review'];

    $stmt = $conn->prepare('INSERT INTO reviews (product_id, name, review) VALUES (?, ?, ?)');
    $stmt->execute([$product_id, $name, $review]);

    header('Location: product_detail.php?id=' . $product_id);
    exit;
}
?>
