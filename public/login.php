<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_" . $_SESSION['role'] . ".php");
    exit();
}
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Login</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="../actions/handle_login.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <p class="auth-switch">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

