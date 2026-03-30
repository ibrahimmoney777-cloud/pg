<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
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
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowed = ['pdf', 'doc', 'docx'];
if (!in_array($ext, $allowed, true)) {
    header('Location: student.php?msg=Only PDF or Word allowed');
    exit;
}

$versionStmt = $pdo->prepare('SELECT COALESCE(MAX(version), 0) AS max_version FROM dissertations WHERE student_id = :student_id');
$versionStmt->execute(['student_id' => $studentId]);
$nextVersion = ((int)$versionStmt->fetch()['max_version']) + 1;

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$safeName = sprintf('student_%d_v%d_%s.%s', $studentId, $nextVersion, uniqid(), $ext);
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

header('Location: student.php?msg=File uploaded successfully');
exit;
