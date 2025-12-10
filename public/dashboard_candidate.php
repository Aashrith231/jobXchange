<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'candidate') {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';
include '../includes/skill_match.php';

$user_id = $_SESSION['user_id'];

// Get candidate's skills
$user_skills_sql = "SELECT skills FROM users WHERE user_id = $user_id";
$user_skills_result = mysqli_query($conn, $user_skills_sql);
$candidate = mysqli_fetch_assoc($user_skills_result);
$candidate_skills = $candidate['skills'] ?? '';

// Fetch approved jobs
$sql = "SELECT j.*, u.name as recruiter_name 
        FROM jobs j 
        JOIN users u ON j.recruiter_id = u.user_id 
        WHERE j.status = 'approved' 
        ORDER BY j.created_at DESC";
$jobs_result = mysqli_query($conn, $sql);

// Fetch user's applications
$my_apps_sql = "SELECT a.*, j.title, j.type, u.name as recruiter_name 
                FROM applications a 
                JOIN jobs j ON a.job_id = j.job_id 
                JOIN users u ON j.recruiter_id = u.user_id 
                WHERE a.user_id = $user_id 
                ORDER BY a.applied_at DESC";
$my_apps_result = mysqli_query($conn, $my_apps_sql);
?>

<div class="dashboard-container">
    <div class="container">
        <h2>Candidate Dashboard</h2>
        
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
        
        <!-- Quick Actions -->
        <div class="section">
            <h3>Quick Actions</h3>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="update_skills.php" class="btn btn-primary">📝 Update My Skills</a>
                <a href="skill_exchange.php" class="btn btn-secondary">🔄 Skill Exchange</a>
                <a href="view_exchange.php" class="btn btn-secondary">Browse Exchange Requests</a>
                <a href="messages.php" class="btn btn-secondary">💬 Messages</a>
            </div>
            <?php if (!empty($candidate_skills)): ?>
            <div style="margin-top: 1rem; padding: 1rem; background: #f0f9ff; border-radius: 8px;">
                <strong>Your Skills:</strong> 
                <span style="color: #666;"><?php echo htmlspecialchars($candidate_skills); ?></span>
            </div>
            <?php else: ?>
            <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                <strong>⚠️ No skills added yet!</strong> 
                <a href="update_skills.php" style="color: #667eea; text-decoration: underline;">Add your skills</a> to see job match scores and get better opportunities.
            </div>
            <?php endif; ?>
        </div>
        
        <!-- My Applications Section -->
        <div class="section">
            <h3>My Applications</h3>
            <?php if (mysqli_num_rows($my_apps_result) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Type</th>
                                <th>Recruiter</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($app = mysqli_fetch_assoc($my_apps_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['title']); ?></td>
                                    <td><?php echo ucfirst($app['type']); ?></td>
                                    <td><?php echo htmlspecialchars($app['recruiter_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                    <td><span class="badge badge-<?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                                    <td>
                                        <?php if ($app['status'] == 'accepted'): ?>
                                            <a href="message_thread.php?application_id=<?php echo $app['application_id']; ?>" class="btn btn-primary btn-sm">
                                                💬 Message Recruiter
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data">You haven't applied to any jobs yet.</p>
            <?php endif; ?>
        </div>
        
        <!-- Available Jobs Section -->
        <div class="section">
            <h3>Available Jobs</h3>
            
            <?php if (mysqli_num_rows($jobs_result) > 0): ?>
                <div class="jobs-grid">
                    <?php while($job = mysqli_fetch_assoc($jobs_result)): ?>
                        <?php
                        // Check if already applied
                        $job_id = $job['job_id'];
                        $check_sql = "SELECT * FROM applications WHERE job_id = $job_id AND user_id = $user_id";
                        $check_result = mysqli_query($conn, $check_sql);
                        $already_applied = mysqli_num_rows($check_result) > 0;
                        
                        // Calculate skill match
                        $match_data = calculateSkillMatch($candidate_skills, $job['skills']);
                        ?>
                        
                        <div class="job-card">
                            <div class="job-header">
                                <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                                <div class="job-badges">
                                    <?php echo displaySkillMatchBadge($match_data); ?>
                                    <span class="badge badge-success">Approved</span>
                                </div>
                            </div>
                            <p class="job-recruiter">Posted by: <?php echo htmlspecialchars($job['recruiter_name']); ?></p>
                            <p class="job-type"><?php echo ucfirst($job['type']); ?></p>
                            <?php if ($match_data['matched'] > 0): ?>
                                <p class="job-match-detail">
                                    <small style="color: #28a745;">
                                        ✓ You have <?php echo $match_data['matched']; ?> of <?php echo $match_data['total']; ?> required skills
                                    </small>
                                </p>
                            <?php endif; ?>
                            <p class="job-description"><?php echo htmlspecialchars($job['description']); ?></p>
                            <p class="job-skills"><strong>Skills:</strong> <?php echo htmlspecialchars($job['skills']); ?></p>
                            <p class="job-salary"><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                            <p class="job-date"><small>Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?></small></p>
                            
                            <div class="job-actions">
                                <?php if ($already_applied): ?>
                                    <button class="btn btn-disabled" disabled>Already Applied</button>
                                <?php else: ?>
                                    <a href="apply_job.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-primary">Apply Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No jobs available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

