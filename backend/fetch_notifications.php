<?php
session_start();
include 'db_config.php';

header("Content-Type: application/json");

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

$user_email = $_SESSION['email'];

// Fetch the latest trainer response that has not been seen yet
$stmt = $conn->prepare("
    SELECT id, trainer_email, status 
    FROM trainer_requests 
    WHERE user_email = ? AND notified = 1 
    ORDER BY id DESC LIMIT 1"); // Fetch the latest request
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
if ($row = $result->fetch_assoc()) {
    $notifications[] = "Trainer (" . htmlspecialchars($row['trainer_email']) . ") has " . strtolower(htmlspecialchars($row['status'])) . " your request.";

    // **Mark the notification as seen in DB** to prevent duplicate alerts
    $updateStmt = $conn->prepare("UPDATE trainer_requests SET notified = 0 WHERE id = ?");
    $updateStmt->bind_param("i", $row['id']);
    $updateStmt->execute();
    $updateStmt->close();
}
$stmt->close();

if (!empty($notifications)) {
    echo json_encode(["success" => true, "notifications" => $notifications]);
} else {
    echo json_encode(["success" => false, "message" => "No new notifications."]);
}

$conn->close();
?>
