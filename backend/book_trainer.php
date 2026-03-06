<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$user_email = $_SESSION['email'];
$trainer_id = $_POST['trainer_id'];
$trainer_email = $_POST['trainer_email'];

// Fetch user details
$query = $conn->prepare("SELECT name, weight FROM users WHERE email = ?");
$query->bind_param("s", $user_email);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}
// Extract user details
$user_name = $user['name'];
$user_weight = $user['weight'];

// Check if weight is set
if (!$user_weight) {
    echo json_encode(["success" => false, "message" => "Update your profile to book a trainer"]);
    exit;
}

// Check if user already booked a trainer
$checkBooking = $conn->prepare("SELECT * FROM trainer_bookings WHERE user_email = ?");
$checkBooking->bind_param("s", $user_email);
$checkBooking->execute();
$bookingExists = $checkBooking->get_result()->fetch_assoc();

if ($bookingExists) {
    echo json_encode(["success" => false, "message" => "You can book only one trainer at a time"]);
    exit;
}

// Insert new booking
$stmt = $conn->prepare("INSERT INTO trainer_bookings (trainer_id, trainer_email, user_name, user_email, weight) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $trainer_id, $trainer_email, $user_name, $user_email, $user_weight);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Trainer booked successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Booking failed"]);
}
?>
