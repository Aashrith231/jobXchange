<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$exchange_id = isset($_GET['exchange_id']) ? (int)$_GET['exchange_id'] : 0;

// Verify that this exchange post belongs to the current user
$verify_sql = "SELECT se.*, u.name as poster_name 
               FROM skill_exchange se 
               JOIN users u ON se.user_id = u.user_id 
               WHERE se.exchange_id = $exchange_id AND se.user_id = $user_id";
$verify_result = mysqli_query($conn, $verify_sql);

if (mysqli_num_rows($verify_result) == 0) {
    $_SESSION['error'] = "Invalid skill exchange request!";
    header("Location: skill_exchange.php");
    exit();
}

$exchange = mysqli_fetch_assoc($verify_result);

// Handle response status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $response_id = (int)$_POST['response_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_sql = "UPDATE skill_exchange_responses 
                   SET status = '$status' 
                   WHERE response_id = $response_id";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "Response status updated successfully!";
        
        // If accepting a response, update exchange status to in_progress
        if ($status == 'accepted') {
            $update_exchange_sql = "UPDATE skill_exchange SET status = 'in_progress' WHERE exchange_id = $exchange_id";
            mysqli_query($conn, $update_exchange_sql);
        }
    } else {
        $_SESSION['error'] = "Error updating response status.";
    }
    
    header("Location: view_exchange_responses.php?exchange_id=$exchange_id");
    exit();
}

// Fetch all responses
$responses_sql = "SELECT ser.*, u.name as responder_name, u.email as responder_email, u.role as responder_role
                  FROM skill_exchange_responses ser
                  JOIN users u ON ser.responder_id = u.user_id
                  WHERE ser.exchange_id = $exchange_id
                  ORDER BY ser.created_at DESC";
$responses_result = mysqli_query($conn, $responses_sql);
?>

<div class="dashboard-container">
    <div class="container">
        <div class="page-header">
            <h2>Responses to Your Skill Exchange Request</h2>
            <a href="skill_exchange.php" class="btn btn-secondary">Back to My Requests</a>
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
        
        <!-- Exchange Request Details -->
        <div class="section">
            <h3>Your Request Details</h3>
            <div class="exchange-card">
                <div class="exchange-header">
                    <span class="badge badge-<?php echo $exchange['status']; ?>"><?php echo ucfirst($exchange['status']); ?></span>
                </div>
                
                <div class="exchange-skills">
                    <div class="skill-offer">
                        <span class="skill-label">🎓 Offering:</span>
                        <span class="skill-value"><?php echo htmlspecialchars($exchange['offer_skill']); ?></span>
                    </div>
                    <div class="skill-arrow">⇄</div>
                    <div class="skill-request">
                        <span class="skill-label">🎯 Seeking:</span>
                        <span class="skill-value"><?php echo htmlspecialchars($exchange['request_skill']); ?></span>
                    </div>
                </div>
                
                <p class="exchange-description"><?php echo nl2br(htmlspecialchars($exchange['description'])); ?></p>
                
                <div class="exchange-footer">
                    <span class="exchange-date">Posted: <?php echo date('M d, Y', strtotime($exchange['created_at'])); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Responses Section -->
        <div class="section">
            <h3>Responses (<?php echo mysqli_num_rows($responses_result); ?>)</h3>
            
            <?php if (mysqli_num_rows($responses_result) > 0): ?>
                <div class="responses-list">
                    <?php while($response = mysqli_fetch_assoc($responses_result)): ?>
                        <div class="response-card">
                            <div class="response-header">
                                <div>
                                    <h4><?php echo htmlspecialchars($response['responder_name']); ?></h4>
                                    <span class="badge badge-<?php echo $response['responder_role']; ?>">
                                        <?php echo ucfirst($response['responder_role']); ?>
                                    </span>
                                    <span class="badge badge-<?php echo $response['status'] == 'pending' ? 'pending' : ($response['status'] == 'accepted' ? 'success' : 'rejected'); ?>">
                                        <?php echo ucfirst($response['status']); ?>
                                    </span>
                                </div>
                                <span class="response-date"><?php echo date('M d, Y H:i', strtotime($response['created_at'])); ?></span>
                            </div>
                            
                            <p class="response-email">Contact: <?php echo htmlspecialchars($response['responder_email']); ?></p>
                            
                            <div class="response-message">
                                <p><?php echo nl2br(htmlspecialchars($response['message'])); ?></p>
                            </div>
                            
                            <?php if ($exchange['status'] == 'open' || $exchange['status'] == 'in_progress'): ?>
                                <div class="response-actions">
                                    <?php if ($response['status'] == 'pending'): ?>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="response_id" value="<?php echo $response['response_id']; ?>">
                                            <input type="hidden" name="status" value="accepted">
                                            <button type="submit" name="update_status" class="btn btn-success btn-sm">Accept</button>
                                        </form>
                                        
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="response_id" value="<?php echo $response['response_id']; ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" name="update_status" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    <?php elseif ($response['status'] == 'accepted'): ?>
                                        <p class="status-text">✓ You accepted this response. You can contact them at: <?php echo htmlspecialchars($response['responder_email']); ?></p>
                                    <?php else: ?>
                                        <p class="status-text text-muted">✗ Rejected</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No responses yet. Share your request with others to get responses!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

