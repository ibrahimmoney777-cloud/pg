<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';
requireRole('student');

$studentId = (int)$_SESSION['user_id'];
if (isGuest()) {
    header('Location: student.php?msg=Guest mode is read-only');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['dissertation_file'])) {
    header('Location: student.php?msg=Invalid request');
    exit;
}

$file = $_FILES['dissertation_file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: student.php?msg=Upload failed');
    exit;
}

$originalName = basename((string)$file['name']);
// Any extension allowed; sanitize stored filename on disk (letters/digits only, max length).
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
if (strlen($ext) > 32) {
    $ext = substr($ext, 0, 32);
}
$extSuffix = $ext !== '' ? '.' . $ext : '';

$versionStmt = $pdo->prepare('SELECT COALESCE(MAX(version), 0) AS max_version FROM dissertations WHERE student_id = :student_id');
$versionStmt->execute(['student_id' => $studentId]);
$nextVersion = ((int)$versionStmt->fetch()['max_version']) + 1;

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$safeName = sprintf('student_%d_v%d_%s%s', $studentId, $nextVersion, uniqid('', true), $extSuffix);
$targetPath = $uploadDir . '/' . $safeName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    header('Location: student.php?msg=Failed to save file');
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO dissertations (student_id, file_name, file_path, version, status, comment) 
     VALUES (:student_id, :file_name, :file_path, :version, :status, :comment)'
);
$stmt->execute([
    'student_id' => $studentId,
    'file_name' => $originalName,
    'file_path' => 'uploads/' . $safeName,
    'version' => $nextVersion,
    'status' => 'Pending',
    'comment' => '',
]);

// Notify supervisors about new submissions (best effort; upload succeeds even if email fails).
$studentNameStmt = $pdo->prepare('SELECT name FROM students WHERE id = :id');
$studentNameStmt->execute(['id' => $studentId]);
$studentName = (string)($studentNameStmt->fetch()['name'] ?? ('Student #' . $studentId));

if (!empty($supervisorEmails) && is_array($supervisorEmails)) {
    $subject = 'New Dissertation Upload - Review Needed';
    $message = "A new dissertation version was uploaded.\n\n"
        . "Student: {$studentName}\n"
        . "File: {$originalName}\n"
        . "Version: V{$nextVersion}\n"
        . "Status: Pending\n"
        . "Time: " . date('Y-m-d H:i:s') . "\n";

    sendNotificationEmail(
        $supervisorEmails,
        $subject,
        $message,
        (string)($mailFrom ?? 'noreply@localhost'),
        (array)($smtpConfig ?? [])
    );
}

header('Location: student.php?msg=File uploaded successfully');
exit;
