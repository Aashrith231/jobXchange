<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_id = mysqli_real_escape_string($conn, $_POST['job_id']);
    $action = mysqli_real_escape_string($conn, $_POST['action']);
    
    if (!in_array($action, ['approve', 'reject'])) {
        $_SESSION['error'] = "Invalid action!";
        header("Location: ../public/dashboard_admin.php");
        exit();
    }
    
    $status = ($action == 'approve') ? 'approved' : 'rejected';
    
    $sql = "UPDATE jobs SET status = '$status' WHERE job_id = $job_id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Job " . $status . " successfully!";
    } else {
        $_SESSION['error'] = "Failed to update job status!";
    }
    
    header("Location: ../public/dashboard_admin.php");
    exit();
} else {
    header("Location: ../public/dashboard_admin.php");
    exit();
}
?>

