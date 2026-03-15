<?php
require_once 'includes/db.php';
$conn->query("INSERT INTO announcements (message, type) VALUES ('Special Offer! Get 5% extra bonus on investments above ₹50,000!', 'success')");
echo "Announcement added!";
?>
