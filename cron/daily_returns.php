<?php
// To be executed via an OS cron job
require_once __DIR__ . '/../includes/db.php';

echo "Running Daily Returns Script...\n";

// Get today's date
$today = date('Y-m-d');

// Find active investments that haven't received a return today
$stmt = $conn->query("SELECT i.id, i.user_id, i.amount, p.roi_percentage, p.duration_months, i.last_return_date, i.start_date, i.end_date, u.auto_compound 
                      FROM investments i 
                      JOIN investment_plans p ON i.plan_id = p.id 
                      JOIN users u ON i.user_id = u.id
                      WHERE i.status = 'active' AND (i.last_return_date IS NULL OR i.last_return_date < '$today')
                      AND i.end_date >= '$today'");

if ($stmt->num_rows == 0) {
    echo "No pending returns for today.\n";
    exit();
}

$count = 0;
while ($inv = $stmt->fetch_assoc()) {
    $inv_id = $inv['id'];
    $user_id = $inv['user_id'];
    
    // Formula: Total expected return = Amount * (ROI / 100)
    // Daily return = Total expected return / (Duration in months * 30 days)
    $total_duration_days = $inv['duration_months'] * 30; // Approximation
    $total_return = $inv['amount'] * ($inv['roi_percentage'] / 100);
    $daily = round($total_return / $total_duration_days, 2);

    // Payout Logic
    if ($inv['auto_compound']) {
        // ADD TO PRINCIPAL (Compounding)
        $conn->query("UPDATE investments SET amount = amount + $daily WHERE id = $inv_id");
        $pay_type = 'compound';
    } else {
        // ADD TO WALLET (Standard)
        $conn->query("UPDATE users SET wallet_balance = wallet_balance + $daily WHERE id = $user_id");
        $pay_type = 'return';
    }
    
    // Log transaction
    $t_stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, payment_method, status) VALUES (?, ?, ?, 'System Cron', 'success')");
    $t_stmt->bind_param("ids", $user_id, $daily, $pay_type);
    $t_stmt->execute();

    // Update investment last_return_date
    $conn->query("UPDATE investments SET last_return_date = '$today', daily_return = '$daily' WHERE id = $inv_id");
    
    $count++;
}

echo "Successfully processed $count daily returns for $today.\n";
?>
