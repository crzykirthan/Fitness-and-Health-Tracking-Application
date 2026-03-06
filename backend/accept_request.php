<?php
include 'db_config.php';
session_start();
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "Trainer not logged in."]);
    exit;
}
$request_id = $_POST['request_id'];
$status = $_POST['status']; // "Accepted" or "Rejected"

// Update request status
$update = $conn->prepare("UPDATE trainer_requests SET status = ? WHERE id = ?");
$update->bind_param("si", $status, $request_id);

if ($update->execute()) {
    echo json_encode(["success" => true, "message" => "Request $status successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update request status."]);
}
?>
