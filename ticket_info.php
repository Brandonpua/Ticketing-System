<?php
session_start();
include 'db.php';
include 'functions.php';

checkLogin();

// Get ticket ID from request
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ticket_id > 0) {
    // Prepare query based on user role
    if ($_SESSION['user']['identifier'] === 'ADMIN') {
        // Admin can view any ticket
        $stmt = $conn->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
        $stmt->bind_param("i", $ticket_id);
    } else {
        // Regular users can only view their company's tickets
        $stmt = $conn->prepare("SELECT * FROM tickets WHERE ticket_id = ? AND company_name = ?");
        $stmt->bind_param("is", $ticket_id, $_SESSION['user']['company_name']);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Return ticket details as JSON
        header('Content-Type: application/json');
        echo json_encode([
            'ticket_id' => $row['ticket_id'],
            'date_created' => $row['date_created'],
            'company_name' => $row['company_name'],
            'description' => $row['description'],
            'from_who' => $row['from_who']
        ]);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'You do not have permission to view this ticket']);
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ticket ID']);
}

$conn->close();
