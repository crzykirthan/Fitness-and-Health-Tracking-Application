<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$user_email = $_SESSION['email'];

$query = $conn->prepare("SELECT goal_name FROM user_goals WHERE user_email = ?");
$query->bind_param("s", $user_email);
$query->execute();
$result = $query->get_result();
$goal = $result->fetch_assoc();
// Check if the user has set a goal
if ($goal) {
    echo json_encode(["success" => true, "goal" => $goal['goal_name']]);
} else {
    echo json_encode(["success" => false, "message" => "No goal set."]);
}
?>
