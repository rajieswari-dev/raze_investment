<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'raze_investment';

// Dynamic Base URL Detection
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
// If you are hosting on a domain (e.g. example.com), set this to empty. 
// If in a subfolder like /Project/, keep it as /Project
$subfolder = strpos($_SERVER['REQUEST_URI'], '/Project') === 0 ? '/Project' : '';

define('BASE_URL', $protocol . "://" . $host . $subfolder);


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");
}
catch (Exception $e) {
    die("Database connection failed. Please run setup.php first. Error: " . $e->getMessage());
}

function sanitize($conn, $data)
{
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8'));
}

function redirect($url)
{
    header("Location: $url");
    exit();
}

function get_setting($conn, $key, $default = '')
{
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        return $res->fetch_assoc()['setting_value'];
    }
    return $default;
}
?>
