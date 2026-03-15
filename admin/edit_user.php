<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_balance'])) {
        $amount = floatval($_POST['balance_change']);
        $type = $_POST['change_type']; // 'add' or 'subtract'
        
        if ($type == 'add') {
            $conn->query("UPDATE users SET wallet_balance = wallet_balance + $amount WHERE id = $user_id");
            $msg = "Added ₹$amount to wallet.";
        } else {
            $conn->query("UPDATE users SET wallet_balance = wallet_balance - $amount WHERE id = $user_id");
            $msg = "Subtracted ₹$amount from wallet.";
        }
    }
}

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) die("User not found.");

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">User Details: <?php echo htmlspecialchars($user['name']); ?></h2>

        <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

        <div class="grid">
            <!-- User Basic Info -->
            <div class="card">
                <h3>Basic Information</h3>
                <div style="margin-top:1.5rem;">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p><strong>Joined:</strong> <?php echo date('d M Y, h:i A', strtotime($user['created_at'])); ?></p>
                    <p><strong>KYC Status:</strong> <span class="badge badge-<?php echo ($user['kyc_status']=='approved'?'success':'warning'); ?>"><?php echo ucfirst($user['kyc_status']); ?></span></p>
                    <p><strong>Wallet Balance:</strong> <span style="font-size:1.2rem; font-weight:bold; color:var(--primary);">₹<?php echo number_format($user['wallet_balance'], 2); ?></span></p>
                </div>
            </div>

            <!-- Wallet Management -->
            <div class="card">
                <h3>Wallet Adjustment</h3>
                <form method="POST" style="margin-top:1.5rem;">
                    <div class="form-group">
                        <label>Amount (₹)</label>
                        <input type="number" name="balance_change" step="1" required>
                    </div>
                    <div class="form-group">
                        <label>Action</label>
                        <select name="change_type" required>
                            <option value="add">Add to Balance</option>
                            <option value="subtract">Subtract from Balance</option>
                        </select>
                    </div>
                    <button type="submit" name="update_balance" class="btn btn-primary" style="width:100%;">Apply Changes</button>
                </form>
            </div>
        </div>

        <div class="card" style="margin-top:2rem;">
            <h3>User's Investments</h3>
            <div style="margin-top:1.5rem; overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Returns</th>
                            <th>Status</th>
                            <th>Start Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $invs = $conn->query("SELECT i.*, p.name as plan_name FROM investments i JOIN investment_plans p ON i.plan_id = p.id WHERE i.user_id = $user_id ORDER BY i.created_at DESC");
                        while($i = $invs->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($i['plan_name']); ?></td>
                            <td>₹<?php echo number_format($i['amount'], 2); ?></td>
                            <td>₹<?php echo number_format($i['daily_return'], 2); ?>/day</td>
                            <td><span class="badge badge-<?php echo ($i['status']=='active'?'success':'danger'); ?>"><?php echo $i['status']; ?></span></td>
                            <td><?php echo date('d M Y', strtotime($i['start_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
