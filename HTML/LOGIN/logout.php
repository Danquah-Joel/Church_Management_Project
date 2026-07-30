<?php
// =====================================================================
// logout.php — destroys the current session and redirects to login
//
// FIX: Previously only returned JSON { success: true } with no redirect,
//      and nothing in the codebase was calling it. Now it performs the
//      redirect itself so any page can link directly to logout.php.
//      If you need a JSON response instead (e.g. for a fetch() call from
//      a JS logout button), remove the header() redirect and uncomment
//      the json_encode line at the bottom.
// =====================================================================

session_start();

// Clear all session variables
$_SESSION = [];

// Expire the session cookie immediately
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// Redirect to login page
// FIX: was returning JSON with no redirect — nothing called this file.
//      Now redirects the browser directly so any <a href="logout.php"> works.
header('Location: Login.html');
exit;

// ── Uncomment below (and remove the header/exit above) if you prefer ──
// ── a JSON response for fetch()-based logout buttons:               ──
// header('Content-Type: application/json');
// echo json_encode(['success' => true]);
