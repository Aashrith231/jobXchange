<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'recruiter') {
    header("Location: login.php");
    exit();
}
include '../includes/header.php';
?>

<div class="form-container">
    <div class="container">
        <div class="form-box">
            <h2>Post a New Job</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="../actions/handle_post_job.php" method="POST" class="job-form">
                <div class="form-group">
                    <label for="title">Job Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Job Description</label>
                    <textarea id="description" name="description" rows="6" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="skills">Required Skills</label>
                    <input type="text" id="skills" name="skills" placeholder="e.g., PHP, MySQL, JavaScript" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="salary">Salary</label>
                        <input type="text" id="salary" name="salary" placeholder="e.g., $50,000 - $70,000" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Job Type</label>
                        <select id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="internship">Internship</option>
                            <option value="full-time">Full-Time</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Post Job</button>
                    <a href="dashboard_recruiter.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

