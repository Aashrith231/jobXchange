<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch all conversations for this user
if ($role == 'recruiter') {
    $sql = "SELECT DISTINCT 
            a.application_id,
            a.user_id as candidate_id,
            u.name as candidate_name,
            j.title as job_title,
            j.job_id,
            (SELECT COUNT(*) FROM messages 
             WHERE application_id = a.application_id 
             AND receiver_id = $user_id 
             AND is_read = 0) as unread_count,
            (SELECT created_at FROM messages 
             WHERE application_id = a.application_id 
             ORDER BY created_at DESC LIMIT 1) as last_message_time
            FROM applications a
            JOIN users u ON a.user_id = u.user_id
            JOIN jobs j ON a.job_id = j.job_id
            WHERE j.recruiter_id = $user_id 
            AND a.status = 'accepted'
            AND EXISTS (SELECT 1 FROM messages WHERE application_id = a.application_id)
            ORDER BY last_message_time DESC";
} else {
    $sql = "SELECT DISTINCT 
            a.application_id,
            j.recruiter_id,
            u.name as recruiter_name,
            j.title as job_title,
            j.job_id,
            (SELECT COUNT(*) FROM messages 
             WHERE application_id = a.application_id 
             AND receiver_id = $user_id 
             AND is_read = 0) as unread_count,
            (SELECT created_at FROM messages 
             WHERE application_id = a.application_id 
             ORDER BY created_at DESC LIMIT 1) as last_message_time
            FROM applications a
            JOIN jobs j ON a.job_id = j.job_id
            JOIN users u ON j.recruiter_id = u.user_id
            WHERE a.user_id = $user_id 
            AND a.status = 'accepted'
            AND EXISTS (SELECT 1 FROM messages WHERE application_id = a.application_id)
            ORDER BY last_message_time DESC";
}

$conversations = mysqli_query($conn, $sql);
?>

<div class="dashboard-container">
    <div class="container">
        <h2>My Messages</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="section">
            <?php if (mysqli_num_rows($conversations) > 0): ?>
                <div class="conversations-list">
                    <?php while($conv = mysqli_fetch_assoc($conversations)): ?>
                        <div class="conversation-card">
                            <div class="conversation-header">
                                <h4>
                                    <?php 
                                    if ($role == 'recruiter') {
                                        echo htmlspecialchars($conv['candidate_name']);
                                    } else {
                                        echo htmlspecialchars($conv['recruiter_name']);
                                    }
                                    ?>
                                </h4>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="badge badge-primary"><?php echo $conv['unread_count']; ?> new</span>
                                <?php endif; ?>
                            </div>
                            <p class="conversation-job">Job: <?php echo htmlspecialchars($conv['job_title']); ?></p>
                            <?php if ($conv['last_message_time']): ?>
                                <p class="conversation-time">
                                    <small>Last message: <?php echo date('M d, Y H:i', strtotime($conv['last_message_time'])); ?></small>
                                </p>
                            <?php endif; ?>
                            <a href="message_thread.php?application_id=<?php echo $conv['application_id']; ?>" class="btn btn-primary btn-sm">
                                View Conversation
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No messages yet. 
                    <?php if ($role == 'candidate'): ?>
                        You can start a conversation after your application is accepted.
                    <?php else: ?>
                        You can message candidates after accepting their applications.
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

