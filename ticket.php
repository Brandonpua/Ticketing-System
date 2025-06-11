<?php
session_start();
include 'db.php';
include 'functions.php';

checkAdmin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = trim($_POST['company_name']);
    $description = trim($_POST['description']);
    $from_who = trim($_POST['from_who']);
    $date_created = date('Y-m-d');

    // Validate that the company exists
    if (!validateCompany($conn, $company_name)) {
        die("Invalid company selected.");
    }

    // Simple validation
    if ($company_name && $description && $from_who) {
        $stmt = $conn->prepare("INSERT INTO tickets (company_name, description, from_who, date_created) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $company_name, $description, $from_who, $date_created);

        if ($stmt->execute()) {
            $stmt->close();
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
