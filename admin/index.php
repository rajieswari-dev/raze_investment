<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) {
    redirect('../login.php');
} else {
    redirect('dashboard.php');
}
?>
