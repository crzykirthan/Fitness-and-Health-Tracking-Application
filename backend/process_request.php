<?php
include 'db_config.php';
session_start();

header("Content-Type: application/json");

// Ensure trainer is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Trainer not logged in"]);
    exit;
}

// Get trainer email from session
$trainer_email = $_SESSION['email'];

// Validate POST parameters
if (!isset($_POST['user_email']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
    exit;
}

$user_email = filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL);
$status = trim($_POST['status']);

// Validate status value
$allowed_statuses = ['Accepted', 'Rejected'];
if (!$user_email || !in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

// Check if the request exists and is still pending
$checkRequest = $conn->prepare("SELECT id FROM trainer_requests WHERE trainer_email = ? AND user_email = ? AND status = 'Pending'");
$checkRequest->bind_param("ss", $trainer_email, $user_email);
$checkRequest->execute();
$result = $checkRequest->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Request not found or already processed"]);
    $checkRequest->close();
    $conn->close();
    exit;
}

// Update the request status and set notified = 1
$updateRequest = $conn->prepare("UPDATE trainer_requests SET status = ?, notified = 1 WHERE trainer_email = ? AND user_email = ?");
$updateRequest->bind_param("sss", $status, $trainer_email, $user_email);

if ($updateRequest->execute()) {
    echo json_encode(["success" => true, "message" => "Request updated to $status and user notified"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error updating request"]);
}

// Close statements and database connection
$checkRequest->close();
$updateRequest->close();
$conn->close();
?>
