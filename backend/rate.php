<?php
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "root", "", "fitness_db");

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $rating = intval($_POST["rating"]);
    $review = trim($_POST["review"]);

    // Validate input
    if (empty($email) || empty($review) || $rating < 1 || $rating > 5) {
        echo json_encode(["success" => false, "message" => "All fields are required and rating must be between 1 and 5."]);
        exit;
    }

    // Check if the email exists in the users table
    $checkEmailQuery = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmailQuery->bind_param("s", $email);
    $checkEmailQuery->execute();
    $result = $checkEmailQuery->get_result();
    $checkEmailQuery->close();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Invalid email. Please register first."]);
        exit;
    }

    // Check if the user already submitted a review
    $checkReview = $conn->prepare("SELECT * FROM reviews WHERE email = ?");
    $checkReview->bind_param("s", $email);
    $checkReview->execute();
    $existingReview = $checkReview->get_result();
    $checkReview->close();

    if ($existingReview->num_rows > 0) {
        // Update existing review instead of inserting a new one
        $stmt = $conn->prepare("UPDATE reviews SET rating = ?, review = ?, created_at = NOW() WHERE email = ?");
        $stmt->bind_param("iss", $rating, $review, $email);
    } else {
        // Insert a new review if no previous review exists
        $stmt = $conn->prepare("INSERT INTO reviews (email, rating, review) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $email, $rating, $review);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Review submitted successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to submit review."]);
    }

    $stmt->close();
}

$conn->close();
?>
