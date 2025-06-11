<?php
// db.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "capstone";

// Single database connection for all operations
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
