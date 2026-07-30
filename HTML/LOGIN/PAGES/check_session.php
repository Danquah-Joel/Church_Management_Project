<?php
// =====================================================================
// check_session.php — tells the client whether a valid server session
// exists. Called by Home_Page.html on load as an auth guard, and by
// Church_Register.html / Church_Register_Records.html via the relative
// path '../check_session.php' from PAGES/CHURCH_REGISTER/.
// Returns JSON: { logged_in: bool, user_id, full_name, role, csrf_token }
//
// csrf_token is issued/returned here because the register pages are
// static .html (not .php), so they can't have a token embedded
// server-side at render time. The JS auth guard in those pages reads
// csrf_token from this response and attaches it as the X-CSRF-Token
// header on save_attendance.php / delete_attendance.php requests.
// =====================================================================

session_start();
header('Content-Type: application/json');

$logged_in = isset($_SESSION['user_id']);

$csrf_token = null;
if ($logged_in) {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];
}

echo json_encode([
    'logged_in'  => $logged_in,
    'user_id'    => $logged_in ? $_SESSION['user_id']   : null,
    'full_name'  => $logged_in ? $_SESSION['full_name'] : null,
    'role'       => $logged_in ? $_SESSION['role']      : null,
    'csrf_token' => $csrf_token,
]);
