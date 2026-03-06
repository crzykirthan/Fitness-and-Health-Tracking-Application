<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Check if email exists in trainers or admins table
    $checkTrainer = $conn->prepare("SELECT email FROM trainers WHERE email = ?");
    $checkTrainer->bind_param("s", $email);
    $checkTrainer->execute();
    $resultTrainer = $checkTrainer->get_result();

    $checkAdmin = $conn->prepare("SELECT email FROM admins WHERE email = ?");
    $checkAdmin->bind_param("s", $email);
    $checkAdmin->execute();
    $resultAdmin = $checkAdmin->get_result();
// Check if email exists in users table
    if ($resultTrainer->num_rows > 0 || $resultAdmin->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "This email is already registered as a Trainer or Admin."]);
    } else {
        echo json_encode(["success" => true]);
    }

    exit();
}
?>
