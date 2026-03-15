<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_plan'])) {
    $name = sanitize($conn, $_POST['name']);
    $min_amount = floatval($_POST['min_amount']);
    $roi = floatval($_POST['roi_percentage']);
    $duration = intval($_POST['duration_months']);

    $stmt = $conn->prepare("INSERT INTO investment_plans (name, min_amount, roi_percentage, duration_months) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddi", $name, $min_amount, $roi, $duration);
    if ($stmt->execute()) {
        $msg = "Plan added successfully.";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM investment_plans WHERE id = $id");
    redirect('plans.php');
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Manage Investment Plans</h2>

        <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

        <div class="grid">
            <div class="card">
                <h3>Create New Plan</h3>
                <form method="POST" style="margin-top: 1.5rem;">
                    <div class="form-group">
                        <label>Plan Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Minimum Amount (₹)</label>
                        <input type="number" name="min_amount" step="1" required>
                    </div>
                    <div class="form-group">
                        <label>ROI (%)</label>
                        <input type="number" name="roi_percentage" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Duration (Months)</label>
                        <input type="number" name="duration_months" required>
                    </div>
                    <button type="submit" name="add_plan" class="btn btn-primary" style="width:100%;">Add Plan</button>
                </form>
            </div>
            
            <div class="card">
                <h3>Existing Plans</h3>
                <div style="margin-top:1.5rem; overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Min Amt</th>
                                <th>ROI</th>
                                <th>Months</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $plans = $conn->query("SELECT * FROM investment_plans ORDER BY min_amount ASC");
                            while($p = $plans->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                <td>₹<?php echo $p['min_amount']; ?></td>
                                <td><?php echo $p['roi_percentage']; ?>%</td>
                                <td><?php echo $p['duration_months']; ?></td>
                                <td>
                                    <a href="plans.php?delete=<?php echo $p['id']; ?>" class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Delete plan?');">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
