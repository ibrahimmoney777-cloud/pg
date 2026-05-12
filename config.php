<?php
declare(strict_types=1);

$dbHost = '127.0.0.1';
$dbName = 'dissertation_system';
$dbUser = 'root';
$dbPass = '';

/**
 * Automatic email notifications:
 * Add one or more supervisor email addresses here.
 */
$supervisorEmails = [
    'supervisor@example.com',
];

/**
 * "From" address used in notification emails.
 * Change this to a valid mailbox on your hosting/server.
 */
$mailFrom = 'noreply@dissertation-system.local';

/**
 * SMTP settings (used by PHPMailer when available).
 * Keep these values safe and replace with your real mail provider settings.
 */
$smtpConfig = [
    'enabled' => true,
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your_email@gmail.com',
    'password' => 'your_app_password',
    'encryption' => 'tls', // tls or ssl
];

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
