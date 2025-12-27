<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Email Sender</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Bulk Email Sender with SMTP</h1>
        <form method="POST" action="">
            <label for="subject">Email Subject:</label>
            <input type="text" id="subject" name="subject" required placeholder="Subject">

            <label for="emails">Email List (one per line):</label>
            <textarea id="emails" name="emails" rows="5" required></textarea>

            <label for="message">Email Message (HTML allowed):</label>
            <textarea id="message" name="message" rows="10" required></textarea>

            <button type="submit">Send Emails</button>
        </form>

        <?php
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        require 'vendor/autoload.php';
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $smtp_host = "your_host";
            $smtp_username = "your_emain";
            $smtp_password = "your_password";
            $smtp_port = 587; 
            $smtp_encryption = PHPMailer::ENCRYPTION_STARTTLS; 
            $from_email = "your_emain";
            $from_name = "subject_emain";

            $subject = $_POST['subject'];
            $emails = explode("\n", trim($_POST['emails']));
            $message = $_POST['message'];

            $mailer = new PHPMailer(true);

            $mailer->SMTPDebug = 0;
            try {
                $mailer->CharSet = "UTF-8";
                $mailer->Encoding = 'base64';

                // SMTP settings
                $mailer->isSMTP();
                $mailer->Host = $smtp_host;
                $mailer->SMTPAuth = true;
                $mailer->Username = $smtp_username;
                $mailer->Password = $smtp_password;
                $mailer->SMTPSecure = $smtp_encryption;
                $mailer->Port = $smtp_port;

                // Sender & Reply-To
                $mailer->setFrom($from_email, $from_name);
                $mailer->addReplyTo($from_email, $from_name);

                // Content
                $mailer->isHTML(true);
                $mailer->Subject = $subject;

                // Better HTML Body Structure
                $email_html_content = '
                <!DOCTYPE html>
                <html lang="fa" dir="rtl">
                <head>
                    <meta charset="UTF-8">
                    <style>
                        body { font-family: Tahoma, Arial, sans-serif; direction: rtl; text-align: right; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                        .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div style="margin-bottom: 20px;">
                            ' . nl2br($message) . '
                        </div>
                        <div class="footer">
                            &copy; ' . date("Y") . ' ' . $from_name . '
                        </div>
                    </div>
                </body>
                </html>';

                $mailer->Body = $email_html_content;
                $mailer->AltBody = strip_tags($message);

                $sent_emails = [];
                $failed_emails = [];

                foreach ($emails as $email) {
                    $email = trim($email);
                    if (!empty($email)) {
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            try {
                                $mailer->clearAddresses();
                                $mailer->addAddress($email);
                                $mailer->send();
                                $sent_emails[] = $email;
                            } catch (Exception $e) {
                                $failed_emails[] = $email . " (Error: " . $mailer->ErrorInfo . ")";
                            }
                        } else {
                            $failed_emails[] = $email . " (Invalid Email Format)";
                        }
                    }
                }

                // Display Results
                echo '<div class="status">';
                
                if (!empty($sent_emails)) {
                    echo '<div class="success" style="margin-bottom: 20px;">
                            <h3>✅ Emails Sent Successfully (' . count($sent_emails) . '):</h3>
                            <ul style="list-style-type: none; padding: 0;">';
                    foreach ($sent_emails as $sent) {
                        echo '<li style="padding: 5px 0; border-bottom: 1px solid #eee;">' . htmlspecialchars($sent) . '</li>';
                    }
                    echo '</ul></div>';
                }

                if (!empty($failed_emails)) {
                    echo '<div class="error">
                            <h3>❌ Failed / Remained (' . count($failed_emails) . '):</h3>
                            <ul style="list-style-type: none; padding: 0;">';
                    foreach ($failed_emails as $failed) {
                        echo '<li style="padding: 5px 0; border-bottom: 1px solid #f5c6cb;">' . htmlspecialchars($failed) . '</li>';
                    }
                    echo '</ul></div>';
                }
                
                if (empty($sent_emails) && empty($failed_emails)) {
                    echo '<div class="error">No emails were processed. Please check your input list.</div>';
                }

                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="status error">Setup error: ' . $mailer->ErrorInfo . '</div>';
            }
        }
        ?>
    </div>
</body>
</html>