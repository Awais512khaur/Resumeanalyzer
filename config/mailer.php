<?php

function send_password_reset_code(string $email, string $name, string $code): void
{
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('Email support is not installed. Run composer install in the project folder.');
    }
    require_once $autoload;

    $settings = require __DIR__ . '/mail.php';
    if ($settings['host'] === '' || $settings['username'] === '' || $settings['password'] === '' || $settings['from_email'] === '') {
        throw new RuntimeException('SMTP settings are missing. Configure the RESUME_SMTP environment variables.');
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $settings['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $settings['username'];
    $mail->Password = $settings['password'];
    $mail->Port = $settings['port'];
    $mail->SMTPSecure = $settings['encryption'] === 'ssl'
        ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($settings['from_email'], $settings['from_name']);
    $mail->addAddress($email, $name);
    $mail->isHTML(true);
    $mail->Subject = 'Your ResumeAI password reset code';
    $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $mail->Body = '<div style="font-family:Arial,sans-serif;color:#172033;max-width:520px;margin:auto;padding:28px;border:1px solid #dbe4ee;border-radius:16px"><p style="color:#0f9f93;font-weight:bold;letter-spacing:2px">RESUMEAI</p><h1 style="font-size:22px">Reset your password</h1><p>Hello ' . $safeName . ',</p><p>Use this one-time code to reset your ResumeAI password:</p><p style="font-size:32px;font-weight:bold;letter-spacing:8px;color:#0f766e">' . $code . '</p><p>This code expires in 10 minutes. If you did not request a reset, you can ignore this email.</p></div>';
    $mail->AltBody = "Hello {$name},\n\nYour ResumeAI password reset code is {$code}. It expires in 10 minutes. If you did not request a reset, ignore this email.";
    $mail->send();
}
