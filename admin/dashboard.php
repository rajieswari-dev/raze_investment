<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$total_users = $conn->query("SELECT COUNT(id) as c FROM users")->fetch_assoc()['c'];
$total_invs = $conn->query("SELECT SUM(amount) as s FROM investments WHERE status = 'active'")->fetch_assoc()['s'] ?? 0;
$pending_kyc = $conn->query("SELECT COUNT(id) as c FROM kyc_documents WHERE status = 'pending'")->fetch_assoc()['c'];
$pending_txs = $conn->query("SELECT COUNT(id) as c FROM transactions WHERE status = 'pending'")->fetch_assoc()['c'];

// Advanced Platform Stats
$total_deposits = $conn->query("SELECT SUM(amount) as s FROM transactions WHERE status = 'success' AND (type='deposit' OR type='investment')")->fetch_assoc()['s'] ?? 0;
$total_payouts = $conn->query("SELECT SUM(amount) as s FROM transactions WHERE status = 'success' AND type='withdrawal'")->fetch_assoc()['s'] ?? 0;
$total_liabilities = $conn->query("SELECT SUM(wallet_balance) as s FROM users")->fetch_assoc()['s'] ?? 0;
$open_tickets = $conn->query("SELECT COUNT(id) as c FROM support_tickets WHERE status = 'open'")->fetch_assoc()['c'];

// Reserve Fund (Deposits - Payouts)
$reserve_fund = $total_deposits - $total_payouts;

// Projected Daily Return Payout
$projected_returns = $conn->query("SELECT SUM(daily_return) as s FROM investments WHERE status = 'active'")->fetch_assoc()['s'] ?? 0;


// Fetch Deposits vs Withdrawals for chart
$chart_tx_dates = [];
$chart_deposits = [];
$chart_withdrawals = [];
$res_tx = $conn->query("SELECT DATE(created_at) as dt, SUM(CASE WHEN type='investment' THEN amount ELSE 0 END) as dep, SUM(CASE WHEN type='withdrawal' THEN amount ELSE 0 END) as wit FROM transactions WHERE status='success' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY dt ASC");
while($row = $res_tx->fetch_assoc()) {
    $chart_tx_dates[] = date('d M', strtotime($row['dt']));
    $chart_deposits[] = $row['dep'];
    $chart_withdrawals[] = $row['wit'];
}

// Fetch user growth
$chart_user_dates = [];
$chart_users = [];
$res_usr = $conn->query("SELECT DATE(created_at) as dt, COUNT(id) as c FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY dt ASC");
while($row = $res_usr->fetch_assoc()) {
    $chart_user_dates[] = date('d M', strtotime($row['dt']));
    $chart_users[] = $row['c'];
}

