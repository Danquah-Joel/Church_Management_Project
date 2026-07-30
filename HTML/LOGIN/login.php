<?php
// =====================================================================
// login.php — authenticates a user (Login.html "USER LOGIN" form)
// Expects POST: username, password
// Returns JSON: { success, user_id, full_name, role } or { success:false, message }
// =====================================================================

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();
ob_clean();
header('Content-Type: application/json');

function send(array $payload): void {
    ob_end_clean();
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    send(['success' => false, 'message' => 'Method not allowed.']);
}

$config_path = __DIR__ . '/db_config.php';
if (!file_exists($config_path)) {
    error_log('login.php: db_config.php not found');
    send(['success' => false, 'message' => 'Server configuration error.']);
}

require $config_path;

try {
    $pdo = getDBConnection();
} catch (Throwable $e) {
    error_log('login.php DB error: ' . $e->getMessage());
    send(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    send(['success' => false, 'message' => 'Username and password are required.']);
}

try {
    $stmt = $pdo->prepare(
        'SELECT user_id, full_name, username, password_hash, role, is_active
         FROM users
         WHERE username = :username
         LIMIT 1'
    );
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();
} catch (Throwable $e) {
    error_log('login.php query error: ' . $e->getMessage());
    send(['success' => false, 'message' => 'A database error occurred. Please try again.']);
}

// Single generic message prevents username enumeration
if (!$user || !password_verify($password, $user['password_hash'])) {
    send(['success' => false, 'message' => 'Invalid username or password.']);
}

if ((int)$user['is_active'] !== 1) {
    send(['success' => false, 'message' => 'This account is not active yet. Please contact an administrator.']);
}

// Regenerate session ID on login to prevent session fixation
session_regenerate_id(true);

$_SESSION['logged_in'] = true; // required by check_session.php's auth guard
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['username']  = $user['username'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role']      = $user['role'];

send([
    'success'   => true,
    'user_id'   => $user['user_id'],
    'full_name' => $user['full_name'],
    'role'      => $user['role'],
]);
