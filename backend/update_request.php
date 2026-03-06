<?php
include 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Update request status and set notified = 1
    $stmt = $conn->prepare("UPDATE trainer_requests SET status=?, notified=1 WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Request updated successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update request."]);
    }

    $stmt->close();
    $conn->close();
}
?>
