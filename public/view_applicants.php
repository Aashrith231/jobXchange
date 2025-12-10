<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'recruiter') {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';
include '../includes/skill_match.php';

if (!isset($_GET['job_id'])) {
    header("Location: dashboard_recruiter.php");
    exit();
}

$job_id = mysqli_real_escape_string($conn, $_GET['job_id']);
$recruiter_id = $_SESSION['user_id'];

// Verify job belongs to recruiter
$job_sql = "SELECT * FROM jobs WHERE job_id = $job_id AND recruiter_id = $recruiter_id";
$job_result = mysqli_query($conn, $job_sql);

if (mysqli_num_rows($job_result) == 0) {
    header("Location: dashboard_recruiter.php");
    exit();
}

$job = mysqli_fetch_assoc($job_result);
$job_skills = $job['skills'];

// Fetch applications with candidate skills
$sql = "SELECT a.*, u.name, u.email, u.skills 
        FROM applications a 
        JOIN users u ON a.user_id = u.user_id 
        WHERE a.job_id = $job_id";
$applications = mysqli_query($conn, $sql);

// Calculate match scores and store in array for sorting
$applicants_with_scores = [];
while($app = mysqli_fetch_assoc($applications)) {
    $match_data = calculateSkillMatch($app['skills'], $job_skills);
    $app['match_score'] = $match_data['percentage'];
    $app['match_data'] = $match_data;
    $applicants_with_scores[] = $app;
}

// Sort by match score (highest first)
usort($applicants_with_scores, function($a, $b) {
    return $b['match_score'] - $a['match_score'];
});
?>

<div class="dashboard-container">
    <div class="container">
        <div class="page-header">
            <h2>Applicants for: <?php echo htmlspecialchars($job['title']); ?></h2>
            <a href="dashboard_recruiter.php" class="btn btn-secondary">Back to Dashboard</a>
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
        
        <?php if (count($applicants_with_scores) > 0): ?>
            <div class="section" style="background: #f8f9fa; padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
                <p style="margin: 0; color: #666;">
                    <strong>💡 Tip:</strong> Applicants are sorted by skill match score (highest first). 
                    The match score shows how well each candidate's skills align with your job requirements.
                </p>
            </div>
            
            <div class="applicants-list">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Match Score</th>
                            <th>Candidate Name</th>
                            <th>Email</th>
                            <th>Candidate Skills</th>
                            <th>Message</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($applicants_with_scores as $app): ?>
                            <tr>
                                <td>
                                    <?php echo displaySkillMatchBadge($app['match_data']); ?>
                                    <div style="margin-top: 0.3rem;">
                                        <small style="color: #666;">
                                            <?php echo $app['match_data']['matched']; ?>/<?php echo $app['match_data']['total']; ?> skills
                                        </small>
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($app['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                <td>
                                    <small style="color: #666;">
                                        <?php echo !empty($app['skills']) ? htmlspecialchars($app['skills']) : '<em>No skills listed</em>'; ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars(substr($app['message'], 0, 100)) . (strlen($app['message']) > 100 ? '...' : ''); ?></td>
                                <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $app['status']; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($app['status'] === 'accepted'): ?>
                                        <button class="btn btn-success btn-sm" disabled>Accepted</button>
                                        <a href="message_thread.php?application_id=<?php echo $app['application_id']; ?>" class="btn btn-primary btn-sm" style="margin-left: 4px;">
                                            💬 Message
                                        </a>
                                    <?php elseif ($app['status'] === 'rejected'): ?>
                                        <button class="btn btn-danger btn-sm" disabled>Rejected</button>
                                    <?php else: ?>
                                        <form action="../actions/handle_application_decision.php" method="POST" style="display:inline-block;">
                                            <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                            <input type="hidden" name="decision" value="accepted">
                                            <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                        </form>
                                        <form action="../actions/handle_application_decision.php" method="POST" style="display:inline-block; margin-left:4px;">
                                            <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                            <input type="hidden" name="decision" value="rejected">
                                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-data">No applications received yet for this job.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

