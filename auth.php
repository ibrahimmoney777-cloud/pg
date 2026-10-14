<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Return true only when the session contains both an authenticated user ID and role. */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

/** Stop access to role-protected pages and redirect unauthenticated/wrong-role users. */
function requireRole(string $role): void
{
    if (!isLoggedIn() || $_SESSION['role'] !== $role) {
        header('Location: index.php');
        exit;
    }
}

function isGuest(): bool
{
    return !empty($_SESSION['is_guest']);
}

/** Escape untrusted text before inserting it into HTML markup. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
