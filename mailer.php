<?php
declare(strict_types=1);

/**
 * Sends one email to multiple recipients.
 * Uses PHPMailer SMTP when vendor autoload is available; falls back to mail().
 */
function sendNotificationEmail(array $toEmails, string $subject, string $body, string $fromEmail, array $smtpConfig = []): bool
{
    $validEmails = [];
    foreach ($toEmails as $email) {
        $clean = filter_var((string)$email, FILTER_VALIDATE_EMAIL);
        if ($clean) {
            $validEmails[] = $clean;
        }
    }
    if (!$validEmails) {
        return false;
    }

    $autoloadPath = __DIR__ . '/vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            return sendViaPhpMailer($validEmails, $subject, $body, $fromEmail, $smtpConfig);
        }
    }

    return sendViaNativeMail($validEmails, $subject, $body, $fromEmail);
}

function sendViaPhpMailer(array $toEmails, string $subject, string $body, string $fromEmail, array $smtpConfig): bool
{
    try {
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $enabled = !empty($smtpConfig['enabled']);
        if ($enabled) {
            $mailer->isSMTP();
            $mailer->Host = (string)($smtpConfig['host'] ?? '');
            $mailer->Port = (int)($smtpConfig['port'] ?? 587);
            $mailer->SMTPAuth = true;
            $mailer->Username = (string)($smtpConfig['username'] ?? '');
            $mailer->Password = (string)($smtpConfig['password'] ?? '');
            $enc = strtolower((string)($smtpConfig['encryption'] ?? 'tls'));
            $mailer->SMTPSecure = $enc === 'ssl'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mailer->setFrom($fromEmail, 'Dissertation System');
        foreach ($toEmails as $email) {
            $mailer->addAddress($email);
        }
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->isHTML(false);
        return $mailer->send();
    } catch (\Throwable $e) {
        error_log('Email notification failed: ' . $e->getMessage());
        return false;
    }
}

function sendViaNativeMail(array $toEmails, string $subject, string $body, string $fromEmail): bool
{
    $headers = [
        'From: ' . $fromEmail,
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion(),
    ];
    $ok = true;
    foreach ($toEmails as $email) {
        if (!@mail($email, $subject, $body, implode("\r\n", $headers))) {
            $ok = false;
        }
    }
    return $ok;
}
