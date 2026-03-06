<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate user input
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: http://localhost/fitness_website/frontend/register.html?message=Invalid email format.");
        exit();
    }

    // Validate password strength (at least 8 characters, 1 uppercase, 1 lowercase, 1 number)
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[A-Z]).{8,}$/', $password)) {
        header("Location: http://localhost/fitness_website/frontend/register.html?message=Password must be at least 8 characters long, contain one uppercase letter, one lowercase letter, and one number.");
        exit();
    }

    // Check if email already exists in users table
    $checkUser = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkUser->bind_param("s", $email);
    $checkUser->execute();
    $resultUser = $checkUser->get_result();

    // Check if email exists in trainers or admins table
    $checkTrainer = $conn->prepare("SELECT email FROM trainers WHERE email = ?");
    $checkTrainer->bind_param("s", $email);
    $checkTrainer->execute();
    $resultTrainer = $checkTrainer->get_result();

    $checkAdmin = $conn->prepare("SELECT email FROM admins WHERE email = ?");
    $checkAdmin->bind_param("s", $email);
    $checkAdmin->execute();
    $resultAdmin = $checkAdmin->get_result();

    if ($resultUser->num_rows > 0) {
        header("Location: http://localhost/fitness_website/frontend/register.html?message=This email is already registered as a User.");
        exit();
    } elseif ($resultTrainer->num_rows > 0 || $resultAdmin->num_rows > 0) {
        header("Location: http://localhost/fitness_website/frontend/register.html?message=This email is already registered as a Trainer or Admin.");
        exit();
    }

    // Hash password before storing
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        // Redirect with success message
        header("Location: http://localhost/fitness_website/frontend/login.html?message=Registration successful! You can now log in.&redirect=http://localhost/fitness_website/frontend/login.html");
        exit();
    } else {
        // Redirect with error message
        header("Location: http://localhost/fitness_website/frontend/register.html?message=Error: " . urlencode($stmt->error));
    }

    // Close connections
    $stmt->close();
    $conn->close();
}
?>
