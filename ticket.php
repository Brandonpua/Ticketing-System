<?php
session_start();
include 'db.php';
include 'functions.php';

checkLogin(); // Allow both admin and non-admins

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = trim($_POST['company_name']);
    $description = trim($_POST['description']);
    $from_who = trim($_POST['from_who']);
    $date_created = date('Y-m-d');

    // Validate that the company exists
    if (!validateCompany($conn, $company_name)) {
        die("Invalid company selected.");
    }

    // Check ticket quota for non-admins
    $stmt = $conn->prepare("SELECT ticket FROM users WHERE company_name = ? LIMIT 1");
    $stmt->bind_param("s", $company_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $ticketsLeft = isset($row['ticket']) ? (int)$row['ticket'] : 0;
    $stmt->close();

    if ($ticketsLeft <= 0) {
        // Set error message and redirect
        $_SESSION['ticket_error'] = 'You have reached your ticket limit. Please renew to continue creating tickets.';
        header("Location: home.php");
        exit();
    }

    // Handle file upload
    $attachment = NULL;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_types = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed'
        ];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file = $_FILES['attachment'];
        if ($file['size'] > $max_size) {
            $_SESSION['ticket_error'] = 'Attachment too large (max 5MB).';
            header("Location: home.php");
            exit();
        }
        if (!in_array(mime_content_type($file['tmp_name']), $allowed_types)) {
            $_SESSION['ticket_error'] = 'Invalid file type.';
            header("Location: home.php");
            exit();
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('att_') . '.' . $ext;
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $target = $upload_dir . $new_name;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $attachment = 'uploads/' . $new_name;
        } else {
            $_SESSION['ticket_error'] = 'Failed to upload attachment.';
            header("Location: home.php");
            exit();
        }
    }

    // Simple validation
    if ($company_name && $description && $from_who) {
        $status = 'unsolved';
        $stmt = $conn->prepare("INSERT INTO tickets (company_name, description, from_who, date_created, attachment, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $company_name, $description, $from_who, $date_created, $attachment, $status);

        if ($stmt->execute()) {
            $stmt->close();
            // Decrement ticket quota
            $stmt2 = $conn->prepare("UPDATE users SET ticket = ticket - 1 WHERE company_name = ?");
            $stmt2->bind_param("s", $company_name);
            $stmt2->execute();
            $stmt2->close();
            $conn->close();
            header("Location: home.php");
            exit();
        } else {
            echo "Error inserting ticket: " . $conn->error;
        }
    } else {
        echo "Please fill all fields.";
    }
} else {
    header("Location: home.php");
    exit();
}
