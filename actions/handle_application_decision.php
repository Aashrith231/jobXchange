<?php
session_start();

// Only recruiters can make decisions on applications
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recruiter') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recruiter_id   = $_SESSION['user_id'];
    $application_id = isset($_POST['application_id']) ? (int) $_POST['application_id'] : 0;
    $job_id         = isset($_POST['job_id']) ? (int) $_POST['job_id'] : 0;
    $decision       = isset($_POST['decision']) ? $_POST['decision'] : '';

    if (!$application_id || !$job_id || !in_array($decision, ['accepted', 'rejected'], true)) {
        $_SESSION['error'] = "Invalid request.";
        header("Location: ../public/dashboard_recruiter.php");
        exit();
    }

    // Verify that this application belongs to a job owned by the current recruiter
    $sql = "SELECT a.application_id
            FROM applications a
            JOIN jobs j ON a.job_id = j.job_id
            WHERE a.application_id = $application_id
              AND a.job_id = $job_id
              AND j.recruiter_id = $recruiter_id";

    $result = mysqli_query($conn, $sql);

    if (!$result || mysqli_num_rows($result) === 0) {
        $_SESSION['error'] = "You are not authorized to update this application.";
        header("Location: ../public/dashboard_recruiter.php");
        exit();
    }

    // Update application status
    $update_sql = "UPDATE applications 
                   SET status = '" . mysqli_real_escape_string($conn, $decision) . "'
                   WHERE application_id = $application_id";

    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "Application has been " . ($decision === 'accepted' ? 'accepted' : 'rejected') . ".";
    } else {
        $_SESSION['error'] = "Failed to update application status. Please try again.";
    }

    header("Location: ../public/view_applicants.php?job_id=" . $job_id);
    exit();
}

// Fallback redirect
header("Location: ../public/dashboard_recruiter.php");
exit();


