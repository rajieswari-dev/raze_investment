<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

function sendOTP($to_email, $otp_code, $subject_type)
{
    $mail = new PHPMailer(true);

    try {
        // Fetch settings from DB (requires $conn to be available/global)
        global $conn;
        if (!isset($conn)) {
            require_once __DIR__ . '/db.php';
            global $conn;
        }

        $smtp_host = get_setting($conn, 'smtp_host', 'smtp.gmail.com');
        $smtp_user = get_setting($conn, 'smtp_user', 'sujithaganesan06@gmail.com');
        $smtp_pass = get_setting($conn, 'smtp_pass', 'tbjv rxhe vrmx kkhg');
        $smtp_port = get_setting($conn, 'smtp_port', '587');

        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        // User needs to put their real SMTP details below for production
        $mail->Username = $smtp_user;
        $mail->Password = $smtp_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp_port;

        // Recipients
        $mail->setFrom('no-reply@razeinvestment.com', 'Raze Investment');
        $mail->addAddress($to_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your OTP - $subject_type";
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                <h2>Hello,</h2>
                <p>Your One-Time Password (OTP) for <strong>$subject_type</strong> is:</p>
                <div style='font-size: 24px; font-weight: bold; background: #eee; padding: 10px; display: inline-block; letter-spacing: 5px; color: #4F46E5;'>$otp_code</div>
                <p>Please enter this code to proceed. This OTP is valid for exactly 10 minutes.</p>
                <p>If you did not request this, please secure your account immediately.</p>
                <br>
                <p>Regards,<br><strong>Raze Investment Team</strong></p>
            </div>
        ";

        $mail->send();
        return true;
    }
    catch (Exception $e) {
        // Fallback for local testing without proper SMTP credentials: Log to a file
        file_put_contents(__DIR__ . '/../otp_log.txt', "(" . date('Y-m-d H:i:s') . ") [$to_email] $subject_type OTP: $otp_code \n", FILE_APPEND);
        return false;
    }
}
?>
