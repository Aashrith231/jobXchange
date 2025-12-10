<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';

// Fetch pending jobs
$pending_jobs_sql = "SELECT j.*, u.name as recruiter_name, u.email as recruiter_email 
                     FROM jobs j 
                     JOIN users u ON j.recruiter_id = u.user_id 
                     WHERE j.status = 'pending' 
                     ORDER BY j.created_at DESC";
$pending_jobs = mysqli_query($conn, $pending_jobs_sql);

// Fetch all jobs
$all_jobs_sql = "SELECT j.*, u.name as recruiter_name 
                 FROM jobs j 
                 JOIN users u ON j.recruiter_id = u.user_id 
                 ORDER BY j.created_at DESC";
$all_jobs = mysqli_query($conn, $all_jobs_sql);

// Fetch all users (excluding admin)
$users_sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
$all_users = mysqli_query($conn, $users_sql);

// Get statistics
$total_users_sql = "SELECT COUNT(*) as count FROM users WHERE role != 'admin'";
$total_users = mysqli_fetch_assoc(mysqli_query($conn, $total_users_sql))['count'];

$total_jobs_sql = "SELECT COUNT(*) as count FROM jobs";
$total_jobs = mysqli_fetch_assoc(mysqli_query($conn, $total_jobs_sql))['count'];

$pending_jobs_count_sql = "SELECT COUNT(*) as count FROM jobs WHERE status = 'pending'";
$pending_jobs_count = mysqli_fetch_assoc(mysqli_query($conn, $pending_jobs_count_sql))['count'];

$total_applications_sql = "SELECT COUNT(*) as count FROM applications";
$total_applications = mysqli_fetch_assoc(mysqli_query($conn, $total_applications_sql))['count'];

// Check if skill_exchange table exists before counting
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'skill_exchange'");
$total_exchanges = 0;
if (mysqli_num_rows($table_check) > 0) {
    $total_exchanges_sql = "SELECT COUNT(*) as count FROM skill_exchange";
    $total_exchanges = mysqli_fetch_assoc(mysqli_query($conn, $total_exchanges_sql))['count'];
}
?>

<div class="dashboard-container">
    <div class="container">
        <h2>Admin Dashboard</h2>
        
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
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_jobs; ?></h3>
                <p>Total Jobs</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $pending_jobs_count; ?></h3>
                <p>Pending Approvals</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_applications; ?></h3>
                <p>Total Applications</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_exchanges; ?></h3>
                <p>Skill Exchanges</p>
            </div>
        </div>
        
        <!-- Pending Jobs Section -->
        <div class="section">
            <h3>Pending Job Approvals</h3>
            
            <?php if (mysqli_num_rows($pending_jobs) > 0): ?>
                <div class="jobs-grid">
                    <?php while($job = mysqli_fetch_assoc($pending_jobs)): ?>
                        <div class="job-card">
                            <div class="job-header">
                                <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                                <span class="badge badge-pending">Pending</span>
                            </div>
                            <p class="job-recruiter">Posted by: <?php echo htmlspecialchars($job['recruiter_name']); ?> (<?php echo htmlspecialchars($job['recruiter_email']); ?>)</p>
                            <p class="job-type"><?php echo ucfirst($job['type']); ?></p>
                            <p class="job-description"><?php echo htmlspecialchars($job['description']); ?></p>
                            <p class="job-skills"><strong>Skills:</strong> <?php echo htmlspecialchars($job['skills']); ?></p>
                            <p class="job-salary"><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                            <p class="job-date"><small>Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?></small></p>
                            
                            <div class="job-actions">
                                <form action="../actions/handle_approve.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                
                                <form action="../actions/handle_approve.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No pending jobs to review.</p>
            <?php endif; ?>
        </div>
        
        <!-- All Jobs Section -->
        <div class="section">
            <h3>All Jobs</h3>
            
            <?php 
            mysqli_data_seek($all_jobs, 0); // Reset pointer
            if (mysqli_num_rows($all_jobs) > 0): 
            ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Recruiter</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Posted Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($job = mysqli_fetch_assoc($all_jobs)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job['title']); ?></td>
                                    <td><?php echo htmlspecialchars($job['recruiter_name']); ?></td>
                                    <td><?php echo ucfirst($job['type']); ?></td>
                                    <td><span class="badge badge-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                    <td>
                                        <form action="../actions/handle_delete_job.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this job?');">
                                            <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data">No jobs available.</p>
            <?php endif; ?>
        </div>
        
        <!-- All Users Section -->
        <div class="section">
            <h3>All Users</h3>
            
            <?php if (mysqli_num_rows($all_users) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registered Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = mysqli_fetch_assoc($all_users)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <form action="../actions/handle_delete_user.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data">No users registered yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

