<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$exchange_id = isset($_POST['exchange_id']) ? (int)$_POST['exchange_id'] : 0;
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';

// Verify that this exchange post belongs to the current user
$verify_sql = "SELECT user_id FROM skill_exchange WHERE exchange_id = $exchange_id AND user_id = $user_id";
$verify_result = mysqli_query($conn, $verify_sql);

if (mysqli_num_rows($verify_result) == 0) {
    $_SESSION['error'] = "Invalid skill exchange request!";
    header("Location: ../public/skill_exchange.php");
    exit();
}

// Validate status
if (!in_array($status, ['open', 'closed', 'in_progress'])) {
    $_SESSION['error'] = "Invalid status!";
    header("Location: ../public/skill_exchange.php");
    exit();
}

// Update status
$update_sql = "UPDATE skill_exchange SET status = '$status' WHERE exchange_id = $exchange_id";

if (mysqli_query($conn, $update_sql)) {
    $_SESSION['success'] = "Request status updated successfully!";
} else {
    $_SESSION['error'] = "Error updating request status. Please try again.";
}

header("Location: ../public/skill_exchange.php");
exit();
?>

