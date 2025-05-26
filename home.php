<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ticketing System - Home</title>
    <link rel="stylesheet" href="home.php" />
    <link rel="stylesheet" href="layout.css" />
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
                <div class="ticket-count">Tic: 40</div>
                <div class="settings-icon">&#9881;</div>
            </div>

            <div class="ticket-table-container">
                <table class="ticket-table">
                    <thead>
                        <tr>
                            <th>Ticket No.</th>
                            <th>Date</th>
                            <th>Company Name</th>
                            <th>Description</th>
                        </tr>
                        <tr class="search-row">
                            <th><input type="text" onkeyup="searchTable(0)" placeholder="Search Ticket No."></th>
                            <th><input type="text" onkeyup="searchTable(1)" placeholder="Search Date"></th>
                            <th><input type="text" onkeyup="searchTable(2)" placeholder="Search Company"></th>
                            <th><input type="text" onkeyup="searchTable(3)" placeholder="Search Description"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>001</td>
                            <td>2025-04-30</td>
                            <td>ABC Corp</td>
                            <td>Issue with login system</td>
                        </tr>
                        <tr>
                            <td>002</td>
                            <td>2025-05-01</td>
                            <td>XYZ Ltd</td>
                            <td>Unable to reset password</td>
                        </tr>
                        <tr>
                            <td>003</td>
                            <td>2025-05-01</td>
                            <td>Example Inc</td>
                            <td>Error during payment</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JS for search filter -->
    <script>
        function searchTable(columnIndex) {
            const input = document.querySelectorAll('.search-row input')[columnIndex];
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.ticket-table');
            const tr = table.getElementsByTagName("tr");

            for (let i = 2; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName("td")[columnIndex];
                if (td) {
                    const txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().includes(filter) ? "" : "none";
                }
            }
        }

        const currentPage = window.location.pathname.split("/").pop().replace(".html", "");
        const activeLink = document.getElementById(currentPage);
        if (activeLink) {
            activeLink.classList.add("active");
        }
    </script>

</body>

</html>
