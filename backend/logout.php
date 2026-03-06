<?php
session_start();

// Check if the user is a trainer and unset relevant session variables
if (isset($_SESSION['trainer_email'])) {
    // Trainer-specific session variables to unset
    unset($_SESSION['trainer_name']);
    unset($_SESSION['trainer_email']);
    unset($_SESSION['specialization']);
} else {
    // General user session variables to unset
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['email']);
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Remove session cookie (optional but recommended)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Display logout popup
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #000;
            font-family: Arial, sans-serif;
        }
        .popup {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.2);
        }
        .popup button {
            background-color: #ffcc00;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .popup button:hover {
            background-color: #e6b800;
        }
    </style>
</head>
<body>
    <div class="popup">
        <h2>You have been logged out successfully.</h2>
        <button onclick="redirect()">OK</button>
    </div>
    <script>
        function redirect() {
            window.location.href = "http://localhost/fitness_website/frontend/index.html";
        }
    </script>
</body>
</html>';
exit();
?>
