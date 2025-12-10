<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: ../public/login.php");
        exit();
    }
    
    // Check user credentials
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../public/dashboard_admin.php");
                    break;
                case 'recruiter':
                    header("Location: ../public/dashboard_recruiter.php");
                    break;
                case 'candidate':
                    header("Location: ../public/dashboard_candidate.php");
                    break;
                default:
                    header("Location: ../public/login.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password!";
            header("Location: ../public/login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid email or password!";
        header("Location: ../public/login.php");
        exit();
    }
} else {
    header("Location: ../public/login.php");
    exit();
}
?>

