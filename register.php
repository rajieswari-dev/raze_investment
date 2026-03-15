<?php
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) redirect('user/dashboard.php');
if (isset($_SESSION['admin_id'])) redirect('admin/dashboard.php');

$error = '';
$success = '';

$site_maintenance = get_setting($conn, 'site_maintenance', '0');
if ($site_maintenance == '1') {
    die("<h1 style='color:white; text-align:center; margin-top:50px;'>Site is currently under maintenance. Please check back later.</h1>");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $phone = sanitize($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (!isset($_POST['terms'])) {
        $error = "You must agree to the Terms and Conditions!";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ref_code = strtoupper(substr(md5(uniqid()), 0, 8));
            $referred_by_id = null;

            if (!empty($_POST['referral_code'])) {
                $ref = sanitize($conn, $_POST['referral_code']);
                $r_user = $conn->query("SELECT id FROM users WHERE referral_code = '$ref'");
                if ($r_user->num_rows > 0) {
                    $referred_by_id = $r_user->fetch_assoc()['id'];
                }
            }

            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $name, $email, $phone, $hashed, $ref_code, $referred_by_id);
            if ($stmt->execute()) {
                // If referred by someone, maybe give the referrer a bonus? (Optional feature)
                if ($referred_by_id !== null) {
                    $ref_bonus = get_setting($conn, 'referral_bonus', 100);
                    $conn->query("UPDATE users SET wallet_balance = wallet_balance + $ref_bonus WHERE id = $referred_by_id");
                }
                
                require_once 'includes/mailer.php';
                sendOTP($email, "Welcome to Raze Investment!", "Registration Success"); // Reused mailer for welcome email

                $success = "Registration successful! Please login.";
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container card">
    <h2 style="text-align:center; margin-bottom: 2rem;">Create an Account</h2>
    
    <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <div class="form-group">
            <label>Referral Code (Optional)</label>
            <input type="text" name="referral_code" value="<?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ''; ?>">
        </div>
        <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-bottom: 1.5rem;">
            <input type="checkbox" name="terms" id="terms" required style="width:18px; height:18px; cursor:pointer;">
            <label for="terms" style="margin-bottom:0; cursor:pointer; font-size: 0.85rem; color: var(--text-muted);">
                I agree to the <a href="terms.php" target="_blank" style="color:var(--primary); text-decoration:underline;">Terms</a> and <a href="privacy.php" target="_blank" style="color:var(--primary); text-decoration:underline;">Privacy Policy</a>
            </label>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
    </form>
    <p style="text-align:center; margin-top:1.5rem; color:var(--text-muted);">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
