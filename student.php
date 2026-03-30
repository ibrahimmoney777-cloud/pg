<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
requireRole('student');

$message = $_GET['msg'] ?? '';
$studentId = (int)$_SESSION['user_id'];
$isGuest = isGuest();

$stmt = $pdo->prepare('SELECT * FROM dissertations WHERE student_id = :student_id ORDER BY version DESC');
$stmt->execute(['student_id' => $studentId]);
$items = $stmt->fetchAll();

$stats = ['total' => 0, 'pending' => 0, 'revision' => 0, 'approved' => 0];
foreach ($items as $item) {
    $stats['total']++;
    if ($item['status'] === 'Pending') $stats['pending']++;
    if ($item['status'] === 'Needs Revision') $stats['revision']++;
    if ($item['status'] === 'Approved') $stats['approved']++;
}

function statusClass(string $status): string
{
    return match ($status) {
        'Pending' => 'pending',
        'Under Review' => 'under-review',
        'Needs Revision' => 'needs-revision',
        'Approved' => 'approved',
        default => 'pending'
    };
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="web.css">
</head>
<body>
<main class="container">
  <header class="topbar">
    <div>
      <h1 class="title">Student Dashboard</h1>
      <p class="muted">Welcome, <?= h($_SESSION['name']) ?></p>
    </div>
    <a class="btn btn-ghost" href="logout.php">Logout</a>
  </header>

  <?php if ($message): ?>
    <div class="alert success"><?= h($message) ?></div>
  <?php endif; ?>

  <section class="grid grid-2">
    <article class="card">
      <h2>Upload New Version</h2>
      <?php if ($isGuest): ?>
        <div class="alert error" style="margin-top:10px;">
          Guest mode is read-only. Login with a regular student account to upload files.
        </div>
      <?php else: ?>
        <p class="muted" style="margin:6px 0 12px;">Allowed files: PDF, DOC, DOCX</p>
        <form method="post" action="upload.php" enctype="multipart/form-data">
          <label>Choose file
            <input type="file" name="dissertation_file" accept=".pdf,.doc,.docx" required>
          </label>
          <button type="submit">Upload</button>
        </form>
      <?php endif; ?>
    </article>

    <article class="card">
      <h2>Progress</h2>
      <div class="stats" style="margin-top:12px;">
        <div class="stat"><p class="muted">Total</p><h3><?= $stats['total'] ?></h3></div>
        <div class="stat"><p class="muted">Pending</p><h3><?= $stats['pending'] ?></h3></div>
        <div class="stat"><p class="muted">Needs Revision</p><h3><?= $stats['revision'] ?></h3></div>
        <div class="stat"><p class="muted">Approved</p><h3><?= $stats['approved'] ?></h3></div>
      </div>
    </article>
  </section>

  <section class="card" style="margin-top:14px;">
    <h2>My Submissions</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>File</th>
            <th>Version</th>
            <th>Status</th>
            <th>Comment</th>
            <th>Upload Date</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= h($item['file_name']) ?></td>
            <td>V<?= (int)$item['version'] ?></td>
            <td class="status <?= statusClass($item['status']) ?>"><?= h($item['status']) ?></td>
            <td><?= h($item['comment']) ?: '-' ?></td>
            <td><?= h((string)$item['upload_date']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
</body>
</html>
