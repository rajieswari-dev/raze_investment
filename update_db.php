<?php
require_once 'includes/db.php';

echo "Adding extra features to database...<br>";

// Add Wallet Balance & Referral system
$conn->query("ALTER TABLE users ADD COLUMN wallet_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER kyc_status");
$conn->query("ALTER TABLE users ADD COLUMN referral_code VARCHAR(20) UNIQUE AFTER wallet_balance");
$conn->query("ALTER TABLE users ADD COLUMN referred_by INT NULL AFTER referral_code");

// Ignore error if foreign key already exists
@$conn->query("ALTER TABLE users ADD FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL");

// Create Support Tickets Table
$conn->query("CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Create Ticket Replies Table
$conn->query("CREATE TABLE IF NOT EXISTS ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NULL, 
    admin_id INT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
)");

// Generate referral codes for existing users
$users = $conn->query("SELECT id FROM users WHERE referral_code IS NULL");
while($u = $users->fetch_assoc()) {
    $code = strtoupper(substr(md5(uniqid()), 0, 8));
    $conn->query("UPDATE users SET referral_code = '$code' WHERE id = " . $u['id']);
}

echo "Database updated successfully!<br>";
?>
