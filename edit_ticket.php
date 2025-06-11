<?php
session_start();
include 'db.php';
include 'functions.php';

checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
    $company_name = trim($_POST['company_name']);
    $from_who = trim($_POST['from_who']);
    $description = trim($_POST['description']);

    // Validate inputs
    if (!$ticket_id || !$company_name || !$from_who || !$description) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }

    // Verify company exists
    if (!validateCompany($conn, $company_name)) {
        echo json_encode(['success' => false, 'message' => 'Invalid company selected']);
        exit();
    }

    // Update ticket
    $stmt = $conn->prepare("UPDATE tickets SET company_name = ?, from_who = ?, description = ? WHERE ticket_id = ?");
    $stmt->bind_param("sssi", $company_name, $from_who, $description, $ticket_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating ticket']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
