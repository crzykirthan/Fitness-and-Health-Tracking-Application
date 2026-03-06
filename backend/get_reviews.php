<?php
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "root", "", "fitness_db");

if ($conn->connect_error) {
    die(json_encode([])); // Return empty response if connection fails
}

// Fetch reviews
$result = $conn->query("SELECT email, rating, review, created_at FROM reviews ORDER BY created_at DESC");
$reviews = [];

while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

echo json_encode($reviews);
$conn->close();
?>
