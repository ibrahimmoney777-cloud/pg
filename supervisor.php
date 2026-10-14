<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
requireRole('supervisor');

$message = $_GET['msg'] ?? '';
$filter = $_GET['status'] ?? 'all';
$validFilters = ['all', 'Pending', 'Under Review', 'Needs Revision', 'Approved'];
if (!in_array($filter, $validFilters, true)) $filter = 'all';

$baseSql = 'SELECT d.*, s.name AS student_name FROM dissertations d JOIN students s ON s.id = d.student_id';
$params = [];
if ($filter !== 'all') {
    $baseSql .= ' WHERE d.status = :status';
    $params['status'] = $filter;
}
$baseSql .= ' ORDER BY d.upload_date DESC';

$stmt = $pdo->prepare($baseSql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$allStmt = $pdo->query('SELECT status FROM dissertations');
$allItems = $allStmt->fetchAll();
$summary = ['total' => 0, 'pending' => 0, 'review' => 0, 'revision' => 0, 'approved' => 0];
foreach ($allItems as $row) {
    $summary['total']++;
    if ($row['status'] === 'Pending') $summary['pending']++;
    if ($row['status'] === 'Under Review') $summary['review']++;
    if ($row['status'] === 'Needs Revision') $summary['revision']++;
    if ($row['status'] === 'Approved') $summary['approved']++;
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
  <title>Supervisor Dashboard — PG Dissertation Management</title>
  <link rel="stylesheet" href="web.css">
</head>
<body>
<main class="container">
  <header class="topbar">
    <div>
      <h1 class="title">Supervisor Dashboard</h1>
      <p class="muted">Welcome, <?= h($_SESSION['name']) ?></p>
    </div>
    <a class="btn btn-ghost" href="logout.php">Sign Out</a>
  </header>

  <?php if ($message): ?>
    <div class="alert <?= str_contains(strtolower($message), 'invalid') || str_contains(strtolower($message), 'fail') ? 'error' : 'success' ?>" role="alert"><?= h($message) ?></div>
  <?php endif; ?>

  <section class="stats" style="margin-bottom:18px;">
    <div class="stat"><p class="muted">Total</p><h3><?= $summary['total'] ?></h3></div>
    <div class="stat"><p class="muted">Pending</p><h3><?= $summary['pending'] ?></h3></div>
    <div class="stat"><p class="muted">Under Review</p><h3><?= $summary['review'] ?></h3></div>
    <div class="stat"><p class="muted">Revision Required</p><h3><?= $summary['revision'] ?></h3></div>
    <div class="stat"><p class="muted">Approved</p><h3><?= $summary['approved'] ?></h3></div>
  </section>

  <section class="card" style="margin-bottom:18px;">
    <form method="get" style="max-width:320px;">
      <label for="status">Filter by Status
        <select id="status" name="status" onchange="this.form.submit()" aria-label="Filter submissions by status">
          <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
          <option value="Pending" <?= $filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
          <option value="Under Review" <?= $filter === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
          <option value="Needs Revision" <?= $filter === 'Needs Revision' ? 'selected' : '' ?>>Revision Required</option>
          <option value="Approved" <?= $filter === 'Approved' ? 'selected' : '' ?>>Approved</option>
        </select>
      </label>
    </form>
  </section>

  <section class="card">
    <h2>All Submissions</h2>
    <p class="muted" style="margin:8px 0 14px;">Review submissions, provide feedback, and update academic status.</p>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Student</th>
            <th>File</th>
            <th>Version</th>
            <th>Status</th>
            <th>Comment</th>
            <th>Upload Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <h3 style="margin:0 0 6px;color:var(--text);">No submissions to review</h3>
                <p style="margin:0;">Student submissions will appear here once uploaded.</p>
              </div>
            </td>
          </tr>
        <?php else: ?>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= h($item['student_name']) ?></td>
            <td>
              <a class="file-link" href="download.php?id=<?= (int)$item['id'] ?>" target="_blank" rel="noopener" title="Open uploaded file"><?= h($item['file_name']) ?></a>
            </td>
            <td>V<?= (int)$item['version'] ?></td>
            <td><?= statusBadge((string)$item['status']) ?></td>
            <td><?= h($item['comment']) ?: '—' ?></td>
            <td><?= h((string)$item['upload_date']) ?></td>
            <td>
              <form method="post" action="review.php" style="min-width:220px;">
                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                <label for="status-<?= (int)$item['id'] ?>">Status
                  <select id="status-<?= (int)$item['id'] ?>" name="status" required aria-required="true">
                    <option value="Under Review" <?= $item['status'] === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                    <option value="Needs Revision" <?= $item['status'] === 'Needs Revision' ? 'selected' : '' ?>>Revision Required</option>
                    <option value="Approved" <?= $item['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                  </select>
                </label>
                <label for="comment-<?= (int)$item['id'] ?>">Feedback
                  <textarea id="comment-<?= (int)$item['id'] ?>" name="comment" placeholder="Write feedback..." maxlength="2000"><?= h($item['comment']) ?></textarea>
                </label>
                <button type="submit">Save</button>
              </form>
            </td>
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
