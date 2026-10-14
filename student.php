<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
requireRole('student');

$message = $_GET['msg'] ?? '';
$studentId = (int)$_SESSION['user_id'];

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

function statusBadge(string $status): string
{
    $class = match ($status) {
        'Pending' => 'status-badge--pending',
        'Under Review' => 'status-badge--under-review',
        'Needs Revision' => 'status-badge--needs-revision',
        'Approved' => 'status-badge--approved',
        default => 'status-badge--pending',
    };
    return '<span class="status-badge ' . $class . '">' . h($status) . '</span>';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard — PG Dissertation Management</title>
  <link rel="stylesheet" href="web.css">
</head>
<body>
<main class="container">
  <header class="topbar">
    <div>
      <h1 class="title">Student Dashboard</h1>
      <p class="muted">Welcome, <?= h($_SESSION['name']) ?></p>
    </div>
    <a class="btn btn-ghost" href="logout.php">Sign Out</a>
  </header>

  <?php if ($message): ?>
    <div class="alert <?= str_contains(strtolower($message), 'fail') || str_contains(strtolower($message), 'invalid') || str_contains(strtolower($message), 'exceed') || str_contains(strtolower($message), 'only') ? 'error' : 'success' ?>" role="alert"><?= h($message) ?></div>
  <?php endif; ?>

  <section class="grid grid-2">
    <article class="card">
      <h2>Upload New Version</h2>
      <p class="muted" style="margin:8px 0 16px;">Submit a new dissertation version in PDF or DOCX format. Maximum file size: 25 MB.</p>
      <form method="post" action="upload.php" enctype="multipart/form-data">
        <label for="dissertation_file">Dissertation File
          <input id="dissertation_file" type="file" name="dissertation_file" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required aria-required="true">
        </label>
        <button type="submit">Upload</button>
      </form>
    </article>

    <article class="card">
      <h2>My Progress</h2>
      <div class="stats" style="margin-top:14px;">
        <div class="stat"><p class="muted">Total</p><h3><?= $stats['total'] ?></h3></div>
        <div class="stat"><p class="muted">Pending</p><h3><?= $stats['pending'] ?></h3></div>
        <div class="stat"><p class="muted">Revision Required</p><h3><?= $stats['revision'] ?></h3></div>
        <div class="stat"><p class="muted">Approved</p><h3><?= $stats['approved'] ?></h3></div>
      </div>
    </article>
  </section>

  <section class="card" style="margin-top:18px;">
    <h2>My Submissions</h2>
    <div class="table-wrap" style="margin-top:14px;">
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
        <?php if (!$items): ?>
          <tr>
            <td colspan="5">
              <div class="empty-state">
                <h3 style="margin:0 0 6px;color:var(--text);">No submissions yet</h3>
                <p style="margin:0;">Upload your first dissertation version to begin the review process.</p>
              </div>
            </td>
          </tr>
        <?php else: ?>
        <?php foreach ($items as $item): ?>
          <tr>
            <td>
              <a class="file-link" href="download.php?id=<?= (int)$item['id'] ?>" target="_blank" rel="noopener" title="Open your file"><?= h($item['file_name']) ?></a>
            </td>
            <td>V<?= (int)$item['version'] ?></td>
            <td><?= statusBadge((string)$item['status']) ?></td>
            <td><?= h($item['comment']) ?: '—' ?></td>
            <td><?= h((string)$item['upload_date']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
<footer class="site-footer">
  <p>© 2026 Team Feshata. All rights reserved.</p>
</footer>
</body>
</html>
