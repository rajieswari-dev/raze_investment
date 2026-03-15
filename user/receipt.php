<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

$user_id = $_SESSION['user_id'];
$tx_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch transaction and user details
$stmt = $conn->prepare("SELECT t.*, u.name as u_name, u.email as u_email, u.phone as u_phone 
                        FROM transactions t 
                        JOIN users u ON t.user_id = u.id 
                        WHERE t.id = ? AND t.user_id = ?");
$stmt->bind_param("ii", $tx_id, $user_id);
$stmt->execute();
$tx = $stmt->get_result()->fetch_assoc();

if(!$tx) die("Receipt not found or access denied.");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt_#<?php echo $tx['id']; ?></title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; color: #333; margin: 0; padding: 40px; }
        .receipt-box { max-width: 800px; margin: auto; border: 1px solid #eee; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #4F46E5; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #4F46E5; }
        .receipt-title { font-size: 20px; color: #666; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .info-section h4 { margin: 0 0 10px 0; color: #4F46E5; text-transform: uppercase; font-size: 12px; }
        .info-section p { margin: 2px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid #eee; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .total-section { text-align: right; }
        .total-row { font-size: 18px; font-weight: bold; color: #4F46E5; }
        .footer { text-align: center; margin-top: 50px; color: #999; font-size: 12px; }
        .btn-print { background: #4F46E5; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin-bottom: 20px; }
        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
            .receipt-box { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

    <div style="text-align: center;">
        <button class="btn-print" onclick="window.print()">Download / Print Receipt</button>
    </div>

    <div class="receipt-box">
        <div class="header">
            <div class="logo">RAZE INVESTMENT</div>
            <div class="receipt-title">Official Receipt</div>
        </div>

        <div class="info-grid">
            <div class="info-section">
                <h4>From:</h4>
                <p><strong>Raze Investment Ltd.</strong></p>
                <p>Corporate Office, Financial District</p>
                <p>Mumbai, Maharashtra, India</p>
                <p>Email: support@razeinvestment.com</p>
            </div>
            <div class="info-section">
                <h4>To:</h4>
                <p><strong><?php echo htmlspecialchars($tx['u_name']); ?></strong></p>
                <p>Email: <?php echo htmlspecialchars($tx['u_email']); ?></p>
                <p>Phone: <?php echo htmlspecialchars($tx['u_phone']); ?></p>
            </div>
        </div>

        <div class="info-grid" style="margin-bottom:20px;">
            <div class="info-section">
                <h4>Transaction Details:</h4>
                <p>Receipt ID: #<?php echo $tx['id']; ?></p>
                <p>Reference: <?php echo htmlspecialchars($tx['reference_id'] ?: 'N/A'); ?></p>
                <p>Date: <?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?></p>
            </div>
            <div class="info-section">
                <h4>Status:</h4>
                <p style="color: <?php echo $tx['status'] == 'success' ? '#10B981' : '#F59E0B'; ?>; font-weight:bold;">
                    <?php echo strtoupper($tx['status']); ?>
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Payment Method</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo ucfirst($tx['type']); ?> Transaction</td>
                    <td><?php echo strtoupper($tx['payment_method']); ?></td>
                    <td style="text-align: right;">₹<?php echo number_format($tx['amount'], 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            <p>Subtotal: ₹<?php echo number_format($tx['amount'], 2); ?></p>
            <p class="total-row">Total: ₹<?php echo number_format($tx['amount'], 2); ?></p>
        </div>

        <div class="footer">
            <p>This is a computer-generated receipt and does not require a physical signature.</p>
            <p>Thank you for choosing Raze Investment!</p>
        </div>
    </div>

</body>
</html>
