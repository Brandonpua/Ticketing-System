<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ticketing System - Package</title>
    <link rel="stylesheet" href="package.css" />
    <link rel="stylesheet" href="layout.css">
</head>

<body>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="home.php" id="home" class="sidebar-btn">Home</a>
            <a href="package.php" id="package" class="sidebar-btn">Package</a>
            <a href="payment.php" id="payment" class="sidebar-btn">Payment</a>
            <a href="about.php" id="about" class="sidebar-btn">About us</a>
        </div>

        <!-- Main Content -->
        <div class="main">
            <div class="top-bar">
                <div class="settings-icon">&#9881;</div>
            </div>

            <div class="package-section">
                <div class="package-box orange">Basic Offer</div>
                <div class="package-box gold">Premium Offer</div>
                <div class="package-box yellow">Standard Offer</div>
            </div>

            <div class="remain-ticket">Remain: 40</div>
        </div>
    </div>

    <script>
        const currentPage = window.location.pathname.split("/").pop().replace(".html", "");
        const activeLink = document.getElementById(currentPage);
        if (activeLink) {
            activeLink.classList.add("active");
        }
    </script>

</body>

</html>
