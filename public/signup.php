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
        <h2>Sign Up</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="../actions/handle_signup.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="role">Register As</label>
                <select id="role" name="role" required onchange="toggleSkillsField()">
                    <option value="">Select Role</option>
                    <option value="candidate">Candidate</option>
                    <option value="recruiter">Recruiter</option>
                </select>
            </div>
            
            <div class="form-group" id="skills-field" style="display: none;">
                <label for="skills">Your Skills <span style="color: #999;">(Required for candidates)</span></label>
                <input type="text" id="skills" name="skills" placeholder="e.g., HTML, CSS, JavaScript, PHP, MySQL">
                <small style="color: #666; display: block; margin-top: 0.3rem;">
                    Enter your skills separated by commas. This helps match you with relevant jobs!
                </small>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </form>
        
        <script>
        function toggleSkillsField() {
            var role = document.getElementById('role').value;
            var skillsField = document.getElementById('skills-field');
            var skillsInput = document.getElementById('skills');
            
            if (role === 'candidate') {
                skillsField.style.display = 'block';
                skillsInput.required = true;
            } else {
                skillsField.style.display = 'none';
                skillsInput.required = false;
                skillsInput.value = '';
            }
        }
        </script>
        
        <p class="auth-switch">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

