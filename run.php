<?php
declare(strict_types=1);

/** Lightweight dependency-free unit tests for shared authentication helpers. */
require_once __DIR__ . '/../auth.php';

$tests = 0;
$failures = [];

function check(string $name, bool $condition): void
{
    global $tests, $failures;
    $tests++;
    if (!$condition) {
        $failures[] = $name;
        fwrite(STDERR, "FAIL: {$name}\n");
        return;
    }
    echo "PASS: {$name}\n";
}

// Escaping should encode HTML-significant characters and preserve safe text.
check('h() escapes HTML characters', h('<script>alert("x")</script>') === '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;');
check('h() returns empty string for null', h(null) === '');
check('h() preserves ordinary text', h('Dissertation V2') === 'Dissertation V2');

// Authentication state depends on both required session fields.
$_SESSION = [];
check('empty session is not logged in', isLoggedIn() === false);
$_SESSION['user_id'] = 7;
check('user ID without role is not logged in', isLoggedIn() === false);
$_SESSION['role'] = 'student';
check('user ID and role means logged in', isLoggedIn() === true);
check('guest flag is false when absent', isGuest() === false);
$_SESSION['is_guest'] = true;
check('guest flag is detected', isGuest() === true);

printf("\n%d tests run; %d failure(s).\n", $tests, count($failures));
exit(count($failures) === 0 ? 0 : 1);
