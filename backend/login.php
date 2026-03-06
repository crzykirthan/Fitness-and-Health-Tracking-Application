<?php
include 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]);

    if (empty($role)) {
        header("Location: http://localhost/fitness_website/frontend/login.html?message=Please select a role.");
        exit();
    }

    // Prepare query based on role
    if ($role === 'user') {
        $sql = "SELECT id, name, email, password FROM users WHERE email = ?";
    } elseif ($role === 'trainer') {
        $sql = "SELECT id, name, email, specialization, password FROM trainers WHERE email = ?";
    } elseif ($role === 'admin') {
        $sql = "SELECT id, name, email, password FROM admins WHERE email = ?";
    } else {
        header("Location: http://localhost/fitness_website/frontend/login.html?message=Invalid role selection.");
        exit();
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        if ($role === 'user') {
            $stmt->bind_result($id, $name, $user_email, $hashed_password);
        } elseif ($role === 'trainer') {
            $stmt->bind_result($id, $name, $trainer_email, $specialization, $hashed_password);
        } elseif ($role === 'admin') {
            $stmt->bind_result($id, $name, $admin_email, $hashed_password);
        }
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;

            // 🔹 Check if the user has a trainer response
            if ($role === 'user') {
                $responseQuery = $conn->prepare("SELECT trainer_email, status FROM trainer_requests WHERE user_email=? AND notified=0");
                $responseQuery->bind_param("s", $email);
                $responseQuery->execute();
                $responseQuery->store_result();

                if ($responseQuery->num_rows > 0) {
                    $notifications = [];
                    while ($responseQuery->fetch()) {
                        $notifications[] = "Trainer ($trainer_email) has $status your request.";
                    }
                    $_SESSION['trainer_notifications'] = $notifications;

                    // Mark as notified
                    $updateQuery = $conn->prepare("UPDATE trainer_requests SET notified=1 WHERE user_email=?");
                    $updateQuery->bind_param("s", $email);
                    $updateQuery->execute();
                    $updateQuery->close();
                }
                $responseQuery->close();
            }

            // Redirect based on role
            if ($role === 'user') {
                header("Location: http://localhost/fitness_website/frontend/home.html");
            } elseif ($role === 'trainer') {
                $_SESSION['specialization'] = $specialization;
                header("Location: http://localhost/fitness_website/backend/trainer_dashboard.php");
            } elseif ($role === 'admin') {
                header("Location: http://localhost/fitness_website/backend/admin_dashboard.php");
            }
            exit();
        } else {
            header("Location: http://localhost/fitness_website/frontend/login.html?message=Invalid password.");
            exit();
        }
    } else {
        header("Location: http://localhost/fitness_website/frontend/login.html?message=No account found with this email.");
        exit();
    }

    $stmt->close();
}
$conn->close();
?>
