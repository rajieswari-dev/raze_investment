<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

if (isset($_GET['action']) && isset($_GET['id'])) {
    $tk_id = intval($_GET['id']);
    if ($_GET['action'] == 'close') {
        $conn->query("UPDATE support_tickets SET status = 'closed' WHERE id = $tk_id");
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Support Tickets Manage</h2>

        <div class="card">
            <h3 style="margin-bottom: 1rem;">All Tickets</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>User Name</th>
                            <th>Subject</th>
                            <th>Created On</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tks = $conn->query("
                            SELECT t.*, u.name as u_name 
                            FROM support_tickets t 
                            JOIN users u ON t.user_id = u.id 
                            ORDER BY CASE WHEN t.status = 'open' THEN 0 ELSE 1 END, t.created_at DESC
                        ");
                        if($tks->num_rows > 0):
                            while($t = $tks->fetch_assoc()):
                        ?>
                        <tr>
                            <td>#<?php echo $t['id']; ?></td>
                            <td><?php echo htmlspecialchars($t['u_name']); ?></td>
                            <td><?php echo htmlspecialchars($t['subject']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($t['created_at'])); ?></td>
                            <td><span class="badge badge-<?php echo ($t['status']=='open'?'warning':'success'); ?>"><?php echo ucfirst($t['status']); ?></span></td>
                            <td>
                                <a href="reply_ticket.php?id=<?php echo $t['id']; ?>" class="btn btn-secondary" style="padding:0.25rem 0.5rem; font-size:0.8rem;">Reply</a>
                                <?php if($t['status'] == 'open'): ?>
                                    <a href="support.php?action=close&id=<?php echo $t['id']; ?>" class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Close this ticket?');">Close</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" style="text-align:center;">No tickets found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
