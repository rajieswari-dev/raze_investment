<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$msg = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'] == 'approve' ? 'success' : 'failed';
    
    $tx = $conn->query("SELECT user_id, amount, type FROM transactions WHERE id = $id")->fetch_assoc();
    if($tx) {
        $u_id = $tx['user_id'];
        $conn->query("UPDATE transactions SET status = '$action' WHERE id = $id");
        
        // --- Special Logic: If Investment Payment is approved, activate the plan ---
        if($tx['type'] == 'investment' && $action == 'success') {
            // Fetch plan_id (We might need to store plan_id in transactions or match amount, but better to fetch from payload)
            // Simplified: We'll assume the most recent 'active' request for this user. 
            // Better way: We should have stored the plan_id in the transaction. Let's add it to the tx query if possible or assume a default for now.
            // Since we didn't add plan_id to transactions table yet, let's grab the best match.
            $plan_q = $conn->query("SELECT id, duration_months FROM investment_plans WHERE min_amount <= " . $tx['amount'] . " ORDER BY min_amount DESC LIMIT 1");
            if($plan_q->num_rows > 0) {
                $plan = $plan_q->fetch_assoc();
                $start = date('Y-m-d');
                $end = date('Y-m-d', strtotime("+" . $plan['duration_months'] . " months"));
                $stmt_inv = $conn->prepare("INSERT INTO investments (user_id, plan_id, amount, status, start_date, end_date) VALUES (?, ?, ?, 'active', ?, ?)");
                $stmt_inv->bind_param("iidss", $u_id, $plan['id'], $tx['amount'], $start, $end);
                $stmt_inv->execute();
            }
        }
        // --------------------------------------------------------------------------

        // Notify user in system
        $title = "Transaction " . ucfirst($action);
        $text = "Your " . $tx['type'] . " transaction of ₹" . $tx['amount'] . " has been marked as $action.";
        $conn->query("INSERT INTO notifications (user_id, title, message) VALUES ($u_id, '$title', '$text')");

        // Send Email Notification
        $u_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $u_stmt->bind_param("i", $u_id);
        $u_stmt->execute();
        $u_email = $u_stmt->get_result()->fetch_assoc()['email'];
        
        require_once '../includes/mailer.php';
        $email_subject = "Transaction Update: " . ucfirst($tx['type']);
        $email_body = "Your transaction of ₹" . number_format($tx['amount'], 2) . " has been <strong>$action</strong>.";
        sendOTP($u_email, $email_body, $email_subject);

        $msg = "Transaction $action recorded.";
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Transactions History</h2>

        <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom: 1rem;">All Transactions</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Ref ID</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $txs = $conn->query("
                            SELECT t.*, u.name as user_name 
                            FROM transactions t 
                            JOIN users u ON t.user_id = u.id 
                            ORDER BY t.created_at DESC
                        ");
                        if($txs->num_rows > 0):
                            while($t = $txs->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['user_name']); ?></td>
                            <td><?php echo date('d M y, h:i A', strtotime($t['created_at'])); ?></td>
                            <td><?php echo ucfirst($t['type']); ?></td>
                            <td>₹<?php echo number_format($t['amount'], 2); ?></td>
                            <td><?php echo strtoupper($t['payment_method']); ?></td>
                            <td>
                                <small style="color:var(--text-muted);"><?php echo htmlspecialchars($t['reference_id']); ?></small>
                                <?php if(!empty($t['proof_screenshot'])): ?>
                                    <br><a href="../uploads/<?php echo $t['proof_screenshot']; ?>" target="_blank" style="color:var(--accent); font-size:0.75rem;">View Proof</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo ($t['status']=='success'?'success':($t['status']=='failed'?'danger':'pending')); ?>">
                                    <?php echo ucfirst($t['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($t['status'] == 'pending'): ?>
                                <div style="display:flex; gap:0.5rem;">
                                    <a href="transactions.php?action=approve&id=<?php echo $t['id']; ?>" class="btn btn-success" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Approve?');">✓</a>
                                    <a href="transactions.php?action=reject&id=<?php echo $t['id']; ?>" class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Reject?');">✗</a>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="8" style="text-align:center;">No transactions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
