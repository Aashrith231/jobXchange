<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $offer_skill = mysqli_real_escape_string($conn, trim($_POST['offer_skill']));
    $request_skill = mysqli_real_escape_string($conn, trim($_POST['request_skill']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    
    if (empty($offer_skill) || empty($request_skill) || empty($description)) {
        $_SESSION['error'] = "All fields are required!";
    } else {
        $sql = "INSERT INTO skill_exchange (user_id, offer_skill, request_skill, description) 
                VALUES ($user_id, '$offer_skill', '$request_skill', '$description')";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Your skill exchange request has been posted successfully!";
            header("Location: view_exchange.php");
            exit();
        } else {
            $_SESSION['error'] = "Error posting your request. Please try again.";
        }
    }
}

// Fetch user's own skill exchange posts
$my_posts_sql = "SELECT se.*, 
                 (SELECT COUNT(*) FROM skill_exchange_responses WHERE exchange_id = se.exchange_id) as response_count
                 FROM skill_exchange se 
                 WHERE se.user_id = $user_id 
                 ORDER BY se.created_at DESC";
$my_posts_result = mysqli_query($conn, $my_posts_sql);
?>

<div class="dashboard-container">
    <div class="container">
        <div class="page-header">
            <h2>Skill Exchange</h2>
            <a href="view_exchange.php" class="btn btn-secondary">View All Requests</a>
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
        
        <!-- Post Skill Exchange Request Form -->
        <div class="section">
            <h3>Post a Skill Exchange Request</h3>
            <p style="color: #666; margin-bottom: 1.5rem;">Share your expertise and learn something new! Offer a skill you have and request one you'd like to learn.</p>
            
            <form method="POST" action="" class="skill-exchange-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="offer_skill">I Can Teach</label>
                        <input type="text" id="offer_skill" name="offer_skill" 
                               placeholder="e.g., Web Development, Graphic Design, Digital Marketing" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="request_skill">I Want to Learn</label>
                        <input type="text" id="request_skill" name="request_skill" 
                               placeholder="e.g., Python Programming, Video Editing, SEO" 
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" 
                              placeholder="Describe your expertise in the skill you're offering and what you hope to learn. Include your experience level, availability, and any specific topics you're interested in." 
                              required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Post Skill Exchange</button>
            </form>
        </div>
        
        <!-- My Posted Requests -->
        <div class="section">
            <h3>My Posted Requests</h3>
            
            <?php if (mysqli_num_rows($my_posts_result) > 0): ?>
                <div class="exchange-grid">
                    <?php while($post = mysqli_fetch_assoc($my_posts_result)): ?>
                        <div class="exchange-card">
                            <div class="exchange-header">
                                <span class="badge badge-<?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span>
                            </div>
                            
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
                            
                            <div class="exchange-actions">
                                <a href="view_exchange_responses.php?exchange_id=<?php echo $post['exchange_id']; ?>" class="btn btn-secondary btn-sm">
                                    View Responses (<?php echo $post['response_count']; ?>)
                                </a>
                                
                                <?php if ($post['status'] == 'open'): ?>
                                    <form method="POST" action="../actions/handle_exchange_status.php" style="display: inline;">
                                        <input type="hidden" name="exchange_id" value="<?php echo $post['exchange_id']; ?>">
                                        <input type="hidden" name="status" value="closed">
                                        <button type="submit" class="btn btn-danger btn-sm">Close Request</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">You haven't posted any skill exchange requests yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

