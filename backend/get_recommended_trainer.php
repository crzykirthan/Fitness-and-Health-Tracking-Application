<?php
include 'db_config.php';

$goal_id = $_GET['goal_id'];

// Trainer recommendations based on goals
$trainerRecommendations = [
    1 => ["John Doe", "johndoe@gmail.com"],   // Weight Loss
    2 => ["Emily Davis", "emilydavis@gmail.com"], // Muscle Gain
    3 => ["Mike Johnson", "mikejohnson@gmail.com"], // Endurance
    4 => ["Jane Smith", "janesmith@gmail.com"], // Flexibility
    5 => ["Sarah Lee", "sarahlee@gmail.com"], // Overall Fitness
    6 => ["Laura Wilson", "laurawilson@gmail.com"] // Mental Wellness
];

if (array_key_exists($goal_id, $trainerRecommendations)) {
    echo json_encode([
        "success" => true,
        "trainer_name" => $trainerRecommendations[$goal_id][0],
        "trainer_email" => $trainerRecommendations[$goal_id][1]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "No trainer available for this goal."]);
}
?>
