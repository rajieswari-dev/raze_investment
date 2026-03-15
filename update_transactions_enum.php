<?php
require_once 'includes/db.php';
$conn->query("ALTER TABLE transactions MODIFY COLUMN type ENUM('investment', 'withdrawal', 'return', 'referral_bonus', 'compound') NOT NULL");
echo "Transactions ENUM updated!";
?>
