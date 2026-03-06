<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "Please log in first."]);
    exit;
}

$user_email = $_SESSION['email'];
$trainer_email = $_POST['trainer_email'];

// Fetch user's name from session or database if not set
if (!isset($_SESSION['name'])) {
    $fetchUser = $conn->prepare("SELECT name FROM users WHERE email = ?");
    $fetchUser->bind_param("s", $user_email);
    $fetchUser->execute();
    $result = $fetchUser->get_result();
    $userData = $result->fetch_assoc();
    $_SESSION['name'] = $userData['name'];
}
$user_name = $_SESSION['name'];

// Check if the user already has an active trainer request (Pending or Accepted)
$checkRequest = $conn->prepare("SELECT * FROM trainer_requests WHERE user_email = ? AND status IN ('Pending', 'Accepted')");
$checkRequest->bind_param("s", $user_email);
$checkRequest->execute();
$result = $checkRequest->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "You can only request one trainer at a time."]);
    exit;
}

// Insert new request
$query = $conn->prepare("INSERT INTO trainer_requests (trainer_email, user_email, user_name, status) VALUES (?, ?, ?, 'Pending')");
$query->bind_param("sss", $trainer_email, $user_email, $user_name);

if ($query->execute()) {
    echo json_encode(["success" => true, "message" => "Trainer request sent."]);
} else {
    echo json_encode(["success" => false, "message" => "Error sending request."]);
}
?>
