<?php
session_start();

// Close any active database connections if they exist
if (isset($conn)) {
    $conn->close();
}

session_destroy(); // Destroy all session data
session_write_close(); // Make sure session is written and closed

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page
header("Location: login.php");
exit();
