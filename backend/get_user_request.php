<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "trainer_id" => null]);
    exit;
}
// Check if the user is logged in
$user_email = $_SESSION['email'];
$query = $conn->prepare("SELECT trainer_id FROM trainer_requests WHERE user_email = ? AND status = 'Pending'");
$query->bind_param("s", $user_email);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

if ($row) {
    echo json_encode(["success" => true, "trainer_id" => $row['trainer_id']]);
} else {
    echo json_encode(["success" => false, "trainer_id" => null]);
}
?>
