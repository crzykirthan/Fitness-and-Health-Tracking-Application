<?php
include 'db_config.php';
session_start();

// Redirect if trainer is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: http://localhost/fitness_website/frontend/login.html?message=Please login to access the dashboard.");
    exit;
}

$trainer_email = $_SESSION['email'];

// Fetch trainer details
$query = $conn->prepare("SELECT name, email, specialization, experience FROM trainers WHERE email = ?");
$query->bind_param("s", $trainer_email);
$query->execute();
$trainer = $query->get_result()->fetch_assoc();

// Fetch trainer requests
$query_requests = $conn->prepare("SELECT user_email, user_name, status FROM trainer_requests WHERE trainer_email = ?");
$query_requests->bind_param("s", $trainer_email);
$query_requests->execute();
$requests = $query_requests->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - FitnessPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-black text-white">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-1/5 bg-gray-900 h-screen p-6">
            <h2 class="text-2xl font-bold text-yellow-400">Trainer Dashboard</h2>
            <ul class="mt-6 space-y-2">
                <li class="p-2 bg-gray-700 rounded"><a href="#" class="block">🏠 Home</a></li>
                <li class="p-2 bg-gray-700 rounded"><a href="#" class="block">📋 User Requests</a></li>
                <li class="p-2 bg-gray-700 rounded"><a href="#" class="block">📖 Training Guides</a></li>
                <li class="p-2 bg-red-600 rounded"><a href="logout.php" class="block">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="w-4/5 p-6">
            <h2 class="text-4xl font-bold text-yellow-400">Welcome, <?= htmlspecialchars($trainer['name']) ?></h2>

            <!-- Trainer Profile -->
            <div class="mt-6 p-6 bg-gray-800 rounded shadow-lg text-lg">
                <h3 class="text-2xl font-semibold text-yellow-400">📊 Your Profile</h3>
                <p class="mt-2">Email: <span class="font-bold text-lg"><?= htmlspecialchars($trainer['email']) ?></span></p>
                <p class="mt-2">Experience: <span class="font-bold text-lg"><?= htmlspecialchars($trainer['experience']) ?> Years</span></p>
                <p class="mt-2">Specialization: <span class="font-bold text-lg"><?= htmlspecialchars($trainer['specialization']) ?></span></p>
            </div>

            <!-- Booking Requests -->
            <div class="mt-6 p-6 bg-gray-800 rounded shadow-lg text-lg">
                <h3 class="text-2xl font-semibold text-yellow-400">📥 User Booking Requests</h3>
                <?php if ($requests->num_rows > 0): ?>
                    <table class="w-full mt-4 border-collapse border border-yellow-400 text-lg">
                        <thead>
                            <tr class="bg-gray-900 text-yellow-400">
                                <th class="p-4 border border-yellow-400">User Name</th>
                                <th class="p-4 border border-yellow-400">Email</th>
                                <th class="p-4 border border-yellow-400">Status</th>
                                <th class="p-4 border border-yellow-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php while ($row = $requests->fetch_assoc()): ?>
        <tr class="bg-gray-700 text-white">
            <td class="p-4 border border-yellow-400 font-bold text-lg"><?= htmlspecialchars($row['user_name']) ?></td>
            <td class="p-4 border border-yellow-400 font-bold text-lg"><?= htmlspecialchars($row['user_email']) ?></td>
            <td class="p-4 border border-yellow-400 font-bold text-lg <?= $row['status'] == 'Accepted' ? 'text-green-400' : ($row['status'] == 'Rejected' ? 'text-red-400' : '') ?>">
                <?= htmlspecialchars($row['status']) ?>
            </td>
            <td class="p-4 border border-yellow-400">
                <?php if ($row['status'] == 'Pending'): ?>
                    <button class="accept-btn bg-green-500 px-4 py-2 rounded text-lg" data-user="<?= $row['user_email'] ?>">Accept</button>
                    <button class="reject-btn bg-red-500 px-4 py-2 rounded text-lg" data-user="<?= $row['user_email'] ?>">Reject</button>
                <?php else: ?>
                    <span class="font-bold text-lg"><?= htmlspecialchars($row['status']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

                    </table>
                <?php else: ?>
                    <p class="mt-4 text-gray-400 text-lg">No booking requests available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

  <!-- Popup Message -->
<div id="popupMessage" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-75">
    <div class="bg-white p-6 rounded shadow-lg text-center">
        <p id="popupText" class="text-lg text-black"></p>
        <button id="closePopupBtn" class="mt-4 bg-yellow-500 px-4 py-2 rounded">OK</button>
    </div>
</div>


    <script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".accept-btn").forEach(button => {
            button.addEventListener("click", function () {
                const userEmail = this.getAttribute("data-user");
                updateRequestStatus(userEmail, "Accepted", this);
            });
        });

        document.querySelectorAll(".reject-btn").forEach(button => {
            button.addEventListener("click", function () {
                const userEmail = this.getAttribute("data-user");
                updateRequestStatus(userEmail, "Rejected", this);
            });
        });
        // Function to update request status
        function updateRequestStatus(userEmail, status, button) {
            fetch("http://localhost/fitness_website/backend/process_request.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ user_email: userEmail, status: status })
            })
            .then(response => response.json())
            .then(data => {
                console.log("Server Response:", data); // Debugging Log

                if (data.success) {
                    showPopup(`Request ${status}`);
                    button.closest("tr").querySelector("td:nth-child(3)").textContent = status;
                    button.closest("td").innerHTML = `<span class="font-bold text-lg">${status}</span>`;
                } else {
                    showPopup(data.message); // Show actual error
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                showPopup("Error processing request.");
            });
        }

        // Function to show popup messages
    function showPopup(message) {
        document.getElementById("popupText").textContent = message;
        document.getElementById("popupMessage").classList.remove("hidden");
    }

    // Function to close popup
    function closePopup() {
        document.getElementById("popupMessage").classList.add("hidden");
    }

    // Ensure the OK button is working
    document.getElementById("closePopupBtn").addEventListener("click", closePopup);
    });
</script>
</body>
</html>