require_once '../includes/header.php';
?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Admin Dashboard</h2>

        <div class="grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
                <small style="color:var(--text-muted);">Registered members</small>
            </div>
            <div class="stat-card">
                <h3>Active Investments</h3>
                <p>₹<?php echo number_format($total_invs, 2); ?></p>
                <small style="color:var(--text-muted);">Capital in play</small>
            </div>
            <div class="stat-card">
                <h3>Total Deposits</h3>
                <p style="color:var(--success);">₹<?php echo number_format($total_deposits, 2); ?></p>
                <small style="color:var(--text-muted);">Lifetime inflows</small>
            </div>
            <div class="stat-card">
                <h3>Total Payouts</h3>
                <p style="color:var(--danger);">₹<?php echo number_format($total_payouts, 2); ?></p>
                <small style="color:var(--text-muted);">Lifetime withdrawals</small>
            </div>
        </div>

        <div class="grid" style="margin-top:2rem;">
            <div class="stat-card">
                <h3>Wallet Liabilities</h3>
                <p>₹<?php echo number_format($total_liabilities, 2); ?></p>
                <small style="color:var(--text-muted);">Total user balances</small>
            </div>
            <div class="stat-card">
                <h3>Open Tickets</h3>
                <p><?php echo $open_tickets; ?></p>
                <a href="support.php" style="font-size:0.8rem; color:var(--accent);">Reply Now</a>
            </div>
            <div class="stat-card">
                <h3>Pending KYC</h3>
                <p style="color:var(--warning);"><?php echo $pending_kyc; ?></p>
                <a href="kyc_requests.php" style="font-size:0.8rem; color:var(--accent);">Review Now</a>
            </div>
            <div class="stat-card">
                <h3>Reserve Fund</h3>
                <p style="color:var(--primary);">₹<?php echo number_format($reserve_fund, 2); ?></p>
                <small style="color:var(--text-muted);">Deposits - Payouts</small>
            </div>
            <div class="stat-card">
                <h3>Pending Payouts</h3>
                <p style="color:var(--warning);"><?php echo $pending_txs; ?></p>
                <a href="transactions.php" style="font-size:0.8rem; color:var(--accent);">Processing</a>
            </div>
            <div class="stat-card">
                <h3>Est. Daily Payout</h3>
                <p style="color:var(--warning);">₹<?php echo number_format($projected_returns, 2); ?></p>
                <small style="color:var(--text-muted);">Next cron liability</small>
            </div>
        </div>

        <div class="grid" style="margin-top:2rem;">
            <div class="card" style="margin-bottom:0;">
                <h3>Newest Users</h3>
                <div style="margin-top:1rem; overflow-x:auto;">
                    <table style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>KYC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $new_users = $conn->query("SELECT name, created_at, kyc_status FROM users ORDER BY created_at DESC LIMIT 5");
                            while($nu = $new_users->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nu['name']); ?></td>
                                <td><?php echo date('d M', strtotime($nu['created_at'])); ?></td>
                                <td><span class="badge badge-<?php echo ($nu['kyc_status']=='approved'?'success':($nu['kyc_status']=='rejected'?'danger':'warning')); ?>" style="font-size:0.6rem;"><?php echo $nu['kyc_status']; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="margin-bottom:0;">
                <h3>Security: Recent Login Activity</h3>
                <div style="margin-top:1rem; overflow-x:auto;">
                    <table style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $logins = $conn->query("SELECT l.*, u.name FROM login_activity l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.login_at DESC LIMIT 5");
                            while($log = $logins->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['name'] ?? 'Admin'); ?></td>
                                <td><code><?php echo $log['ip_address']; ?></code></td>
                                <td><small style="color:var(--text-muted);"><?php echo substr($log['user_agent'], 0, 30); ?>...</small></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid" style="margin-top:2rem;">
            <div class="card" style="margin-bottom:0;">
                <h3>Investments vs Withdrawals (30 Days)</h3>
                <canvas id="txChart" style="margin-top:1.5rem; max-height: 250px;"></canvas>
            </div>
            <div class="card" style="margin-bottom:0;">
                <h3>User Growth (30 Days)</h3>
                <canvas id="userChart" style="margin-top:1.5rem; max-height: 250px;"></canvas>
            </div>
        </div>

        <div class="card" style="margin-top:2rem;">
            <h3>Recent Transactions</h3>
            <div style="margin-top:1.5rem; overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent_txs = $conn->query("SELECT t.*, u.name FROM transactions t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 5");
                        while($rt = $recent_txs->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rt['name'] ?? 'System'); ?></td>
                            <td>₹<?php echo number_format($rt['amount'], 2); ?></td>
                            <td><?php echo ucfirst($rt['type']); ?></td>
                            <td><span class="badge badge-<?php echo ($rt['status']=='success'?'success':($rt['status']=='pending'?'warning':'danger')); ?>"><?php echo $rt['status']; ?></span></td>
                            <td><?php echo date('d M, h:i A', strtotime($rt['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top:2rem;">
            <h3>Quick Actions</h3>
            <div style="margin-top:1.5rem; display:flex; gap:1rem; flex-wrap:wrap;">
                <a href="kyc_requests.php" class="btn btn-primary">Review KYC</a>
                <a href="announcements.php" class="btn btn-secondary">Manage News Bar</a>
                <a href="plans.php" class="btn btn-secondary">Manage Plans</a>
                <a href="export.php?type=users" class="btn btn-success" style="background:#10B981;">Export Users (CSV)</a>
                <a href="export.php?type=transactions" class="btn btn-success" style="background:#10B981;">Export TXs (CSV)</a>
            </div>
        </div>
    </main>
</div>

<script>
    // Transactions Bar Chart
    const ctx1 = document.getElementById('txChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_tx_dates); ?>,
            datasets: [
                {
                    label: 'Investments (₹)',
                    data: <?php echo json_encode($chart_deposits); ?>,
                    backgroundColor: '#10B981'
                },
                {
                    label: 'Withdrawals (₹)',
                    data: <?php echo json_encode($chart_withdrawals); ?>,
                    backgroundColor: '#EF4444'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: { ticks: { color: '#f8fafc' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#f8fafc' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            },
            plugins: { legend: { position: 'top', labels: { color: '#f8fafc' } } }
        }
    });

    // Users Line Chart
    const ctx2 = document.getElementById('userChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_user_dates); ?>,
            datasets: [{
                label: 'New Users',
                data: <?php echo json_encode($chart_users); ?>,
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { ticks: { color: '#f8fafc' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#f8fafc' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            },
            plugins: { legend: { display: false } }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>
