<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}
// Check if the user is logged in
$user_email = $_SESSION['email'];

// Check if the user already has a pending request
$query = $conn->prepare("SELECT trainer_email FROM trainer_requests WHERE user_email = ? AND status = 'Pending'");
$query->bind_param("s", $user_email);
$query->execute();
$result = $query->get_result();
$request = $result->fetch_assoc();

// Check if the user has a pending request
if ($request) {
    echo json_encode(["success" => true, "trainer_email" => $request['trainer_email']]);
} else {
    echo json_encode(["success" => false, "message" => "No pending requests"]);
}
?>
