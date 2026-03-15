<?php
require_once 'includes/db.php';
require_once 'includes/mailer.php';

if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['login_otp'])) {
    redirect('login.php');
}

$error = '';
$success = "An OTP has been sent to " . htmlspecialchars($_SESSION['temp_email']) . ". Please check your inbox (or spam folder) and verify your login.";

// Handle Resend OTP
if (isset($_GET['resend'])) {
    $otp = rand(100000, 999999);
    $_SESSION['login_otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + (10 * 60);
    sendOTP($_SESSION['temp_email'], $otp, "Login Verification (Resent)");
    $success = "A new OTP has been sent successfully!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = trim($_POST['otp']);

    if (time() > $_SESSION['otp_expiry']) {
        $error = "OTP has expired. Please go back and login again.";
    } elseif ($entered_otp == $_SESSION['login_otp']) {
        // OTP matched -> Grant access
        $u_id = $_SESSION['temp_user_id'];
        $_SESSION['user_id'] = $u_id;
        $_SESSION['kyc_status'] = $_SESSION['temp_kyc_status'];
        
        // --- Security Log: Track Activity ---
        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $conn->query("INSERT INTO login_activity (user_id, ip_address, user_agent, status) VALUES ($u_id, '$ip', '$ua', 'success')");
        $conn->query("UPDATE users SET last_login_ip = '$ip', last_login_at = CURRENT_TIMESTAMP WHERE id = $u_id");
        // ------------------------------------

        // Remove temp session data
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_kyc_status']);
        unset($_SESSION['temp_email']);
        unset($_SESSION['login_otp']);
        unset($_SESSION['otp_expiry']);

        redirect('user/dashboard.php');
    } else {
        $error = "Invalid OTP. Please try again.";
        $success = ''; // Clear success message so they focus on the error
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container card">
    <h2 style="text-align:center; margin-bottom: 2rem;">2-Factor Authentication</h2>
    
    <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Enter 6-Digit OTP</label>
            <input type="text" name="otp" required maxlength="6" style="font-size: 1.5rem; letter-spacing: 5px; text-align: center;">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Verify & Login</button>
    </form>
    <p style="text-align:center; margin-top:1.5rem; color:var(--text-muted);">
        Didn't receive the email? <a href="verify_otp.php?resend=1" style="color:var(--accent);">Resend OTP</a> or <a href="login.php" style="margin-left:5px;">Go back</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
