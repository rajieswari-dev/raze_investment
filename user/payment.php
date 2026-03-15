<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];
$kyc_status = $_SESSION['kyc_status'];

if ($kyc_status != 'approved') {
    die("Unauthorized Access. KYC not approved.");
}

if (!isset($_GET['plan_id'])) redirect('invest.php');
$plan_id = intval($_GET['plan_id']);

$stmt = $conn->prepare("SELECT * FROM investment_plans WHERE id = ?");
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) {
    die("Plan not found");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $method = sanitize($conn, $_POST['payment_method']);
    $reference = sanitize($conn, $_POST['reference_id']);
    
    // Handle Screenshot Upload
    $screenshot_name = '';
    if(isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] == 0) {
        $ext = pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION);
        $screenshot_name = 'pay_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['screenshot']['tmp_name'], '../uploads/' . $screenshot_name);
    }

    if ($amount < $plan['min_amount']) {
        $error = "Minimum investment for this plan is ₹" . number_format($plan['min_amount'], 2);
    } else {
        // Create Transaction - Keep it 'pending' until Admin verifies screenshot
        $stmt_tx = $conn->prepare("INSERT INTO transactions (user_id, amount, type, payment_method, reference_id, proof_screenshot, status) VALUES (?, ?, 'investment', ?, ?, ?, 'pending')");
        $stmt_tx->bind_param("idsss", $user_id, $amount, $method, $reference, $screenshot_name);
        
        if ($stmt_tx->execute()) {
            $success = "Payment proof submitted! Your investment will be activated after admin verification.";
        } else {
            $error = "Payment tracking failed.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Complete Payment</h2>

        <div class="card" style="max-width: 600px;">
            <div style="background:var(--background); padding:1.5rem; border-radius:8px; margin-bottom: 1.5rem;">
                <h3 style="margin-bottom: 0.5rem; color:var(--accent);"><?php echo htmlspecialchars($plan['name']); ?></h3>
                <p>Minimum Investment: <strong>₹<?php echo number_format($plan['min_amount'], 2); ?></strong></p>
                <p>Duration: <strong><?php echo $plan['duration_months']; ?> Months</strong> @ <?php echo $plan['roi_percentage']; ?>% ROI</p>
            </div>

            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?> <br><br><a href="history.php" class="btn btn-secondary">View History</a></div>
            <?php else: ?>
                <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Investment Amount (₹)</label>
                        <input type="number" name="amount" min="<?php echo $plan['min_amount']; ?>" step="100" required value="<?php echo $plan['min_amount']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" required>
                            <option value="upi">UPI</option>
                            <option value="qr_code">QR Code</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transaction Reference ID</label>
                        <input type="text" name="reference_id" required placeholder="e.g. UTR Number or Transaction ID">
                    </div>
                    <div class="form-group">
                        <label>Payment Proof (Screenshot)</label>
                        <input type="file" name="screenshot" accept="image/*" required>
                    </div>
                    <!-- Mock QR code display if selected (Using JS to show/hide would be better, but we keep it simple here) -->
                    <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                        <p style="color:var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Send payment to UPI ID: <strong>razeinvestment@bank</strong></p>
                        <p style="color:var(--text-muted); font-size: 0.9rem;">Or Bank: Raze Investment | A/C: 1234567890 | IFSC: RAZE0001</p>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Confirm Payment</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
