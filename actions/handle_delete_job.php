<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_id = mysqli_real_escape_string($conn, $_POST['job_id']);
    
    // Delete job (cascading will delete related applications)
    $sql = "DELETE FROM jobs WHERE job_id = $job_id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Job deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete job!";
    }
    
    header("Location: ../public/dashboard_admin.php");
    exit();
} else {
    header("Location: ../public/dashboard_admin.php");
    exit();
}
?>

