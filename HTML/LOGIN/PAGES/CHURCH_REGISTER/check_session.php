<?php
// ============================================================
//  check_session.php
//  Method : GET
//  Returns: { logged_in: bool, username: string|null, csrf_token: string|null }
//
//  Called by the auth guard in Church_Register.html on load.
//  If not logged in, the register redirects to Login.html.
//
//  Also issues/returns the CSRF token for the session. The
//  register pages are static .html (not .php), so they can't
//  have a token embedded server-side at render time the way
//  the old Church_Register.php did. Instead, the JS auth guard
//  reads csrf_token from this response and attaches it as the
//  X-CSRF-Token header on save_attendance.php requests.
// ============================================================

session_start();
header('Content-Type: application/json');

$loggedIn = !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

$csrfToken = null;
if ($loggedIn) {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrfToken = $_SESSION['csrf_token'];
}

echo json_encode([
    'logged_in'  => $loggedIn,
    'username'   => $loggedIn ? ($_SESSION['username'] ?? null) : null,
    'role'       => $loggedIn ? ($_SESSION['role']     ?? null) : null,
    'csrf_token' => $csrfToken,
]);
