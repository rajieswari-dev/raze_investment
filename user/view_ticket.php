<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$tq = $conn->prepare("SELECT * FROM support_tickets WHERE id = ? AND user_id = ?");
$tq->bind_param("ii", $ticket_id, $user_id);
$tq->execute();
$ticket = $tq->get_result()->fetch_assoc();

if(!$ticket) die("Ticket not found.");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
    if ($ticket['status'] == 'closed') {
        $error = "Ticket is closed.";
    } else {
        $msg = sanitize($conn, $_POST['message']);
        $rep = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $rep->bind_param("iis", $ticket_id, $user_id, $msg);
        $rep->execute();
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
            <h2>Ticket #<?php echo $ticket_id; ?> - <?php echo htmlspecialchars($ticket['subject']); ?></h2>
            <span class="badge badge-<?php echo ($ticket['status']=='open'?'warning':'success'); ?>" style="font-size:1rem;"><?php echo ucfirst($ticket['status']); ?></span>
        </div>

        <div id="chat-container" class="card" style="max-width:800px; height: 500px; overflow-y:auto; background: var(--card-bg); display: flex; flex-direction: column; gap: 15px; padding: 20px;">
            <?php
            $reps = $conn->query("
                SELECT r.*, u.name as u_name, a.username as a_name 
                FROM ticket_replies r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN admins a ON r.admin_id = a.id
                WHERE r.ticket_id = $ticket_id
                ORDER BY r.created_at ASC
            ");
            while($r = $reps->fetch_assoc()):
                $is_admin = !empty($r['admin_id']);
            ?>
            <div style="max-width: 80%; padding: 12px 16px; border-radius: 15px; position: relative; 
                <?php echo $is_admin ? 'align-self: flex-start; background: rgba(79, 70, 229, 0.1); border-bottom-left-radius: 2px;' : 'align-self: flex-end; background: var(--primary); color: white; border-bottom-right-radius: 2px;'; ?>">
                
                <div style="font-size: 0.75rem; margin-bottom: 5px; opacity: 0.8; display: flex; justify-content: space-between; gap: 10px;">
                    <strong><?php echo $is_admin ? 'Raze Support' : 'You'; ?></strong>
                    <span><?php echo date('h:i A', strtotime($r['created_at'])); ?></span>
                </div>
                
                <p style="margin: 0; line-height: 1.4;"><?php echo nl2br(htmlspecialchars($r['message'])); ?></p>
            </div>
            <?php endwhile; ?>
        </div>

        <script>
            // Auto scroll to bottom of chat
            const chatContainer = document.getElementById('chat-container');
            chatContainer.scrollTop = chatContainer.scrollHeight;
        </script>

        <?php if($ticket['status'] == 'open'): ?>
        <div class="card" style="max-width:800px; margin-top:2rem;">
            <form method="POST">
                <div class="form-group">
                    <label>Reply to Ticket</label>
                    <textarea name="message" rows="3" required></textarea>
                </div>
                <button type="submit" name="reply" class="btn btn-primary" style="width:100%;">Send Reply</button>
            </form>
        </div>
        <?php else: ?>
        <div class="alert alert-success" style="max-width:800px; margin-top:2rem;">This ticket is closed and resolved.</div>
        <?php endif; ?>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
