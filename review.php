<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
requireRole('supervisor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: supervisor.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$comment = substr(trim(strip_tags($_POST['comment'] ?? '')), 0, 2000);

// Pending is the initial state; supervisors may move a submission to one of these review states.
$allowedStatuses = ['Under Review', 'Needs Revision', 'Approved'];
if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
    header('Location: supervisor.php?msg=Invalid review data');
    exit;
}

$stmt = $pdo->prepare('UPDATE dissertations SET status = :status, comment = :comment WHERE id = :id');
$stmt->execute([
    'status' => $status,
    'comment' => $comment,
    'id' => $id,
]);

header('Location: supervisor.php?msg=Submission updated successfully');
exit;
