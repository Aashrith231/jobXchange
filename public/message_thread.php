<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

if (!isset($_GET['application_id'])) {
    header("Location: messages.php");
    exit();
}

$application_id = mysqli_real_escape_string($conn, $_GET['application_id']);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Verify user has access to this conversation
$verify_sql = "SELECT a.*, j.title, j.recruiter_id, j.job_id,
               u1.name as candidate_name, u1.user_id as candidate_id,
               u2.name as recruiter_name
               FROM applications a
               JOIN jobs j ON a.job_id = j.job_id
               JOIN users u1 ON a.user_id = u1.user_id
               JOIN users u2 ON j.recruiter_id = u2.user_id
               WHERE a.application_id = $application_id";

$verify_result = mysqli_query($conn, $verify_sql);

if (mysqli_num_rows($verify_result) == 0) {
    header("Location: messages.php");
    exit();
}

$app_data = mysqli_fetch_assoc($verify_result);

// Check if user is part of this conversation
if ($role == 'recruiter' && $app_data['recruiter_id'] != $user_id) {
    header("Location: messages.php");
    exit();
}

if ($role == 'candidate' && $app_data['candidate_id'] != $user_id) {
    header("Location: messages.php");
    exit();
}

// Mark messages as read
$mark_read = "UPDATE messages SET is_read = 1 
              WHERE application_id = $application_id 
              AND receiver_id = $user_id";
mysqli_query($conn, $mark_read);

// Fetch all messages
$messages_sql = "SELECT m.*, u.name as sender_name 
                 FROM messages m
                 JOIN users u ON m.sender_id = u.user_id
                 WHERE m.application_id = $application_id
                 ORDER BY m.created_at ASC";
$messages = mysqli_query($conn, $messages_sql);

include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="container">
        <div class="page-header">
            <div>
                <h2>Conversation: <?php echo htmlspecialchars($app_data['title']); ?></h2>
                <p style="color: white; margin-top: 0.5rem;">
                    <?php 
                    if ($role == 'recruiter') {
                        echo "With: " . htmlspecialchars($app_data['candidate_name']);
                    } else {
                        echo "With: " . htmlspecialchars($app_data['recruiter_name']);
                    }
                    ?>
                </p>
            </div>
            <div>
                <a href="messages.php" class="btn btn-secondary">Back to Messages</a>
                <?php if ($role == 'recruiter'): ?>
                    <a href="dashboard_recruiter.php" class="btn btn-secondary">Dashboard</a>
                <?php else: ?>
                    <a href="dashboard_candidate.php" class="btn btn-secondary">Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="message-container">
            <!-- Messages Thread -->
            <div class="messages-thread" id="messagesThread">
                <?php if (mysqli_num_rows($messages) > 0): ?>
                    <?php while($msg = mysqli_fetch_assoc($messages)): ?>
                        <div class="message-bubble <?php echo ($msg['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                            <div class="message-header">
                                <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                                <span class="message-time"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div class="message-text">
                                <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-data">No messages yet. Start the conversation!</p>
                <?php endif; ?>
            </div>
            
            <!-- Send Message Form -->
            <div class="message-input-box">
                <form action="../actions/handle_send_message.php" method="POST" class="message-form">
                    <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
                    <input type="hidden" name="job_id" value="<?php echo $app_data['job_id']; ?>">
                    <input type="hidden" name="receiver_id" value="<?php echo ($role == 'recruiter') ? $app_data['candidate_id'] : $app_data['recruiter_id']; ?>">
                    
                    <textarea name="message_text" 
                              id="messageInput" 
                              placeholder="Type your message..." 
                              rows="3" 
                              required></textarea>
                    
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom of messages
window.onload = function() {
    var thread = document.getElementById('messagesThread');
    thread.scrollTop = thread.scrollHeight;
};

// Focus on message input
document.getElementById('messageInput').focus();
</script>

<?php include '../includes/footer.php'; ?>

