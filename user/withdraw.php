<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

unset($_SESSION['withdraw_payload']);

$min_withdraw = get_setting($conn, 'min_withdrawal', 500);

$user = $conn->query("SELECT wallet_balance, kyc_status FROM users WHERE id = $user_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($user['kyc_status'] != 'approved') {
        $error = "You cannot withdraw until your KYC is Approved.";
    } else {
        $amt = floatval($_POST['amount']);
        if ($amt < $min_withdraw) {
            $error = "Minimum withdrawal is ₹$min_withdraw.";
        } elseif ($amt > $user['wallet_balance']) {
            $error = "Insufficient wallet balance.";
        } else {
            // Validate, generate OTP, and redirect to verify page
            $meth = sanitize($conn, $_POST['method']);

            $otp = rand(100000, 999999);
            $_SESSION['withdraw_payload'] = [
                'amount' => $amt,
                'method' => $meth,
                'otp_code' => $otp,
                'otp_expiry' => time() + (10 * 60)
            ];

            require_once '../includes/mailer.php';
            // Getting user's email
            $stmt = $conn->query("SELECT email FROM users WHERE id = $user_id");
            $email = $stmt->fetch_assoc()['email'];
            
            sendOTP($email, $otp, "Withdrawal Request (₹$amt)");

            redirect('withdraw_verify.php');
        }
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Withdraw Funds</h2>

        <div class="card" style="max-width: 600px;">
            <div style="background:rgba(255,255,255,0.05); padding:1.5rem; border-radius:8px; margin-bottom: 1.5rem; display:flex; justify-content:space-between; align-items:center;">
                <h3>Available Balance</h3>
                <span class="gradient-text" style="font-size:2rem; font-weight:700;">₹<?php echo number_format($user['wallet_balance'], 2); ?></span>
            </div>

            <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Withdrawal Amount (₹)</label>
                    <input type="number" name="amount" min="<?php echo $min_withdraw; ?>" step="100" max="<?php echo $user['wallet_balance']; ?>" required>
                    <small style="color:var(--text-muted)">Minimum withdrawal ₹<?php echo $min_withdraw; ?>.</small>
                </div>
                <div class="form-group">
                    <label>Receive Method</label>
                    <select name="method" required>
                        <option value="bank_transfer">Bank Transfer (To KYC Bank details)</option>
                    </select>
                </div>
                <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1.5rem;">Note: Withdrawals take 24-48 business hours to process.</p>
                <button type="submit" class="btn btn-primary" style="width: 100%;" <?php echo ($user['wallet_balance'] < $min_withdraw) ? 'disabled' : ''; ?>>Request Withdrawal</button>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
