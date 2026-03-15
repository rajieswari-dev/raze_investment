<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']))
    redirect('../login.php');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitize($conn, $_POST['name']);
        $phone = sanitize($conn, $_POST['phone']);
        $auto_compound = isset($_POST['auto_compound']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, auto_compound = ? WHERE id = ?");
        $stmt->bind_param("ssii", $name, $phone, $auto_compound, $user_id);
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
        }
        else {
            $error = "Failed to update profile.";
        }
    }
    elseif (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        // Get user current password hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $curr_hash = $stmt->get_result()->fetch_assoc()['password'];

        if (!password_verify($old_pass, $curr_hash)) {
            $error = "Incorrect old password.";
        }
        elseif ($new_pass !== $confirm_pass) {
            $error = "New passwords do not match.";
        }
        elseif (strlen($new_pass) < 6) {
            $error = "New password must be at least 6 characters.";
        }
        else {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hash, $user_id);
            if ($stmt->execute()) {
                $success = "Password changed successfully!";
            }
            else {
                $error = "Failed to change password.";
            }
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$ref_bonus = get_setting($conn, 'referral_bonus', 100);

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">My Profile</h2>

        <div class="card" style="max-width: 600px;">
            <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php
endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php
endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small style="color:var(--text-muted)">*Email cannot be changed.</small>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Member Since</label>
                    <input type="text" value="<?php echo date('d M Y', strtotime($user['created_at'])); ?>" disabled>
                </div>
                <div class="form-group" style="display:flex; align-items:center; gap:10px; background: rgba(16, 185, 129, 0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.1);">
                    <input type="checkbox" name="auto_compound" id="auto_compound" <?php echo $user['auto_compound'] ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                    <div>
                        <label for="auto_compound" style="margin-bottom:0; cursor: pointer; color:var(--success); font-weight:bold;">🚀 Auto-Compound Daily Returns</label>
                        <p style="font-size: 0.8rem; color:var(--text-muted); margin-bottom:0;">Re-invest your daily profit into your active capital to grow faster!</p>
                    </div>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary" style="margin-top: 1rem;">Update Profile</button>
            </form>
        </div>

        <div class="card" style="max-width: 600px; margin-top: 2rem;">
            <h3>Change Password</h3>
            <form method="POST" style="margin-top: 1.5rem;">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-warning" style="background:var(--warning); color:white;">Change Password</button>
            </form>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
