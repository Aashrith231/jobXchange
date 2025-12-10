<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'candidate') {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

if (!isset($_GET['job_id'])) {
    header("Location: dashboard_candidate.php");
    exit();
}

$job_id = mysqli_real_escape_string($conn, $_GET['job_id']);
$user_id = $_SESSION['user_id'];

// Check if job exists and is approved
$job_sql = "SELECT j.*, u.name as recruiter_name 
            FROM jobs j 
            JOIN users u ON j.recruiter_id = u.user_id 
            WHERE j.job_id = $job_id AND j.status = 'approved'";
$job_result = mysqli_query($conn, $job_sql);

if (mysqli_num_rows($job_result) == 0) {
    $_SESSION['error'] = "Job not found!";
    header("Location: dashboard_candidate.php");
    exit();
}

$job = mysqli_fetch_assoc($job_result);

// Check if already applied
$check_sql = "SELECT * FROM applications WHERE job_id = $job_id AND user_id = $user_id";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    $_SESSION['error'] = "You have already applied for this job!";
    header("Location: dashboard_candidate.php");
    exit();
}

include '../includes/header.php';
?>

<div class="form-container">
    <div class="container">
        <div class="form-box">
            <h2>Apply for Job</h2>
            
            <div class="job-info-card">
                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                <p><strong>Company/Recruiter:</strong> <?php echo htmlspecialchars($job['recruiter_name']); ?></p>
                <p><strong>Type:</strong> <?php echo ucfirst($job['type']); ?></p>
                <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                <p><strong>Skills Required:</strong> <?php echo htmlspecialchars($job['skills']); ?></p>
            </div>
            
            <form action="../actions/handle_apply.php" method="POST" class="application-form">
                <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                
                <div class="form-group">
                    <label for="message">Cover Letter / Message</label>
                    <textarea id="message" name="message" rows="8" placeholder="Tell the recruiter why you're a great fit for this position..." required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                    <a href="dashboard_candidate.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

