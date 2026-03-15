<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) {
    die("Unauthorized");
}

$type = $_GET['type'] ?? '';

if ($type === 'users') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=users_export_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'KYC Status', 'Wallet Balance', 'Joined At']);
    
    $res = $conn->query("SELECT id, name, email, phone, kyc_status, wallet_balance, created_at FROM users ORDER BY id DESC");
    while ($row = $res->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
} 
elseif ($type === 'transactions') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=transactions_export_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['TX ID', 'User ID', 'Amount', 'Type', 'Method', 'Status', 'Date']);
    
    $res = $conn->query("SELECT id, user_id, amount, type, payment_method, status, created_at FROM transactions ORDER BY id DESC");
    while ($row = $res->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}
else {
    die("Invalid Export Type");
}
?>
