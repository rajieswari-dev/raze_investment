<?php
require_once 'includes/db.php';
require_once 'includes/mailer.php';

if (isset($_SESSION['user_id'])) redirect('user/dashboard.php');
if (isset($_SESSION['admin_id'])) redirect('admin/dashboard.php');

$error = '';

$site_maintenance = get_setting($conn, 'site_maintenance', '0');
if ($site_maintenance == '1') {
    $error = "The site is currently under maintenance. Only Administrators can login.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = sanitize($conn, $_POST['identifier']); // email or username
    $password = $_POST['password'];

    // Check admin first
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            redirect('admin/dashboard.php');
        } else {
            $error = "Invalid credentials!";
        }
    } else {
        // Check user
        $stmt = $conn->prepare("SELECT id, password, kyc_status, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $identifier);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            if ($site_maintenance == '1') {
                $error = "User login is disabled during maintenance.";
            } else {
                $user = $res->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Generate 2FA OTP
                    $otp = rand(100000, 999999);
                    $_SESSION['temp_user_id'] = $user['id'];
                    $_SESSION['temp_kyc_status'] = $user['kyc_status'];
                    $_SESSION['temp_email'] = $user['email'];
                    $_SESSION['login_otp'] = $otp;
                    $_SESSION['otp_expiry'] = time() + (10 * 60);

                    sendOTP($user['email'], $otp, "Login Verification");
                    
                    redirect('verify_otp.php');
                } else {
                    $error = "Invalid credentials!";
                }
            }
        } else {
            $error = "User not found!";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container card">
    <h2 style="text-align:center; margin-bottom: 2rem;">Welcome Back</h2>
    
    <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email Address / Username (Admin)</label>
            <input type="text" name="identifier" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="show-pass" onclick="togglePass()" style="width:16px; height:16px; cursor:pointer;">
            <label for="show-pass" style="margin-bottom:0; cursor:pointer; font-size:0.9rem; color:var(--text-muted);">Show Password</label>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
    </form>

    <script>
    function togglePass() {
        var x = document.getElementById("password");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
    </script>
    <p style="text-align:center; margin-top:1.5rem; color:var(--text-muted);">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
