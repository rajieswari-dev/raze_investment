<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Investment History</h2>

        <div class="card">
            <h3>Your Investments</h3>
            <div style="margin-top:1.5rem; overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Plan Name</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $invs = $conn->query("
                            SELECT i.*, p.name as plan_name 
                            FROM investments i 
                            JOIN investment_plans p ON i.plan_id = p.id 
                            WHERE i.user_id = $user_id 
                            ORDER BY i.created_at DESC
                        ");
                        if($invs->num_rows > 0):
                            while($inv = $invs->fetch_assoc()):
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($inv['plan_name']); ?></strong></td>
                            <td>₹<?php echo number_format($inv['amount'], 2); ?></td>
                            <td><span class="badge badge-<?php echo ($inv['status']=='active'?'success':($inv['status']=='completed'?'success':'danger')); ?>"><?php echo ucfirst($inv['status']); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($inv['start_date'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($inv['end_date'])); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center;">No investments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top:2rem;">
            <h3>Transaction Log</h3>
            <div style="margin-top:1.5rem; overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $txs = $conn->query("SELECT * FROM transactions WHERE user_id = $user_id ORDER BY created_at DESC");
                        if($txs->num_rows > 0):
                            while($tx = $txs->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?></td>
                            <td><small style="color:var(--text-muted);"><?php echo htmlspecialchars($tx['reference_id'] ?? '-'); ?></small></td>
                            <td><?php echo ucfirst($tx['type']); ?></td>
                            <td>₹<?php echo number_format($tx['amount'], 2); ?></td>
                            <td><?php echo strtoupper($tx['payment_method']); ?></td>
                            <td><span class="badge badge-<?php echo ($tx['status']=='success'?'success':($tx['status']=='failed'?'danger':'pending')); ?>"><?php echo ucfirst($tx['status']); ?></span>
                                <?php if($tx['status'] == 'success'): ?>
                                    <a href="receipt.php?id=<?php echo $tx['id']; ?>" target="_blank" style="margin-left:5px; font-size:0.8rem; color:var(--accent);">[Receipt]</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" style="text-align:center;">No transactions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
