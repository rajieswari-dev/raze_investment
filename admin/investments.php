<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$msg = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'] == 'complete' ? 'completed' : 'cancelled';
    
    // Get user id and amount
    $inv = $conn->query("SELECT user_id, amount FROM investments WHERE id = $id")->fetch_assoc();
    if($inv) {
        $u_id = $inv['user_id'];
        $amt = $inv['amount'];
        $conn->query("UPDATE investments SET status = '$action' WHERE id = $id");
        
        // Notify user
        $title = "Investment " . ucfirst($action);
        $text = "Your investment of ₹" . $amt . " has been marked as $action.";
        if($action == 'completed') {
            // we could generate a return transaction here
            $text .= " Check your transactions for returns.";
        }
        $conn->query("INSERT INTO notifications (user_id, title, message) VALUES ($u_id, '$title', '$text')");

        $msg = "Investment $action successfully.";
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Monitor Investments</h2>

        <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom: 1rem;">Active Investments</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Start Due</th>
                            <th>End Due</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $invs = $conn->query("
                            SELECT i.*, u.name as user_name, p.name as plan_name 
                            FROM investments i 
                            JOIN users u ON i.user_id = u.id 
                            JOIN investment_plans p ON i.plan_id = p.id 
                            ORDER BY i.created_at DESC
                        ");
                        while($i = $invs->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($i['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($i['plan_name']); ?></td>
                            <td>₹<?php echo $i['amount']; ?></td>
                            <td><?php echo date('d M Y', strtotime($i['start_date'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($i['end_date'])); ?></td>
                            <td>
                                <span class="badge badge-<?php echo ($i['status']=='active'?'success':($i['status']=='completed'?'success':'danger')); ?>">
                                    <?php echo ucfirst($i['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($i['status'] == 'active'): ?>
                                <div style="display:flex; gap:0.5rem;">
                                    <a href="investments.php?action=complete&id=<?php echo $i['id']; ?>" class="btn btn-success" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Mark as completed?');">Complete</a>
                                    <a href="investments.php?action=cancel&id=<?php echo $i['id']; ?>" class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Cancel investment?');">Cancel</a>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
