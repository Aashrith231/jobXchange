<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    
    // Prevent deleting admin users
    $check_sql = "SELECT role FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if ($user['role'] == 'admin') {
            $_SESSION['error'] = "Cannot delete admin users!";
            header("Location: ../public/dashboard_admin.php");
            exit();
        }
        
        // Delete user (cascading will delete related jobs and applications)
        $sql = "DELETE FROM users WHERE user_id = $user_id";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "User deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete user!";
        }
    } else {
        $_SESSION['error'] = "User not found!";
    }
    
    header("Location: ../public/dashboard_admin.php");
    exit();
} else {
    header("Location: ../public/dashboard_admin.php");
    exit();
}
?>

