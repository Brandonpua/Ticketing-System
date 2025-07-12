<?php
session_start();
include 'db.php';
include 'functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['identifier'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
    $reply = isset($_POST['reply']) ? trim($_POST['reply']) : '';
    if (!$ticket_id || $reply === '') {
        echo json_encode(['success' => false, 'message' => 'Missing ticket ID or reply.']);
        exit();
    }
    $stmt = $conn->prepare("UPDATE tickets SET reply = ?, status = 'solved' WHERE ticket_id = ?");
    $stmt->bind_param("si", $reply, $ticket_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update ticket.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
$conn->close();
