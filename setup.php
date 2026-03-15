<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'raze_investment';

// Connect directly to DB since manual creation is needed on hosting
try {
    $conn = new mysqli($host, $user, $pass, $dbname);
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . ". Make sure the database '$dbname' exists in cPanel.");
}

echo "Connected successfully to $dbname<br>";

$queries = [
    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        kyc_status ENUM('unsubmitted', 'pending', 'approved', 'rejected') DEFAULT 'unsubmitted',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS kyc_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        pan_number VARCHAR(20) NOT NULL,
        aadhaar_number VARCHAR(20) NOT NULL,
        bank_account VARCHAR(30) NOT NULL,
        ifsc_code VARCHAR(20) NOT NULL,
        document_path VARCHAR(255) NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS investment_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        min_amount DECIMAL(15,2) NOT NULL DEFAULT 100000.00,
        roi_percentage DECIMAL(5,2) NOT NULL,
        duration_months INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS investments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (plan_id) REFERENCES investment_plans(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        type ENUM('deposit', 'withdrawal', 'investment', 'return') NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        reference_id VARCHAR(100),
        status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        title VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

foreach ($queries as $q) {
    if ($conn->query($q) === TRUE) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Insert admin
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$check_admin = $conn->query("SELECT * FROM admins WHERE username = 'admin'");
if ($check_admin->num_rows == 0) {
    if ($conn->query("INSERT INTO admins (username, password) VALUES ('admin', '$admin_pass')") === TRUE) {
        echo "Admin created: admin / admin123<br>";
    }
}

// Insert sample investment plan
$check_plan = $conn->query("SELECT * FROM investment_plans LIMIT 1");
if ($check_plan->num_rows == 0) {
    $conn->query("INSERT INTO investment_plans (name, min_amount, roi_percentage, duration_months) VALUES ('Premium Wealth Plan', 100000.00, 12.5, 12)");
    $conn->query("INSERT INTO investment_plans (name, min_amount, roi_percentage, duration_months) VALUES ('Elite Growth Plan', 500000.00, 15.0, 24)");
    echo "Sample plans created<br>";
}

echo "<br><a href='index.php'>Go to Home</a>";
$conn->close();
?>
