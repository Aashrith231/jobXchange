<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'candidate') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $job_id = mysqli_real_escape_string($conn, $_POST['job_id']);
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    
    // Validation
    if (empty($message)) {
        $_SESSION['error'] = "Message is required!";
        header("Location: ../public/apply_job.php?job_id=$job_id");
        exit();
    }
    
    // Check if job exists and is approved
    $job_check = "SELECT * FROM jobs WHERE job_id = $job_id AND status = 'approved'";
    $job_result = mysqli_query($conn, $job_check);
    
    if (mysqli_num_rows($job_result) == 0) {
        $_SESSION['error'] = "Job not found or not available!";
        header("Location: ../public/dashboard_candidate.php");
        exit();
    }
    
    // Check if already applied
    $check_sql = "SELECT * FROM applications WHERE job_id = $job_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "You have already applied for this job!";
        header("Location: ../public/dashboard_candidate.php");
        exit();
    }
    
    // Insert application
    $sql = "INSERT INTO applications (job_id, user_id, message, status) 
            VALUES ($job_id, $user_id, '$message', 'pending')";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Application submitted successfully!";
        header("Location: ../public/dashboard_candidate.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to submit application. Please try again.";
        header("Location: ../public/apply_job.php?job_id=$job_id");
        exit();
    }
} else {
    header("Location: ../public/dashboard_candidate.php");
    exit();
}
?>

