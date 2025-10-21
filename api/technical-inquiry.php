<?php
// 步骤 1：使用 Composer 的自动加载器
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $company = isset($_POST["company"]) ? strip_tags(trim($_POST["company"])) : '';
    $message = strip_tags(trim($_POST["message"]));

    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: /contact/error.html'); // 使用根相对路径
        exit("Invalid input.");
    }

    $mail = new PHPMailer(true);

    try {
        // 步骤 2：使用环境变量
        $mail->isSMTP();
        $mail->CharSet    = 'UTF-8';
        $mail->Host       = $_SERVER['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_SERVER['SMTP_USERNAME'];
        $mail->Password   = $_SERVER['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int)$_SERVER['SMTP_PORT'];

        // --- 1. 发送通知邮件给管理员 ---
        $mail->setFrom($_SERVER['FROM_EMAIL'], $name . ' (Website Inquiry)');
        $mail->addAddress($_SERVER['FROM_EMAIL'], 'Catherine Zhang');
        $mail->addReplyTo($email, $name);

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

        // --- 2. 发送自动回信给客户 ---
        $mail->clearAllRecipients();
        $mail->clearAttachments();
        $mail->clearReplyTos();

        $mail->setFrom($_SERVER['FROM_EMAIL'], $_SERVER['REPLY_TO_NAME'] . ' | Gorgeo Fasteners');
        $mail->addAddress($email, $name);
        $mail->Subject = "Confirmation: We've received your inquiry [Analysis in Progress]";
        
        $signature = "Best regards,<br><strong>Catherine Zhang</strong><br><span>Senior Assembly Fit Consultant</span><br><span>Structural Fit Reliability · ±0.01 mm</span><br><span>Gorgeo Fasteners | Sleeves · Pins · Locator Bolts</span>";
        $autoReplyBody = " ... "; // 您的自动回复邮件正文，保持不变
        $mail->Body = $autoReplyBody; // 请确保 $autoReplyBody 变量已定义

        $mail->send();
        header('Location: /contact/thank-you.html');
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