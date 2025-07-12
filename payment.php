<?php
session_start();
include 'db.php';
include 'functions.php';

checkLogin();

// Fetch company details for settings dropdown
$companyName = $_SESSION['user']['company_name'];
$stmt = $conn->prepare("SELECT company_name, tin_no FROM users WHERE company_name = ? LIMIT 1");
$stmt->bind_param("s", $companyName);
$stmt->execute();
$companyDetails = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticketing System - Payment</title>
    <link rel="stylesheet" href="layout.css">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="settings.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="home.php" id="home" class="sidebar-btn">Home</a>
            <a href="package.php" id="package" class="sidebar-btn">Package</a>
            <a href="payment.php" id="payment" class="sidebar-btn active">Payment</a>
            <a href="about.php" id="about" class="sidebar-btn">About us</a>
        </div>

        <!-- Main Content -->
        <div class="main">
            <div class="top-bar">
                <div style="flex:1"></div>
                <div class="settings-icon" onclick="toggleSettings()">&#9881;</div>
                <div class="settings-dropdown" id="settingsDropdown">
                    <div class="company-info">
                        <h4>Company Information</h4>
                        <div class="info-row">
                            <span class="info-label">Company Name:</span>
                            <span class="info-value"><?= htmlspecialchars($companyDetails['company_name']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">TIN Number:</span>
                            <span class="info-value"><?= htmlspecialchars($companyDetails['tin_no']) ?></span>
                        </div>
                    </div>
                    <button class="logout-btn" onclick="confirmLogout()">Logout</button>
                </div>
            </div>
            <h1>Payment</h1>
            <p>Payment details will be here.</p>
        </div>
    </div>
    <script>
        const currentPage = window.location.pathname.split("/").pop().replace(".html", "");
        const activeLink = document.getElementById(currentPage);
        if (activeLink) {
            activeLink.classList.add("active");
        }

        function toggleSettings() {
            const dropdown = document.getElementById('settingsDropdown');
            dropdown.classList.toggle('show');
            document.addEventListener('click', function closeDropdown(e) {
                if (!e.target.matches('.settings-icon') && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                    document.removeEventListener('click', closeDropdown);
                }
            });
        }

        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>

</html>
