<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send'])) {
    $user_id = $_POST['user_id'] == 'all' ? 'NULL' : intval($_POST['user_id']);
    $title = sanitize($conn, $_POST['title']);
    $message = sanitize($conn, $_POST['message']);

    $q = "INSERT INTO notifications (user_id, title, message) VALUES ($user_id, '$title', '$message')";
    if ($conn->query($q)) {
        $msg = "Notification sent successfully.";
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Send Notifications</h2>

        <div class="card" style="max-width: 600px;">
            <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>
            <h3>Broadcast / Send Message</h3>
            <form method="POST" style="margin-top: 1.5rem;">
                <div class="form-group">
                    <label>Recipient</label>
                    <select name="user_id">
                        <option value="all">All Users</option>
                        <?php
                        $users = $conn->query("SELECT id, name, email FROM users");
                        while($u = $users->fetch_assoc()) {
                            echo "<option value='{$u['id']}'>" . htmlspecialchars($u['name'] . " (" . $u['email'] . ")") . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="5" required></textarea>
                </div>
                <button type="submit" name="send" class="btn btn-primary" style="width:100%;">Send Notification</button>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
