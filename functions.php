<?php
// functions.php
// Check if user is logged in
function checkLogin()
{
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
}

// Check if user is admin
function checkAdmin()
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['identifier'] !== 'ADMIN') {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        } else {
            header("Location: login.php");
        }
        exit();
    }
}

// Validate company exists
function validateCompany($conn, $company_name)
{
    $stmt = $conn->prepare("SELECT company_name FROM users WHERE company_name = ? LIMIT 1");
    $stmt->bind_param("s", $company_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
}
