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
    $reply = isset($_POST['reply']) ? trim($_POST['reply']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'unsolved';
    $complexity = isset($_POST['complexity']) ? trim($_POST['complexity']) : '';

    // Validate inputs
    if (!$ticket_id || !$company_name || !$from_who || !$description) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    // Validate complexity value
    $valid_complexities = ['', 'low', 'medium', 'high'];
    if (!in_array($complexity, $valid_complexities)) {
        echo json_encode(['success' => false, 'message' => 'Invalid complexity value']);
        exit();
    }

    // Verify company exists
    if (!validateCompany($conn, $company_name)) {
        echo json_encode(['success' => false, 'message' => 'Invalid company selected']);
        exit();
    }

    // Update ticket with reply, status, and complexity
    $stmt = $conn->prepare("UPDATE tickets SET company_name = ?, from_who = ?, description = ?, reply = ?, status = ?, complexity = ? WHERE ticket_id = ?");
    $stmt->bind_param("ssssssi", $company_name, $from_who, $description, $reply, $status, $complexity, $ticket_id);

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
