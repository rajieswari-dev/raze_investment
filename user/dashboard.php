<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Active Investments Total
$active_inv = $conn->query("SELECT SUM(amount) as active FROM investments WHERE user_id = $user_id AND status = 'active'")->fetch_assoc()['active'] ?? 0;

// Total Returns/Profits
$total_returns = $conn->query("SELECT SUM(amount) as ret FROM transactions WHERE user_id = $user_id AND type = 'return' AND status = 'success'")->fetch_assoc()['ret'] ?? 0;

// Total Withdrawn
$total_withdrawn = $conn->query("SELECT SUM(amount) as wit FROM transactions WHERE user_id = $user_id AND type = 'withdrawal' AND status = 'success'")->fetch_assoc()['wit'] ?? 0;

// Fetch last 30 days of return history for chart
$chart_returns = [];
$chart_dates = [];
$res = $conn->query("SELECT DATE(created_at) as dt, SUM(amount) as amt FROM transactions WHERE user_id = $user_id AND type = 'return' AND status = 'success' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY dt ASC");
while($row = $res->fetch_assoc()) {
    $chart_dates[] = date('d M', strtotime($row['dt']));
    $chart_returns[] = $row['amt'];
}

// Update session KYC status
$_SESSION['kyc_status'] = $user['kyc_status'];

require_once '../includes/header.php';
?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem; flex-wrap:wrap; gap:1rem;">
            <h2>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h2>
            <div style="text-align:right;">
                <small style="color:var(--text-muted); display:block;">Last Login: <?php echo $user['last_login_at'] ? date('d M, h:i A', strtotime($user['last_login_at'])) : 'First time'; ?></small>
                <small style="color:var(--text-muted); display:block;">IP: <?php echo $user['last_login_ip'] ?? 'N/A'; ?></small>
            </div>
        </div>
        
        <?php if($user['kyc_status'] == 'unsubmitted'): ?>
            <div class="alert alert-error">
                Your KYC is pending. Please complete your <a href="kyc.php" style="color:white; text-decoration:underline;">KYC Verification</a> to start investing.
            </div>
        <?php elseif($user['kyc_status'] == 'pending'): ?>
            <div class="alert" style="background:var(--warning); color:white;">
                Your KYC details are under review by the administration.
            </div>
        <?php elseif($user['kyc_status'] == 'rejected'): ?>
            <div class="alert alert-error">
                Your previous KYC submission was rejected. Please <a href="kyc.php" style="color:white; text-decoration:underline;">resubmit</a> your details.
            </div>
        <?php endif; ?>

        <div class="grid">
            <div class="stat-card">
                <h3>Wallet Balance</h3>
                <p>₹<?php echo number_format($user['wallet_balance'], 2); ?></p>
                <div style="margin-top:0.5rem; display:flex; gap:0.5rem;">
                    <a href="withdraw.php" class="btn btn-secondary" style="font-size:0.75rem; padding:0.3rem 0.6rem;">Withdraw</a>
                    <a href="invest.php" class="btn btn-primary" style="font-size:0.75rem; padding:0.3rem 0.6rem;">Invest Now</a>
                </div>
            </div>
            <div class="stat-card">
                <h3>Active Investments</h3>
                <p>₹<?php echo number_format($active_inv, 2); ?></p>
                <small style="color:var(--text-muted);">Earning daily returns</small>
            </div>
            <div class="stat-card">
                <h3>Total Earnings</h3>
                <p style="color:var(--success);">+₹<?php echo number_format($total_returns, 2); ?></p>
                <small style="color:var(--text-muted);">Lifetime ROI</small>
            </div>
            <div class="stat-card">
                <h3>Total Withdrawn</h3>
                <p>₹<?php echo number_format($total_withdrawn, 2); ?></p>
                <small style="color:var(--text-muted);">Paid to bank</small>
            </div>
        </div>

        <div class="grid" style="margin-top:2rem;">
            <div class="stat-card">
                <h3>KYC Status</h3>
                <p style="font-size: 1.2rem; margin-top:0.5rem;">
                    <span class="badge badge-<?php 
                        echo ($user['kyc_status']=='approved'?'success':($user['kyc_status']=='rejected'?'danger':'warning')); 
                    ?>">
                        <?php echo ucfirst($user['kyc_status']); ?>
                    </span>
                </p>
                <?php if($user['kyc_status'] != 'approved'): ?>
                <a href="kyc.php" style="font-size:0.8rem; color:var(--accent); text-decoration:underline;">Update KYC</a>
                <?php endif; ?>
            </div>
            <div class="stat-card">
                <h3>Referral Code</h3>
                <p style="font-size: 1.5rem; letter-spacing: 2px;"><?php echo htmlspecialchars($user['referral_code']); ?></p>
                <small style="color:var(--text-muted); font-size: 0.75rem;">Get ₹<?php echo get_setting($conn, 'referral_bonus', 100); ?> per friend!</small>
            </div>
        </div>

        <div class="grid" style="margin-top:2rem;">
            <div class="card" style="margin-bottom:0;">
                <h3>Portfolio Distribution</h3>
                <canvas id="portfolioChart" style="margin-top:1.5rem; max-height: 250px;"></canvas>
            </div>
            <div class="card" style="margin-bottom:0;">
                <h3>Returns (Last 30 Days)</h3>
                <canvas id="returnsChart" style="margin-top:1.5rem; max-height: 250px;"></canvas>
            </div>
        </div>

        <div class="card" style="margin-top:2rem;">
            <h3>Recent Transactions</h3>
            <div style="margin-top:1.5rem; overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $txs = $conn->query("SELECT * FROM transactions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
                        if($txs->num_rows > 0):
                            while($tx = $txs->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?></td>
                            <td><?php echo ucfirst($tx['type']); ?></td>
                            <td>₹<?php echo number_format($tx['amount'], 2); ?></td>
                            <td><?php echo strtoupper($tx['payment_method']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo ($tx['status']=='success'?'success':($tx['status']=='failed'?'danger':'pending')); ?>"><?php echo ucfirst($tx['status']); ?></span>
                                <?php if($tx['status'] == 'success'): ?>
                                    <a href="receipt.php?id=<?php echo $tx['id']; ?>" target="_blank" style="margin-left:5px; font-size:0.8rem; color:var(--accent);">[Receipt]</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center;">No recent transactions.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    // Portfolio Pie Chart
    const ctx1 = document.getElementById('portfolioChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Active Investments', 'Wallet Balance'],
            datasets: [{
                data: [<?php echo $active_inv; ?>, <?php echo $user['wallet_balance']; ?>],
                backgroundColor: ['#4F46E5', '#10B981'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { color: '#f8fafc' } } }
        }
    });

    // Returns Line Chart
    const ctx2 = document.getElementById('returnsChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_dates); ?>,
            datasets: [{
                label: 'Daily Return (₹)',
                data: <?php echo json_encode($chart_returns); ?>,
                borderColor: '#38BDF8',
                backgroundColor: 'rgba(56, 189, 248, 0.2)',
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
