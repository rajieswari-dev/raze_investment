<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];

// Mark all as read when visited
$conn->query("UPDATE notifications SET is_read = TRUE WHERE user_id = $user_id OR user_id IS NULL");

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Notifications</h2>

        <div class="card" style="max-width: 800px;">
            <?php
            $nots = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id OR user_id IS NULL ORDER BY created_at DESC");
            if($nots->num_rows > 0):
                while($not = $nots->fetch_assoc()):
            ?>
            <div style="padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); <?php echo !$not['is_read'] ? 'background: rgba(255,255,255,0.02);' : ''; ?>">
                <h4 style="color:var(--accent); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($not['title']); ?></h4>
                <p style="color:var(--text-main); margin-bottom: 0.5rem;"><?php echo nl2br(htmlspecialchars($not['message'])); ?></p>
                <small style="color:var(--text-muted);"><?php echo date('d M Y, h:i A', strtotime($not['created_at'])); ?></small>
            </div>
            <?php endwhile; else: ?>
            <p style="text-align:center; color:var(--text-muted);">You have no notifications.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
