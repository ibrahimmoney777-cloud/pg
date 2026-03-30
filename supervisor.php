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
  <title>Supervisor Dashboard</title>
  <link rel="stylesheet" href="web.css">
</head>
<body>
<main class="container">
  <header class="topbar">
    <div>
      <h1 class="title">Supervisor Dashboard</h1>
      <p class="muted">Welcome, <?= h($_SESSION['name']) ?></p>
    </div>
    <a class="btn btn-ghost" href="logout.php">Logout</a>
  </header>

  <?php if ($message): ?>
    <div class="alert success"><?= h($message) ?></div>
  <?php endif; ?>

  <section class="card" style="margin-bottom:14px;">
    <form method="get" style="max-width:300px;">
      <label>Filter by status
        <select name="status" onchange="this.form.submit()">
          <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
          <option value="Pending" <?= $filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
          <option value="Under Review" <?= $filter === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
          <option value="Needs Revision" <?= $filter === 'Needs Revision' ? 'selected' : '' ?>>Needs Revision</option>
          <option value="Approved" <?= $filter === 'Approved' ? 'selected' : '' ?>>Approved</option>
        </select>
      </label>
    </form>
  </section>

  <section class="card">
    <h2>All Submissions</h2>
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
            <th>File</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= h($item['student_name']) ?></td>
            <td><?= h($item['file_name']) ?></td>
            <td>V<?= (int)$item['version'] ?></td>
            <td class="status <?= statusClass($item['status']) ?>"><?= h($item['status']) ?></td>
            <td><?= h($item['comment']) ?: '-' ?></td>
            <td><?= h((string)$item['upload_date']) ?></td>
            <td><a class="btn btn-ghost" href="<?= h($item['file_path']) ?>" target="_blank">Open</a></td>
            <td>
              <form method="post" action="review.php" style="min-width:220px;">
                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                <label>Status
                  <select name="status" required>
                    <option value="Under Review">Under Review</option>
                    <option value="Needs Revision">Needs Revision</option>
                    <option value="Approved">Approved</option>
                  </select>
                </label>
                <label>Comment
                  <textarea name="comment" placeholder="Write feedback..."><?= h($item['comment']) ?></textarea>
                </label>
                <button type="submit">Save</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
</body>
</html>
