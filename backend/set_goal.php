<?php
include 'db_config.php';
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "User not logged in", "popup" => true]);
    exit;
}

$user_email = $_SESSION['email'];
$goal_id = isset($_POST['goal_id']) ? intval($_POST['goal_id']) : 0;

// Check if goal_id is valid
$goalNames = [
    1 => "Weight Loss",
    2 => "Muscle Gain",
    3 => "Endurance",
    4 => "Flexibility",
    5 => "Overall Fitness",
    6 => "Mental Wellness"
];

if (!array_key_exists($goal_id, $goalNames)) {
    echo json_encode(["success" => false, "message" => "Invalid goal selection", "popup" => true]);
    exit;
}

$goal_name = $goalNames[$goal_id];

// Check if the user already has a goal
$checkGoal = $conn->prepare("SELECT * FROM user_goals WHERE user_email = ?");
$checkGoal->bind_param("s", $user_email);
$checkGoal->execute();
$result = $checkGoal->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "You have already set a goal. You cannot change it.", "popup" => true]);
    exit;
}

// Insert goal into the database
$stmt = $conn->prepare("INSERT INTO user_goals (user_email, goal_id, goal_name) VALUES (?, ?, ?)");
$stmt->bind_param("sis", $user_email, $goal_id, $goal_name);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Goal set successfully!", "popup" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to set goal.", "popup" => true]);
}

$stmt->close();
$conn->close();
?>
