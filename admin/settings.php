<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $settings_to_update = [
        'min_withdrawal' => sanitize($conn, $_POST['min_withdrawal']),
        'referral_bonus' => sanitize($conn, $_POST['referral_bonus']),
        'site_maintenance' => isset($_POST['site_maintenance']) ? '1' : '0',
        'smtp_host' => sanitize($conn, $_POST['smtp_host']),
        'smtp_user' => sanitize($conn, $_POST['smtp_user']),
        'smtp_pass' => sanitize($conn, $_POST['smtp_pass']), // You might want deeper encryption in real app
        'smtp_port' => sanitize($conn, $_POST['smtp_port'])
    ];

    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    foreach ($settings_to_update as $key => $val) {
        $stmt->bind_param("sss", $key, $val, $val);
        $stmt->execute();
    }
    $success = "System settings updated successfully!";
}

$min_withdrawal = get_setting($conn, 'min_withdrawal', '500');
$referral_bonus = get_setting($conn, 'referral_bonus', '100');
$site_maintenance = get_setting($conn, 'site_maintenance', '0');
$smtp_host = get_setting($conn, 'smtp_host', 'smtp.gmail.com');
$smtp_user = get_setting($conn, 'smtp_user', '');
$smtp_pass = get_setting($conn, 'smtp_pass', '');
$smtp_port = get_setting($conn, 'smtp_port', '587');

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">System Settings</h2>

        <div class="card" style="max-width: 800px;">
            <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <div class="grid">
                    <div>
                        <h3 style="margin-bottom:1rem; color:var(--accent);">Platform Rules</h3>
                        <div class="form-group">
                            <label>Minimum Withdrawal Amount (₹)</label>
                            <input type="number" name="min_withdrawal" value="<?php echo htmlspecialchars($min_withdrawal); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Referral Bonus Amount (₹)</label>
                            <input type="number" name="referral_bonus" value="<?php echo htmlspecialchars($referral_bonus); ?>" required>
                        </div>
                        <div class="form-group" style="padding:1rem; border:1px solid rgba(255,255,255,0.1); border-radius:8px; background:rgba(0,0,0,0.2);">
                            <label style="display:flex; align-items:center; cursor:pointer;">
                                <input type="checkbox" name="site_maintenance" value="1" <?php if($site_maintenance == '1') echo 'checked'; ?> style="width:20px; margin-right:10px;">
                                <strong>Enable Site Maintenance Mode</strong>
                            </label>
                            <small style="color:var(--text-muted); display:block; margin-top:0.5rem; margin-left:30px;">This will stop users from interacting with the app dynamically.</small>
                        </div>
                    </div>

                    <div>
                        <h3 style="margin-bottom:1rem; color:var(--accent);">SMTP Email Server (PHPMailer)</h3>
                        <div class="form-group">
                            <label>SMTP Host</label>
                            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($smtp_host); ?>">
                        </div>
                        <div class="form-group">
                            <label>SMTP Port</label>
                            <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($smtp_port); ?>">
                        </div>
                        <div class="form-group">
                            <label>SMTP Username (Email ID)</label>
                            <input type="text" name="smtp_user" value="<?php echo htmlspecialchars($smtp_user); ?>">
                        </div>
                        <div class="form-group">
                            <label>SMTP App Password</label>
                            <input type="text" name="smtp_pass" value="<?php echo htmlspecialchars($smtp_pass); ?>">
                        </div>
                    </div>
                </div>

                <div style="margin-top:2rem;">
                    <button type="submit" name="update_settings" class="btn btn-primary" style="width:100%;">Save All Settings</button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
