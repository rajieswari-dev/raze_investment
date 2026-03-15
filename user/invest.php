<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];
$kyc_status = $_SESSION['kyc_status'];

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Investment Plans</h2>

        <?php if($kyc_status != 'approved'): ?>
            <div class="alert alert-error">
                You cannot invest at this time. Your KYC must be <strong>Approved</strong>. Please check your <a href="kyc.php" style="color:white; text-decoration:underline;">KYC Status</a>.
            </div>
        <?php else: ?>
            <div class="grid">
                <?php
                $plans = $conn->query("SELECT * FROM investment_plans ORDER BY min_amount ASC");
                while($plan = $plans->fetch_assoc()):
                ?>
                <div class="stat-card" style="border: 1px solid var(--primary); text-align: left;">
                    <h3 style="color:var(--text-main); font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($plan['name']); ?></h3>
                    <p style="font-size: 1.1rem; color:var(--text-muted); margin-bottom: 1rem;">
                        Min Investment: <strong>₹<?php echo number_format($plan['min_amount'], 2); ?></strong>
                    </p>
                    <ul style="list-style:none; margin-bottom: 1.5rem; color: var(--text-muted);">
                        <li style="margin-bottom: 0.5rem;">📈 ROI: <strong style="color:var(--success)"><?php echo $plan['roi_percentage']; ?>%</strong></li>
                        <li style="margin-bottom: 0.5rem;">⏱ Duration: <strong><?php echo $plan['duration_months']; ?> Months</strong></li>
                    </ul>
                    <a href="payment.php?plan_id=<?php echo $plan['id']; ?>" class="btn btn-primary" style="width:100%;">Invest Now</a>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
