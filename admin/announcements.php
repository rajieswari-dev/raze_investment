<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$msg = '';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM announcements WHERE id = $id");
    $msg = "Announcement deleted.";
}

if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE announcements SET is_active = NOT is_active WHERE id = $id");
    $msg = "Status toggled.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_news'])) {
    $message = sanitize($conn, $_POST['message']);
    $type = $_POST['type'];
    
    $stmt = $conn->prepare("INSERT INTO announcements (message, type, is_active) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $message, $type);
    $stmt->execute();
    $msg = "News broadcasted successfully!";
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Manage News & Announcements</h2>

        <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

        <div class="grid">
            <!-- Add News -->
            <div class="card">
                <h3>Post New Announcement</h3>
                <form method="POST" style="margin-top:1.5rem;">
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" rows="3" required placeholder="e.g. New investment plan launched!"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Style / Type</label>
                        <select name="type">
                            <option value="success">Success (Green)</option>
                            <option value="info">Info (Blue)</option>
                            <option value="warning">Warning (Yellow)</option>
                            <option value="danger">Urgent (Red)</option>
                        </select>
                    </div>
                    <button type="submit" name="add_news" class="btn btn-primary" style="width:100%;">Broadcast Now</button>
                </form>
            </div>

            <!-- News List -->
            <div class="card">
                <h3>Announcement History</h3>
                <div style="margin-top:1.5rem; overflow-y:auto; max-height: 400px;">
                    <?php
                    $news = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
                    while($n = $news->fetch_assoc()):
                    ?>
                    <div style="padding:1rem; border: 1px solid rgba(255,255,255,0.05); border-radius:8px; margin-bottom:1rem; opacity: <?php echo $n['is_active'] ? '1' : '0.5'; ?>;">
                        <p style="margin-bottom:0.5rem;"><?php echo htmlspecialchars($n['message']); ?></p>
                        <small style="color:var(--text-muted); display:block; margin-bottom:0.5rem;">Posted: <?php echo date('d M, h:i A', strtotime($n['created_at'])); ?></small>
                        <div style="display:flex; gap:0.5rem;">
                            <a href="announcements.php?toggle=<?php echo $n['id']; ?>" class="btn <?php echo $n['is_active']?'btn-warning':'btn-success'; ?>" style="font-size:0.75rem; padding:0.2rem 0.6rem;">
                                <?php echo $n['is_active'] ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <a href="announcements.php?delete=<?php echo $n['id']; ?>" class="btn btn-danger" style="font-size:0.75rem; padding:0.2rem 0.6rem;" onclick="return confirm('Delete news?')">Delete</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
