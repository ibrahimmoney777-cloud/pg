<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    header('Location: ' . ($_SESSION['role'] === 'student' ? 'student.php' : 'supervisor.php'));
    exit;
}

$error = '';

// This page handles the sign-in form and routes successful logins by role.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'student';
    $name = trim(strtolower($_POST['name'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!in_array($role, ['student', 'supervisor'], true)) {
        $role = 'student';
    }

    if ($name === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $name = substr($name, 0, 100);
        // The table name is selected only from this fixed allow-list; user input is never used as SQL.
        $table = $role === 'student' ? 'students' : 'supervisors';
        $stmt = $pdo->prepare("SELECT id, name, password FROM {$table} WHERE name = :name");
        $stmt->execute(['name' => $name]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Invalid username or password.';
        } else {
            $hash = hash('sha256', $password);
            if (!hash_equals((string)$user['password'], $hash)) {
                $error = 'Invalid username or password.';
            } else {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $role;
                unset($_SESSION['is_guest']);
                header('Location: ' . ($role === 'student' ? 'student.php' : 'supervisor.php'));
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PG Dissertation Management System — Sign In</title>
  <link rel="stylesheet" href="web.css">
</head>
<body>
  <main class="container">
    <section class="card login-panel">
      <h1 class="title">Dissertation Management System</h1>
      <p class="muted subtitle">Enter your credentials to access the postgraduate dissertation portal.</p>
      <?php if ($error): ?>
        <div class="alert error" role="alert"><?= h($error) ?></div>
      <?php endif; ?>
      <form method="post" novalidate>
        <label for="role">Role
          <select id="role" name="role" required aria-required="true">
            <option value="student">Student</option>
            <option value="supervisor">Supervisor</option>
          </select>
        </label>
        <label for="name">Username
          <input id="name" name="name" type="text" placeholder="Enter your username" required aria-required="true" maxlength="100" autocomplete="username">
        </label>
        <label for="password">Password
          <input id="password" name="password" type="password" placeholder="Enter your password" required aria-required="true" maxlength="128" autocomplete="current-password">
        </label>
        <button type="submit">Sign In</button>
      </form>
    </section>
  </main>
  <footer class="site-footer">
    <p>© 2026 Team Feshata. All rights reserved.</p>
  </footer>
</body>
</html>
