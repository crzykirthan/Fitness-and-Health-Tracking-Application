<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: http://localhost/fitness_website/backend/login.php");
    exit;
}

// Fetch Users
$query_users = $conn->query("SELECT id, name, email FROM users");

// Fetch Trainers
$query_trainers = $conn->query("SELECT id, name, email, specialization FROM trainers");

// Fetch Trainer Requests
$query_requests = $conn->query("
    SELECT tr.id, tr.user_name, tr.user_email, tr.trainer_email, t.name AS trainer_name, tr.status 
    FROM trainer_requests tr
    JOIN trainers t ON tr.trainer_email = t.email
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FitnessPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-black text-white">

<div class="flex">
    <div class="w-1/5 bg-gray-900 h-screen p-6 fixed top-0 left-0">
        <h2 class="text-2xl font-bold text-yellow-400">Admin Dashboard</h2>
        <ul class="mt-6">
            <li class="p-2 bg-gray-700 rounded mt-2"><a href="#" class="block">👥 Manage Users</a></li>
            <li class="p-2 bg-gray-700 rounded mt-2"><a href="#" class="block">🏋️‍♂️ Manage Trainers</a></li>
            <li class="p-2 bg-gray-700 rounded mt-2"><a href="#" class="block">📑 View Bookings</a></li>
            <li class="p-2 bg-red-600 rounded mt-2"><a href="logout.php" class="block">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="w-4/5 ml-[20%] p-6 overflow-y-auto h-screen">
        <h2 class="text-3xl font-bold text-yellow-400">Admin Panel</h2>

        <!-- Users Table -->
<div class="mt-6 bg-gray-800 p-4 rounded shadow">
    <h3 class="text-xl font-bold text-yellow-400">Users</h3>
    <table class="w-full border border-yellow-400 mt-4">
        <tr class="bg-gray-900 text-yellow-400">
            <th class="p-2 border">Name</th>
            <th class="p-2 border">Email</th>
            <th class="p-2 border">Actions</th>
        </tr>
        <?php while ($user = $query_users->fetch_assoc()): ?>
            <tr class="bg-gray-700">
                <td class="p-2 border">
                    <input type="text" value="<?= $user['name'] ?>" id="name_<?= $user['id'] ?>" class="bg-gray-600 text-white p-1 rounded">
                </td>
                <td class="p-2 border">
                    <input type="email" value="<?= $user['email'] ?>" id="email_<?= $user['id'] ?>" class="bg-gray-600 text-white p-1 rounded" readonly>
                </td>
                <td class="p-2 border">
                    <button onclick="updateUser(<?= $user['id'] ?>)" class="bg-yellow-500 px-4 py-2 rounded">Save</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>


        <!-- Trainers Table -->
<div class="mt-6 bg-gray-800 p-4 rounded shadow">
    <h3 class="text-xl font-bold text-yellow-400">Trainers</h3>
    <table class="w-full border border-yellow-400 mt-4">
        <tr class="bg-gray-900 text-yellow-400">
            <th class="p-2 border">Name</th>
            <th class="p-2 border">Email</th>
            <th class="p-2 border">Specialization</th>
            <th class="p-2 border">Actions</th>
        </tr>
        <?php while ($trainer = $query_trainers->fetch_assoc()): ?>
            <tr class="bg-gray-700">
                <td class="p-2 border"><?= $trainer['name'] ?></td>
                <td class="p-2 border"><?= $trainer['email'] ?></td>
                <td class="p-2 border"><?= $trainer['specialization'] ?></td>
                <td class="p-2 border text-center">
                    <i class="fas fa-edit text-blue-400 cursor-pointer px-2"></i>
                    <i class="fas fa-trash text-red-500 cursor-pointer px-2"></i>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>


        <!-- Trainer Requests Table -->
        <div class="mt-6 bg-gray-800 p-4 rounded shadow">
            <h3 class="text-xl font-bold text-yellow-400">Trainer Requests</h3>
            <table class="w-full border border-yellow-400 mt-4">
                <tr class="bg-gray-900 text-yellow-400">
                    <th class="p-2 border">User</th>
                    <th class="p-2 border">Trainer</th>
                    <th class="p-2 border">Status</th>
                    <th class="p-2 border">Actions</th>
                </tr>
                <?php while ($request = $query_requests->fetch_assoc()): ?>
                    <tr class="bg-gray-700">
                        <td class="p-2 border"><?= $request['user_name'] ?></td>
                        <td class="p-2 border"><?= $request['trainer_name'] ?></td>
                        <td class="p-2 border" id="status_<?= $request['id'] ?>"><?= $request['status'] ?></td>
                        <td class="p-2 border">
                            <button onclick="showModifyOptions(<?= $request['id'] ?>)" id="modifyBtn_<?= $request['id'] ?>" class="bg-yellow-500 px-4 py-2 rounded">Modify</button>
                            
                            <div id="actionButtons_<?= $request['id'] ?>" class="hidden">
                                <button onclick="updateStatus(<?= $request['id'] ?>, 'Accepted')" class="bg-green-500 px-4 py-2 rounded">Accept</button>
                                <button onclick="updateStatus(<?= $request['id'] ?>, 'Rejected')" class="bg-red-500 px-4 py-2 rounded">Reject</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

<!-- Popup Message -->
<div id="popup" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded text-black text-center w-96">
        <p id="popupText" class="mb-4"></p>
        <button onclick="closePopup()" class="mt-4 bg-yellow-500 px-6 py-2 rounded text-center">OK</button>
    </div>
</div>

<script>
// JavaScript for handling trainer requests and user updates
    // Function to show modify options for trainer requests
    function showModifyOptions(id) {
        document.getElementById(`modifyBtn_${id}`).classList.add("hidden");
        document.getElementById(`actionButtons_${id}`).classList.remove("hidden");
    }
// Function to update trainer request status
    function updateStatus(id, status) {
        fetch('update_request.php', {
            method: 'POST',
            body: new URLSearchParams({ id, status })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById(`status_${id}`).textContent = status;
            showPopup("Trainer request updated successfully!");

            document.getElementById(`modifyBtn_${id}`).classList.remove("hidden");
            document.getElementById(`actionButtons_${id}`).classList.add("hidden");
        });
    }

    //showPopup function to display messages
    function showPopup(message) {
        document.getElementById("popupText").textContent = message;
        document.getElementById("popup").classList.remove("hidden");
    }

    function closePopup() {
        document.getElementById("popup").classList.add("hidden");
    }

    
    // Update Trainer Details Functionality (Similar to update_trainer.php)
    function updateUser(id) {
    const name = document.getElementById(`name_${id}`).value;
    const email = document.getElementById(`email_${id}`).value;

    fetch("update_user.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ id, name, email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showPopup("User updated successfully!");
            
            // If the logged-in user was updated, refresh the profile page
            if (email === sessionStorage.getItem("loggedInUserEmail")) {
                window.location.reload();
            }
        } else {
            showPopup(data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showPopup("Something went wrong. Please try again.");
    });
}


</script>

</body>
</html>
