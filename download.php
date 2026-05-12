<?php
declare(strict_types=1);
/**
 * Serves an uploaded dissertation file only to the owning student or any supervisor.
 * Prevents direct guessing of paths under /uploads/.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid request');
}

$stmt = $pdo->prepare('SELECT * FROM dissertations WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$row = $stmt->fetch();
if (!$row) {
    http_response_code(404);
    exit('Not found');
}

$role = $_SESSION['role'] ?? '';
$sessionUserId = (int)($_SESSION['user_id'] ?? 0);
$ownerId = (int)$row['student_id'];

if ($role === 'student') {
    if ($sessionUserId !== $ownerId) {
        http_response_code(403);
        exit('Forbidden');
    }
} elseif ($role === 'supervisor') {
    // Supervisors may open any submission file
} else {
    http_response_code(403);
    exit('Forbidden');
}

$relative = (string)$row['file_path'];
$full = realpath(__DIR__ . '/' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative));
$uploadsRoot = realpath(__DIR__ . '/uploads');

if ($full === false || $uploadsRoot === false || !is_file($full)) {
    http_response_code(404);
    exit('File missing');
}

// Ensure resolved path stays inside uploads/
if (strpos($full, $uploadsRoot) !== 0) {
    http_response_code(403);
    exit('Forbidden');
}

$ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
// Open in the browser tab when possible (pics, PDF, video, audio, text, etc.); only force download for risky types.
$forceAttachmentExts = ['exe', 'dll', 'bat', 'cmd', 'msi', 'scr', 'com', 'pif', 'vbs', 'ps1', 'reg', 'jar'];
$disposition = in_array($ext, $forceAttachmentExts, true) ? 'attachment' : 'inline';

$mime = 'application/octet-stream';
if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $detected = $finfo->file($full);
    if (is_string($detected) && $detected !== '') {
        $mime = $detected;
    }
} elseif (function_exists('mime_content_type')) {
    $detected = @mime_content_type($full);
    if (is_string($detected) && $detected !== '') {
        $mime = $detected;
    }
}

$downloadName = basename((string)$row['file_name']);
$safeName = str_replace(['"', "\r", "\n"], '', $downloadName);
header('Content-Type: ' . $mime);
header('Content-Disposition: ' . $disposition . '; filename="' . $safeName . '"');
header('Content-Length: ' . (string)filesize($full));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');
readfile($full);
exit;
