<?php
require_once 'includes/db.php';
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS auto_compound TINYINT DEFAULT 0");
echo "User table updated!";
?>
