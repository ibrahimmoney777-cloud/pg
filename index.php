<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    header('Location: ' . ($_SESSION['role'] === 'student' ? 'student.php' : 'supervisor.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    if ($action === 'guest') {
        $guestName = 'guest_student';
        $stmt = $pdo->prepare('SELECT id, name FROM students WHERE name = :name');
        $stmt->execute(['name' => $guestName]);
        $guest = $stmt->fetch();

        if (!$guest) {
            $insert = $pdo->prepare('INSERT INTO students (name, password) VALUES (:name, SHA2(:password, 256))');
            $insert->execute([
                'name' => $guestName,
                'password' => 'guest',
            ]);
            $guest = [
                'id' => (int)$pdo->lastInsertId(),
                'name' => $guestName,
            ];
        }

        $_SESSION['user_id'] = (int)$guest['id'];
        $_SESSION['name'] = $guest['name'];
        $_SESSION['role'] = 'student';
        $_SESSION['is_guest'] = true;
        header('Location: student.php');
        exit;
    }

    $role = $_POST['role'] ?? 'student';
    $name = trim(strtolower($_POST['name'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!in_array($role, ['student', 'supervisor'], true)) {
        $role = 'student';
    }

    if ($name === '' || $password === '') {
        $error = 'Name and password are required.';
    } else {
        $name = substr($name, 0, 100);
        $table = $role === 'student' ? 'students' : 'supervisors';
        $stmt = $pdo->prepare("SELECT id, name FROM {$table} WHERE name = :name");
        $stmt->execute(['name' => $name]);
        $user = $stmt->fetch();

        if (!$user) {
            $insert = $pdo->prepare("INSERT INTO {$table} (name, password) VALUES (:name, SHA2(:password, 256))");
            $insert->execute([
                'name' => $name,
                'password' => $password,
            ]);
            $user = [
                'id' => (int)$pdo->lastInsertId(),
                'name' => $name,
            ];
        } else {
            // Keep demo credentials flexible by accepting any password and updating it.
            $update = $pdo->prepare("UPDATE {$table} SET password = SHA2(:password, 256) WHERE id = :id");
            $update->execute([
                'password' => $password,
                'id' => (int)$user['id'],
            ]);
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $role;
        unset($_SESSION['is_guest']);
        header('Location: ' . ($role === 'student' ? 'student.php' : 'supervisor.php'));
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dissertation System - Login</title>
  <link rel="stylesheet" href="web.css">
</head>
<body>
  <main class="container">
    <section class="card" style="max-width:520px;margin:40px auto;">
      <h1 class="title">Dissertation System</h1>
      <p class="muted" style="margin:8px 0 14px;">Login as Student or Supervisor</p>
      <?php if ($error): ?>
        <div class="alert error"><?= h($error) ?></div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="login">
        <label>Role
          <select name="role" required>
            <option value="">Choose role</option>
            <option value="student">Student</option>
            <option value="supervisor">Supervisor</option>
          </select>
        </label>
        <label>Name
          <input name="name" type="text" placeholder="ali / dr_ahmed" required>
        </label>
        <label>Password
          <input name="password" type="password" placeholder="Enter password" required>
        </label>
        <button type="submit">Login</button>
      </form>
      <form method="post" style="margin-top:10px;">
        <input type="hidden" name="action" value="guest">
        <button type="submit" class="btn btn-ghost">Skip as Guest</button>
      </form>
      <p class="muted" style="margin-top:14px;font-size:13px;">
        Login accepts any username/password for the selected role.
      </p>
    </section>
  </main>
</body>
</html>
