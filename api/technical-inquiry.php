<?php
// 關鍵修改 #1：使用 Composer 的自動載入器，替換所有舊的 require 語句
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. 清理和驗證輸入 (這部分邏輯是正確的，保持不變)
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $company = isset($_POST["company"]) ? strip_tags(trim($_POST["company"])) : '';
    $message = strip_tags(trim($_POST["message"]));

    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: /contact/error.html'); // 使用根相對路徑
        exit("Invalid input.");
    }

    $mail = new PHPMailer(true);

    try {
        // 關鍵修改 #2：將所有 $config[...] 替換為 getenv(...)
        $mail->isSMTP();
        $mail->CharSet    = 'UTF-8';
        $mail->Host       = getenv('SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME');
        $mail->Password   = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int)getenv('SMTP_PORT');

        // --- 1. 發送通知郵件給管理員 ---
        $mail->setFrom(getenv('FROM_EMAIL'), $name . ' (Website Inquiry)');
        $mail->addAddress(getenv('FROM_EMAIL'), 'Catherine Zhang');
        $mail->addReplyTo($email, $name);

        // 附件處理邏輯 (這部分是正確的，保持不變)
        if (isset($_FILES['drawing']) && $_FILES['drawing']['error'] == UPLOAD_ERR_OK) {
            $file_size = $_FILES['drawing']['size'];
            $file_name = $_FILES['drawing']['name'];
            $file_tmp_name = $_FILES['drawing']['tmp_name'];
            $allowed_extensions = ['pdf', 'dwg', 'dxf', 'step', 'stp', 'iges', 'igs', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_size <= 5 * 1024 * 1024 && in_array($file_extension, $allowed_extensions)) {
                $mail->addAttachment($file_tmp_name, $file_name);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = "New Technical Inquiry from {$name}" . ($company ? " ({$company})" : "");
        $mail->Body = "<strong>Name:</strong> " . nl2br(htmlspecialchars($name)) . "<br>" .
                      "<strong>Email:</strong> " . htmlspecialchars($email) . "<br>" .
                      "<strong>Company:</strong> " . nl2br(htmlspecialchars($company)) . "<br>" .
                      "<strong>Message:</strong><br>" . nl2br(htmlspecialchars($message));
        $mail->send();

        // --- 2. 發送自動回信給客戶 ---
        $mail->clearAllRecipients();
        $mail->clearAttachments();
        $mail->clearReplyTos();

        $mail->setFrom(getenv('FROM_EMAIL'), getenv('REPLY_TO_NAME') . ' | Gorgeo Fasteners');
        $mail->addAddress($email, $name);
        $mail->Subject = "Confirmation: We've received your inquiry [Analysis in Progress]";
        
        $signature = "Best regards,<br>
<strong>Catherine Zhang</strong><br>
<span>Senior Assembly Fit Consultant</span><br>
<span>Structural Fit Reliability · ±0.01 mm</span><br>
<span>Gorgeo Fasteners | Sleeves · Pins · Locator Bolts</span>";
        
        $autoReplyBody = "
        <div style='font-family: Calibri, sans-serif; font-size: 11pt; color: #333; line-height: 1.5;'>
            <p>Hi " . htmlspecialchars($name) . ",</p>
            <p>This is an automatic confirmation that we have successfully received your inquiry and any attached drawings. Thank you for reaching out.</p>
            <p>Our engineering team will personally review your message and get back to you within one business day. Please rest assured that all submitted files are handled with complete confidentiality.</p>
            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
            <p><strong>While you wait, explore how we solve similar challenges:</strong></p>
            <ul style='padding-left: 0; list-style: none;'>
                <li style='margin-bottom: 10px;'>
                    <a href='https://www.gorgeofasteners.com/blog/vibration-loosening-fix/vibration-loosening-fix.html' style='color: #007bff; text-decoration: none;'>
                        <strong>Case Study: Fixing Chronic Vibration Loosening</strong><br>
                        <span style='color: #555; font-size: 0.9em;'>How we use structural geometry, not just torque, to create joints that never back out.</span>
                    </a>
                </li>
                <li>
                    <a href='https://www.gorgeofasteners.com/blog/coating-induced-jam-fit/coating-induced-jam-fit.html' style='color: #007bff; text-decoration: none;'>
                        <strong>Teardown: When a 0.05mm Coating Jams Assembly</strong><br>
                        <span style='color: #555; font-size: 0.9em;'>Dissecting how an unmodeled finish layer can turn a perfect CAD fit into a production-line failure.</span>
                    </a>
                </li>
            </ul>
            <br>
            <p>{$signature}</p>
            <p style='font-size: 0.85em; color: #777; margin-top: 25px;'>
                P.S. If you need to add any information to your inquiry, simply reply to this email. For truly urgent matters, you can find our direct contact details on our website.
            </p>
        </div>";
        
        $mail->Body = $autoReplyBody;

        $mail->send();
        header('Location: /contact/thank-you.html'); // 使用根相對路徑
        exit();

    } catch (Exception $e) {
        error_log("Contact Form Error for {$email}: {$mail->ErrorInfo}");
        header('Location: /contact/error.html');
        exit();
    }
} else {
    http_response_code(403);
    exit("Invalid request method.");
}
?>