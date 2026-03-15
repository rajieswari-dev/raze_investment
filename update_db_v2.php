<?php
require_once 'includes/db.php';

echo "<h2>Starting Database Upgrades...</h2>";

// 1. Settings Table
$q1 = "CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
)";
if ($conn->query($q1)) echo "<p>Settings table checked/created.</p>";

$defaults = [
    'min_withdrawal' => '500',
    'referral_bonus' => '100',
    'site_maintenance' => '0'
];

foreach ($defaults as $k => $v) {
    $conn->query("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('$k', '$v')");
}
echo "<p>Default settings inserted.</p>";

// 2. Automated returns: Add columns to track daily payouts
$q2 = "ALTER TABLE investments ADD COLUMN IF NOT EXISTS last_return_date DATE NULL AFTER status";
$conn->query($q2);
$q3 = "ALTER TABLE investments ADD COLUMN IF NOT EXISTS daily_return DECIMAL(10,2) DEFAULT 0 AFTER amount";
$conn->query($q3);
echo "<p>Investment tables updated for automated returns.</p>";

echo "<h3 style='color:green'>Database upgrade complete!</h3>";
?>
