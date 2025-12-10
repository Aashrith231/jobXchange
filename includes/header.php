<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobXchange - Internship & Job Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">JobXchange</h1>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <nav class="user-nav">
                        <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
                        <?php if ($_SESSION['role'] != 'admin'): ?>
                            <a href="skill_exchange.php" class="btn btn-primary">
                                🔄 Skill Exchange
                            </a>
                            <?php
                            include 'db.php';
                            // Count unread messages
                            $user_id = $_SESSION['user_id'];
                            $unread_sql = "SELECT COUNT(*) as unread FROM messages WHERE receiver_id = $user_id AND is_read = 0";
                            $unread_result = mysqli_query($conn, $unread_sql);
                            $unread_count = mysqli_fetch_assoc($unread_result)['unread'];
                            ?>
                            <a href="messages.php" class="btn btn-secondary">
                                💬 Messages
                                <?php if ($unread_count > 0): ?>
                                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                        <a href="../actions/handle_logout.php" class="btn btn-logout">Logout</a>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </header>

