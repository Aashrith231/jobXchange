<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $skills = isset($_POST['skills']) ? mysqli_real_escape_string($conn, trim($_POST['skills'])) : null;
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: ../public/signup.php");
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: ../public/signup.php");
        exit();
    }
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters!";
        header("Location: ../public/signup.php");
        exit();
    }
    
    if (!in_array($role, ['candidate', 'recruiter'])) {
        $_SESSION['error'] = "Invalid role selected!";
        header("Location: ../public/signup.php");
        exit();
    }
    
    // Validate skills for candidates
    if ($role === 'candidate' && empty($skills)) {
        $_SESSION['error'] = "Skills are required for candidates!";
        header("Location: ../public/signup.php");
        exit();
    }
    
    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Email already registered!";
        header("Location: ../public/signup.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user with skills
    if ($role === 'candidate' && !empty($skills)) {
        $sql = "INSERT INTO users (name, email, password, role, skills) VALUES ('$name', '$email', '$hashed_password', '$role', '$skills')";
    } else {
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";
    }
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: ../public/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed! Please try again.";
        header("Location: ../public/signup.php");
        exit();
    }
} else {
    header("Location: ../public/signup.php");
    exit();
}
?>

