<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

$user_stmt = $conn->prepare("SELECT kyc_status FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$kyc_status = $user_stmt->get_result()->fetch_assoc()['kyc_status'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && in_array($kyc_status, ['unsubmitted', 'rejected'])) {
    
    $pan = sanitize($conn, $_POST['pan_number']);
    $aadhaar = sanitize($conn, $_POST['aadhaar_number']);
    $bank_acc = sanitize($conn, $_POST['bank_account']);
    $ifsc = sanitize($conn, $_POST['ifsc_code']);

    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $filename = time() . '_' . $user_id . '.' . $ext;
            $upload_path = '../uploads/' . $filename;
            
            if (move_uploaded_file($_FILES['document']['tmp_name'], $upload_path)) {
                // Remove existing if any
                $conn->query("DELETE FROM kyc_documents WHERE user_id = $user_id");

                $stmt = $conn->prepare("INSERT INTO kyc_documents (user_id, pan_number, aadhaar_number, bank_account, ifsc_code, document_path) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssss", $user_id, $pan, $aadhaar, $bank_acc, $ifsc, $filename);
                
                if ($stmt->execute()) {
                    $conn->query("UPDATE users SET kyc_status = 'pending' WHERE id = $user_id");
                    $kyc_status = 'pending';
                    $success = "KYC Details submitted successfully. Please wait for admin approval.";
                } else {
                    $error = "Database Error.";
                }
            } else {
                $error = "Failed to upload document.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG and PDF allowed.";
        }
    } else {
        $error = "Please upload a valid document.";
    }
}

$kyc_data = null;
if ($kyc_status != 'unsubmitted') {
    $kyc_stmt = $conn->query("SELECT * FROM kyc_documents WHERE user_id = $user_id ORDER BY id DESC LIMIT 1");
    if ($kyc_stmt->num_rows > 0) {
        $kyc_data = $kyc_stmt->fetch_assoc();
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">KYC Verification</h2>

        <div class="card" style="max-width: 800px;">
            <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

            <?php if($kyc_status == 'pending'): ?>
                <div class="alert alert-success">Your KYC application is currently under review.</div>
                <div class="grid">
                    <div>
                        <p><strong>PAN:</strong> <?php echo htmlspecialchars($kyc_data['pan_number']); ?></p>
                        <p><strong>Aadhaar:</strong> <?php echo htmlspecialchars($kyc_data['aadhaar_number']); ?></p>
                    </div>
                </div>
            <?php elseif($kyc_status == 'approved'): ?>
                <div class="alert alert-success">Your KYC is VERIFIED AND APPROVED. You can now invest.</div>
                <div class="grid">
                    <div>
                        <p><strong>PAN:</strong> <?php echo htmlspecialchars($kyc_data['pan_number']); ?></p>
                        <p><strong>Aadhaar:</strong> <?php echo htmlspecialchars($kyc_data['aadhaar_number']); ?></p>
                        <p><strong>Bank Account:</strong> XXXXX<?php echo substr($kyc_data['bank_account'], -4); ?></p>
                    </div>
                </div>
            <?php else: // unsubmitted or rejected ?>
                <?php if($kyc_status == 'rejected'): ?>
                    <div class="alert alert-error">Your previous application was REJECTED. Please check your details and resubmit.</div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="grid">
                        <div class="form-group">
                            <label>PAN Number</label>
                            <input type="text" name="pan_number" required style="text-transform: uppercase;">
                        </div>
                        <div class="form-group">
                            <label>Aadhaar Number</label>
                            <input type="text" name="aadhaar_number" required>
                        </div>
                        <div class="form-group">
                            <label>Bank Account Number</label>
                            <input type="text" name="bank_account" required>
                        </div>
                        <div class="form-group">
                            <label>Bank IFSC Code</label>
                            <input type="text" name="ifsc_code" required style="text-transform: uppercase;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Upload Document (ID Proof/Bank Passbook)</label>
                        <input type="file" name="document" accept=".png, .jpg, .jpeg, .pdf" required>
                        <small style="color:var(--text-muted)">Max size 2MB. JPG, PNG, PDF only.</small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit KYC Details</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
