<?php
require_once 'includes/db.php';
$conn->query("CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$conn->query("INSERT INTO faqs (question, answer, category) VALUES 
('How to deposit?', 'Go to Investment Plans, select a plan, and upload your payment proof.', 'Payments'),
('What is the minimum withdrawal?', 'You can check the minimum limit in the withdrawal section. It is set by the administrator.', 'Withdrawals'),
('How long does KYC take?', 'Usually, it takes 24-48 hours for our team to verify your documents.', 'KYC')");
echo "FAQ Table Ready!";
?>
