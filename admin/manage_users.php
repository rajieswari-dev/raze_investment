<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $id");
    redirect('manage_users.php');
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Manage Users</h2>

        <div class="card">
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>KYC Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = $conn->query("SELECT * FROM users ORDER BY id DESC");
                        while($u = $users->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo ($u['kyc_status']=='approved'?'success':($u['kyc_status']=='pending'?'warning':'danger')); ?>">
                                    <?php echo ucfirst($u['kyc_status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-primary" style="padding:0.25rem 0.5rem; font-size:0.8rem;">Manage</a>
                                <a href="manage_users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Delete this user completely?');">Delete</a>
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
