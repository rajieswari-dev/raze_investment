<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ticket'])) {
    $subject = sanitize($conn, $_POST['subject']);
    $msg = sanitize($conn, $_POST['message']);

    $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, subject, status) VALUES (?, ?, 'open')");
    $stmt->bind_param("is", $user_id, $subject);
    $stmt->execute();
    $tk_id = $stmt->insert_id;

    $rep = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
    $rep->bind_param("iis", $tk_id, $user_id, $msg);
    $rep->execute();

    $success = "Support ticket submitted. We will reply shortly.";
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Support Center</h2>

        <div class="grid">
            <div class="card">
                <h3>Submit New Ticket</h3>
                <?php if($success): ?><div class="alert alert-success" style="margin-top:1rem;"><?php echo $success; ?></div><?php endif; ?>
                <form method="POST" style="margin-top: 1.5rem;">
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label>Message Detail</label>
                        <textarea name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" name="submit_ticket" class="btn btn-primary" style="width:100%;">Create Ticket</button>
                </form>
            </div>

            <div class="card">
                <h3>My Tickets</h3>
                <div style="margin-top:1.5rem; overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tks = $conn->query("SELECT * FROM support_tickets WHERE user_id = $user_id ORDER BY id DESC");
                            if($tks->num_rows > 0):
                                while($t = $tks->fetch_assoc()):
                            ?>
                            <tr>
                                <td>#<?php echo $t['id']; ?></td>
                                <td><?php echo htmlspecialchars($t['subject']); ?></td>
                                <td><span class="badge badge-<?php echo ($t['status']=='open'?'warning':'success'); ?>"><?php echo ucfirst($t['status']); ?></span></td>
                                <td><a href="view_ticket.php?id=<?php echo $t['id']; ?>" class="btn btn-secondary" style="padding:0.25rem 0.5rem; font-size:0.8rem;">View</a></td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4" style="text-align:center;">No tickets yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
