<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'candidate') {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Get current skills
$sql = "SELECT skills FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
$current_skills = $user['skills'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $skills = mysqli_real_escape_string($conn, trim($_POST['skills']));
    
    if (empty($skills)) {
        $_SESSION['error'] = "Skills cannot be empty!";
    } else {
        $update_sql = "UPDATE users SET skills = '$skills' WHERE user_id = $user_id";
        
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['success'] = "Skills updated successfully!";
            header("Location: dashboard_candidate.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating skills. Please try again.";
        }
    }
}
?>

<div class="dashboard-container">
    <div class="container">
        <div class="page-header">
            <h2>Update Your Skills</h2>
            <a href="dashboard_candidate.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="form-box">
            <h3>Your Skills Profile</h3>
            <p style="color: #666; margin-bottom: 1.5rem;">
                Keep your skills up to date to get better job matches! Adding relevant skills helps recruiters find you and shows you the most suitable opportunities.
            </p>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="skills">Your Skills</label>
                    <input type="text" id="skills" name="skills" 
                           value="<?php echo htmlspecialchars($current_skills); ?>" 
                           placeholder="e.g., HTML, CSS, JavaScript, PHP, MySQL, React, Python" 
                           required>
                    <small style="color: #666; display: block; margin-top: 0.3rem;">
                        Enter your skills separated by commas
                    </small>
                </div>
                
                <div class="info-box" style="background: #e3f2fd; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <h4 style="margin: 0 0 0.5rem 0; color: #1976d2;">💡 Tips for Adding Skills:</h4>
                    <ul style="margin: 0; padding-left: 1.5rem; color: #666;">
                        <li>Include programming languages (PHP, JavaScript, Python, etc.)</li>
                        <li>Add frameworks and libraries (React, Laravel, Django, etc.)</li>
                        <li>List tools and technologies (Git, Docker, MySQL, etc.)</li>
                        <li>Include soft skills (Team Leadership, Communication, etc.)</li>
                        <li>Be specific and honest about your abilities</li>
                    </ul>
                </div>
                
                <?php if (!empty($current_skills)): ?>
                <div class="current-skills" style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <h4 style="margin: 0 0 0.5rem 0;">Current Skills:</h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <?php
                        $skills_array = array_map('trim', explode(',', $current_skills));
                        foreach ($skills_array as $skill) {
                            echo '<span style="background: #667eea; color: white; padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem;">';
                            echo htmlspecialchars($skill);
                            echo '</span>';
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Skills</button>
                    <a href="dashboard_candidate.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

