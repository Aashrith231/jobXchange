<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'recruiter') {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';

$recruiter_id = $_SESSION['user_id'];

// Fetch recruiter's jobs
$sql = "SELECT * FROM jobs WHERE recruiter_id = $recruiter_id ORDER BY created_at DESC";
$jobs_result = mysqli_query($conn, $sql);
?>

<div class="dashboard-container">
    <div class="container">
        <div class="dashboard-header">
            <h2>Recruiter Dashboard</h2>
            <a href="post_job.php" class="btn btn-primary">Post New Job</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="section">
            <h3>Quick Actions</h3>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="skill_exchange.php" class="btn btn-primary">🔄 Skill Exchange</a>
                <a href="view_exchange.php" class="btn btn-secondary">Browse Exchange Requests</a>
                <a href="messages.php" class="btn btn-secondary">💬 Messages</a>
            </div>
        </div>
        
        <div class="jobs-section">
            <h3>My Posted Jobs</h3>
            
            <?php if (mysqli_num_rows($jobs_result) > 0): ?>
                <div class="jobs-grid">
                    <?php while($job = mysqli_fetch_assoc($jobs_result)): ?>
                        <div class="job-card">
                            <div class="job-header">
                                <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                                <span class="badge badge-<?php echo $job['status']; ?>">
                                    <?php echo ucfirst($job['status']); ?>
                                </span>
                            </div>
                            <p class="job-type"><?php echo ucfirst($job['type']); ?></p>
                            <p class="job-description"><?php echo htmlspecialchars(substr($job['description'], 0, 150)); ?>...</p>
                            <p class="job-skills"><strong>Skills:</strong> <?php echo htmlspecialchars($job['skills']); ?></p>
                            <p class="job-salary"><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                            <p class="job-date"><small>Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?></small></p>
                            
                            <?php
                            // Count applications
                            $job_id = $job['job_id'];
                            $app_sql = "SELECT COUNT(*) as count FROM applications WHERE job_id = $job_id";
                            $app_result = mysqli_query($conn, $app_sql);
                            $app_count = mysqli_fetch_assoc($app_result)['count'];
                            ?>
                            
                            <div class="job-actions">
                                <a href="view_applicants.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-secondary btn-sm">
                                    View Applicants (<?php echo $app_count; ?>)
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">You haven't posted any jobs yet. <a href="post_job.php">Post your first job</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

