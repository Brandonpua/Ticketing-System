<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

if (isset($_SESSION['user'])) {
    // Already logged in, redirect to home
    header("Location: home.php");
    exit();
}

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']);
    $password_input = trim($_POST['password']);

    // Prepare query
    $sql = "SELECT * FROM users WHERE (company_name = ? OR tin_no = ?) LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // For now, assume plain password comparison; ideally use password_hash & password_verify
        if ($user['password'] === $password_input) {
            // Login success
            $_SESSION['user'] = [
                'company_name' => $user['company_name'],
                'tin_no' => $user['tin_no'],
                'identifier' => $identifier  // Store the identifier to check for admin
            ];
            header("Location: home.php");
            exit();
        } else {
            $login_error = "Invalid credentials. Try again.";
        }
    } else {
        $login_error = "Invalid credentials. Try again.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login / Sign up Ticketing System</title>
    <link rel="stylesheet" href="login.css" />
</head>

<body>
    <div class="login-container">
        <h2>Login</h2>

        <div class="login-header">Login / Sign up</div>

        <?php if ($login_error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="Login.php">
            <input type="text" name="identifier" placeholder="Company Name / Tin No." required />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit">Enter</button>
        </form>
    </div>
</body>

</html>
