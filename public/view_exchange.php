<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_response'])) {
    $exchange_id = (int)$_POST['exchange_id'];
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    
    if (empty($message)) {
        $_SESSION['error'] = "Message cannot be empty!";
    } else {
        // Check if user is not responding to their own post
        $check_sql = "SELECT user_id FROM skill_exchange WHERE exchange_id = $exchange_id";
        $check_result = mysqli_query($conn, $check_sql);
        $post_owner = mysqli_fetch_assoc($check_result)['user_id'];
        
        if ($post_owner == $user_id) {
            $_SESSION['error'] = "You cannot respond to your own skill exchange request!";
        } else {
            // Check if already responded
            $existing_sql = "SELECT * FROM skill_exchange_responses 
                           WHERE exchange_id = $exchange_id AND responder_id = $user_id";
            $existing_result = mysqli_query($conn, $existing_sql);
            
            if (mysqli_num_rows($existing_result) > 0) {
                $_SESSION['error'] = "You have already responded to this request!";
            } else {
                $sql = "INSERT INTO skill_exchange_responses (exchange_id, responder_id, message) 
                        VALUES ($exchange_id, $user_id, '$message')";
                
                if (mysqli_query($conn, $sql)) {
                    $_SESSION['success'] = "Your response has been sent successfully!";
                } else {
                    $_SESSION['error'] = "Error sending your response. Please try again.";
                }
            }
        }
    }
}

// Fetch all skill exchange posts (excluding user's own posts)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

$sql = "SELECT se.*, u.name as poster_name, u.role as poster_role,
        (SELECT COUNT(*) FROM skill_exchange_responses WHERE exchange_id = se.exchange_id) as response_count
        FROM skill_exchange se 
        JOIN users u ON se.user_id = u.user_id 
        WHERE se.user_id != $user_id";

// Add status filter
if ($filter != 'all') {
    $sql .= " AND se.status = '$filter'";
}

// Add search filter
if (!empty($search)) {
    $sql .= " AND (se.offer_skill LIKE '%$search%' OR se.request_skill LIKE '%$search%' OR se.description LIKE '%$search%')";
}

$sql .= " ORDER BY se.created_at DESC";
$posts_result = mysqli_query($conn, $sql);
?>

<div class="dashboard-container">
    <div class="container">
        <div class="page-header">
            <h2>Browse Skill Exchange Requests</h2>
            <a href="skill_exchange.php" class="btn btn-primary">Post New Request</a>
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
        
        <!-- Search and Filter Section -->
        <div class="section">
            <form method="GET" action="" class="filter-form">
                <div class="filter-row">
                    <div class="form-group" style="flex: 2;">
                        <input type="text" name="search" placeholder="Search skills..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               style="width: 100%;">
                    </div>
                    
                    <div class="form-group">
                        <select name="filter" style="width: 100%;">
                            <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="open" <?php echo $filter == 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo $filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="closed" <?php echo $filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="view_exchange.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
        
        <!-- Skill Exchange Posts -->
        <div class="section">
            <h3>Available Skill Exchange Opportunities</h3>
            
            <?php if (mysqli_num_rows($posts_result) > 0): ?>
                <div class="exchange-grid">
                    <?php while($post = mysqli_fetch_assoc($posts_result)): ?>
                        <?php
                        // Check if current user has already responded
                        $check_response_sql = "SELECT * FROM skill_exchange_responses 
                                              WHERE exchange_id = {$post['exchange_id']} AND responder_id = $user_id";
                        $check_response_result = mysqli_query($conn, $check_response_sql);
                        $already_responded = mysqli_num_rows($check_response_result) > 0;
                        ?>
                        
                        <div class="exchange-card">
                            <div class="exchange-header">
                                <div>
                                    <span class="badge badge-<?php echo $post['poster_role']; ?>"><?php echo ucfirst($post['poster_role']); ?></span>
                                    <span class="badge badge-<?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span>
                                </div>
                            </div>
                            
                            <p class="exchange-poster">Posted by: <?php echo htmlspecialchars($post['poster_name']); ?></p>
                            
                            <div class="exchange-skills">
                                <div class="skill-offer">
                                    <span class="skill-label">🎓 Offering:</span>
                                    <span class="skill-value"><?php echo htmlspecialchars($post['offer_skill']); ?></span>
                                </div>
                                <div class="skill-arrow">⇄</div>
                                <div class="skill-request">
                                    <span class="skill-label">🎯 Seeking:</span>
                                    <span class="skill-value"><?php echo htmlspecialchars($post['request_skill']); ?></span>
                                </div>
                            </div>
                            
                            <p class="exchange-description"><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
                            
                            <div class="exchange-footer">
                                <span class="exchange-date">Posted: <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                                <span class="exchange-responses">
                                    <?php echo $post['response_count']; ?> Response<?php echo $post['response_count'] != 1 ? 's' : ''; ?>
                                </span>
                            </div>
                            
                            <?php if ($post['status'] == 'open' && !$already_responded): ?>
                                <div class="response-form-container" id="response-form-<?php echo $post['exchange_id']; ?>" style="display: none;">
                                    <form method="POST" action="" class="response-form">
                                        <input type="hidden" name="exchange_id" value="<?php echo $post['exchange_id']; ?>">
                                        <div class="form-group">
                                            <label>Your Message</label>
                                            <textarea name="message" rows="4" placeholder="Introduce yourself and explain how you can help with their request..." required></textarea>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" name="submit_response" class="btn btn-success btn-sm">Send Response</button>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleResponseForm(<?php echo $post['exchange_id']; ?>)">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="exchange-actions">
                                    <button onclick="toggleResponseForm(<?php echo $post['exchange_id']; ?>)" class="btn btn-primary btn-sm">
                                        💬 Respond to Request
                                    </button>
                                </div>
                            <?php elseif ($already_responded): ?>
                                <div class="exchange-actions">
                                    <button class="btn btn-disabled btn-sm" disabled>✓ Already Responded</button>
                                </div>
                            <?php else: ?>
                                <div class="exchange-actions">
                                    <button class="btn btn-disabled btn-sm" disabled>Closed</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No skill exchange requests found. Be the first to <a href="skill_exchange.php">post one</a>!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleResponseForm(exchangeId) {
    var form = document.getElementById('response-form-' + exchangeId);
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>

