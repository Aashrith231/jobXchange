<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'recruiter') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recruiter_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $skills = mysqli_real_escape_string($conn, trim($_POST['skills']));
    $salary = mysqli_real_escape_string($conn, trim($_POST['salary']));
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    
    // Validation
    if (empty($title) || empty($description) || empty($skills) || empty($salary) || empty($type)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: ../public/post_job.php");
        exit();
    }
    
    if (!in_array($type, ['internship', 'full-time'])) {
        $_SESSION['error'] = "Invalid job type!";
        header("Location: ../public/post_job.php");
        exit();
    }
    
    // Insert job (status defaults to pending)
    $sql = "INSERT INTO jobs (recruiter_id, title, description, skills, salary, type, status) 
            VALUES ($recruiter_id, '$title', '$description', '$skills', '$salary', '$type', 'pending')";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Job posted successfully! Waiting for admin approval.";
        header("Location: ../public/dashboard_recruiter.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to post job. Please try again.";
        header("Location: ../public/post_job.php");
        exit();
    }
} else {
    header("Location: ../public/post_job.php");
    exit();
}
?>

