<?php
session_start();
include 'db.php';
include 'functions.php';

checkAdmin();

// Update existing tickets with 'unsolve' status to 'unsolved'
$stmt = $conn->prepare("UPDATE tickets SET status = 'unsolved' WHERE status = 'unsolve'");
if ($stmt->execute()) {
    $affected_rows = $stmt->affected_rows;
    echo "Updated $affected_rows tickets from 'unsolve' to 'unsolved'<br>";
} else {
    echo "Error updating tickets: " . $conn->error . "<br>";
}
$stmt->close();

// Show current status distribution
$result = $conn->query("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
echo "<h3>Current Status Distribution:</h3>";
while ($row = $result->fetch_assoc()) {
    echo "Status: " . htmlspecialchars($row['status']) . " - Count: " . $row['count'] . "<br>";
}

$conn->close();
echo "<br><a href='home.php'>Back to Home</a>";
