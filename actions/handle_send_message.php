<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = mysqli_real_escape_string($conn, $_POST['receiver_id']);
    $application_id = mysqli_real_escape_string($conn, $_POST['application_id']);
    $job_id = mysqli_real_escape_string($conn, $_POST['job_id']);
    $message_text = mysqli_real_escape_string($conn, trim($_POST['message_text']));
    
    // Validation
    if (empty($message_text)) {
        $_SESSION['error'] = "Message cannot be empty!";
        header("Location: ../public/message_thread.php?application_id=$application_id");
        exit();
    }
    
    // Verify the application exists and user has access
    $verify_sql = "SELECT a.*, j.recruiter_id 
                   FROM applications a 
                   JOIN jobs j ON a.job_id = j.job_id 
                   WHERE a.application_id = $application_id";
    $verify_result = mysqli_query($conn, $verify_sql);
    
    if (mysqli_num_rows($verify_result) == 0) {
        $_SESSION['error'] = "Invalid conversation!";
        header("Location: ../public/messages.php");
        exit();
    }
    
    $app = mysqli_fetch_assoc($verify_result);
    
    // Check user is part of this conversation
    $role = $_SESSION['role'];
    if ($role == 'recruiter' && $app['recruiter_id'] != $sender_id) {
        $_SESSION['error'] = "Access denied!";
        header("Location: ../public/messages.php");
        exit();
    }
    
    if ($role == 'candidate' && $app['user_id'] != $sender_id) {
        $_SESSION['error'] = "Access denied!";
        header("Location: ../public/messages.php");
        exit();
    }
    
    // Insert message
    $sql = "INSERT INTO messages (sender_id, receiver_id, job_id, application_id, message_text, is_read) 
            VALUES ($sender_id, $receiver_id, $job_id, $application_id, '$message_text', 0)";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../public/message_thread.php?application_id=$application_id");
        exit();
    } else {
        $_SESSION['error'] = "Failed to send message. Please try again.";
        header("Location: ../public/message_thread.php?application_id=$application_id");
        exit();
    }
} else {
    header("Location: ../public/messages.php");
    exit();
}
?>

