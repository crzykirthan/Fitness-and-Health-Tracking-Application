<?php
session_start();
header("Content-Type: application/json");

include 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}
// Get user email from session
$email = $_SESSION['email'];

// Fetch user details from the database
$sql = "SELECT name, email, gender, weight FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo json_encode(["success" => true, "user" => $user]);
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
