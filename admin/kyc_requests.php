<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$msg = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $kyc_id = intval($_GET['id']);
    $action = $_GET['action'] == 'approve' ? 'approved' : 'rejected';
    
    // Get user id
    $stmt = $conn->prepare("SELECT user_id FROM kyc_documents WHERE id = ?");
    $stmt->bind_param("i", $kyc_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($res->num_rows > 0) {
        $user_row = $res->fetch_assoc();
        $u_id = $user_row['user_id'];
        
        $conn->query("UPDATE kyc_documents SET status = '$action' WHERE id = $kyc_id");
        $conn->query("UPDATE users SET kyc_status = '$action' WHERE id = $u_id");
        
        // Notify user in system
        $title = "KYC $action";
        $text = "Your KYC application has been $action.";
        $conn->query("INSERT INTO notifications (user_id, title, message) VALUES ($u_id, '$title', '$text')");

        // Send Email Notification
        $u_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $u_stmt->bind_param("i", $u_id);
        $u_stmt->execute();
        $u_email = $u_stmt->get_result()->fetch_assoc()['email'];
        
        require_once '../includes/mailer.php';
        sendOTP($u_email, "Your KYC Status has been updated to: ".ucfirst($action), "KYC Status Update");

        $msg = "KYC successfully $action.";
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">KYC Requests</h2>

        <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom: 1rem;">Pending Approvals</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>PAN</th>
                            <th>Aadhaar</th>
                            <th>Bank Account</th>
                            <th>IFSC</th>
                            <th>Document</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $reqs = $conn->query("
                            SELECT k.*, u.name 
                            FROM kyc_documents k 
                            JOIN users u ON k.user_id = u.id 
                            WHERE k.status = 'pending' 
                            ORDER BY k.submitted_at ASC
                        ");
                        if($reqs->num_rows > 0):
                            while($r = $reqs->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['name']); ?></td>
                            <td><?php echo htmlspecialchars($r['pan_number']); ?></td>
                            <td><?php echo htmlspecialchars($r['aadhaar_number']); ?></td>
                            <td><?php echo htmlspecialchars($r['bank_account']); ?></td>
                            <td><?php echo htmlspecialchars($r['ifsc_code']); ?></td>
                            <td>
                                <a href="../uploads/<?php echo $r['document_path']; ?>" target="_blank" style="color:var(--accent); text-decoration:underline;">View Doc</a>
                            </td>
                            <td>
                                <div style="display:flex; gap:0.5rem;">
                                    <a href="kyc_requests.php?action=approve&id=<?php echo $r['id']; ?>" class="btn btn-success" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Approve this KYC?');">Approve</a>
                                    <a href="kyc_requests.php?action=reject&id=<?php echo $r['id']; ?>" class="btn btn-danger" style="padding:0.25rem 0.5rem; font-size:0.8rem;" onclick="return confirm('Reject this KYC?');">Reject</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="7" style="text-align:center;">No pending KYC requests.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
