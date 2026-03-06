<?php
session_start();
header("Content-Type: application/json");

include 'db_config.php'; 

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "User not logged in.", "popup" => true]);
    exit;
}

$email = $_SESSION['email'];
$name = $_POST["name"];
$gender = $_POST["gender"];
$weight = $_POST["weight"];

// Update user details in database
$sql = "UPDATE users SET name=?, gender=?, weight=? WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssis", $name, $gender, $weight, $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Profile updated successfully!", "popup" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error updating profile.", "popup" => true]);
}

$stmt->close();
$conn->close();
?>
