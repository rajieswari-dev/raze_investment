<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

if (!isset($_SESSION['withdraw_payload'])) {
    redirect('withdraw.php');
}

$user_id = $_SESSION['user_id'];
$payload = $_SESSION['withdraw_payload'];
$amt = $payload['amount'];
$meth = $payload['method'];

$error = '';
$success = "OTP Sent! Please check your securely registered email to verify this withdrawal request of ₹" . number_format($amt, 2) . ".";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = trim($_POST['otp']);

    if (time() > $payload['otp_expiry']) {
        $error = "OTP has expired. Please initiate the withdrawal again.";
        unset($_SESSION['withdraw_payload']);
    } elseif ($entered_otp == $payload['otp_code']) {
        // OTP matched -> Process the withdrawal safely

        // 1. Get user again to ensure they still have balance right before deducting
        $user_check = $conn->query("SELECT wallet_balance FROM users WHERE id = $user_id")->fetch_assoc();
        
        if ($user_check['wallet_balance'] >= $amt) {
            // Deduct balance and create pending transaction
            $conn->query("UPDATE users SET wallet_balance = wallet_balance - $amt WHERE id = $user_id");
            
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, payment_method, status) VALUES (?, ?, 'withdrawal', ?, 'pending')");
            $stmt->bind_param("ids", $user_id, $amt, $meth);
            if ($stmt->execute()) {
                unset($_SESSION['withdraw_payload']); // Success, clear session
                
                // We're redirecting back to withdraw page with a flash message, but since PHP doesn't have native flash without $_SESSION trick, we'll just show success here.
                echo "<script>alert('Verified successfully! Your withdrawal request has been submitted to administration.'); window.location.href='history.php';</script>";
                exit();
            } else {
                // Refund on fail (extremely rare, but good practice)
                $conn->query("UPDATE users SET wallet_balance = wallet_balance + $amt WHERE id = $user_id");
                $error = "Database Error. Failed to create withdrawal request.";
            }
        } else {
            $error = "Insufficient wallet balance at time of execution.";
            unset($_SESSION['withdraw_payload']);
        }
    } else {
        $error = "Invalid OTP. Please try again.";
        $success = ''; // Clear success message
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Secure Verification (2FA)</h2>

        <div class="card" style="max-width: 600px;">
            <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Enter 6-Digit OTP</label>
                    <input type="text" name="otp" required maxlength="6" style="font-size: 1.5rem; letter-spacing: 5px; text-align: center;">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Verify & Confirm Withdrawal</button>
            </form>
            <p style="text-align:center; margin-top:1.5rem; color:var(--text-muted);">
                Entered wrong details? <a href="withdraw.php">Cancel Request</a>
            </p>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
