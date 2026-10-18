
<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/vendor/autoload.php';

function sendAppEmail($recipient, $subject, $message)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'be53ef001@smtp-brevo.com';
        $mail->Password   = 'xsmtpsib-39d4032aa5d707e7c3f629437517a4720315df6b0abb564ba03d572b123bfea4-11Lwevb5pFt1TTOH';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->CharSet = 'UTF-8';

        // Gunakan alamat pengirim yang sudah diverifikasi di Brevo
        $mail->setFrom(
            'aryaanputramuh@gmail.com',
            'LoginSignup'
        );

        $mail->addAddress($recipient);
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;

    } catch (\Throwable $e) {
        error_log('Email delivery failed: ' . $e->getMessage());
        return false;
    }
}
