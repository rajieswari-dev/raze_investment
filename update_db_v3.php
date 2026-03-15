<?php
require_once 'includes/db.php';

echo "<h2>Starting Security & Activity Database Upgrades...</h2>";

// 1. Create Login Activity Table
$q1 = "CREATE TABLE IF NOT EXISTS login_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    admin_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'failed') DEFAULT 'success'
)";
if ($conn->query($q1)) echo "<p>Login activity table created.</p>";

// 2. Add last_login columns to users and admins
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45) NULL");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL");
$conn->query("ALTER TABLE admins ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45) NULL");
$conn->query("ALTER TABLE admins ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL");

// 3. Create News/Announcements table
$q2 = "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success') DEFAULT 'info',
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($q2)) echo "<p>Announcements table created.</p>";

// 4. Update Deposits Table for Screenshot feature
$q3 = "ALTER TABLE transactions ADD COLUMN IF NOT EXISTS proof_screenshot VARCHAR(255) NULL AFTER reference_id";
$conn->query($q3);
echo "<p>Transactions table updated for Proof Uploads.</p>";

// 5. Create FAQs table
$q4 = "CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($q4);

// Seed some initial FAQs
$conn->query("INSERT IGNORE INTO faqs (question, answer, category) VALUES 
('How to deposit?', 'Go to Investment Plans, select a plan, and upload your payment proof.', 'Payments'),
('What is the minimum withdrawal?', 'You can check the minimum limit in the withdrawal section. It is set by the administrator.', 'Withdrawals'),
('How long does KYC take?', 'Usually, it takes 24-48 hours for our team to verify your documents.', 'KYC')");

echo "<h3 style='color:green'>Security & Deposit Database upgrade complete!</h3>";

?>
