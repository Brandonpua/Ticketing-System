<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$login_success = false; // Default: login failed

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "capstone";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $identifier = $_POST['identifier'];
    $password_input = $_POST['password'];

    $sql = "SELECT * FROM users WHERE (company_name = ? OR tin_no = ?) AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $identifier, $identifier, $password_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $login_success = true;
    } else {
        echo "<script>alert(' Invalid credentials. Try again.');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login / Sign up - Ticketing System</title>
    <link rel="stylesheet" href="login.css" />
</head>

<body>
    <div class="login-container">
        <div class="login-header">Login / Sign up</div>
        <form class="login-form" method="POST" action="login.php">
            <input type="text" name="identifier" placeholder="Company Name / Tin No." required />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit">Enter</button>
        </form>
    </div>

    <?php if ($login_success): ?>
        <script>
            window.location.href = "home.php";
        </script>
    <?php endif; ?>
</body>

</html>
