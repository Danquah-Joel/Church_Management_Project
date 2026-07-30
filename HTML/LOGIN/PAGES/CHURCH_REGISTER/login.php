<?php
// ============================================================
//  login.php
//  Method : POST
//  Body   : JSON  { "username": "...", "password": "..." }
//  Returns: { success: bool, message: string }
//
//  On success sets $_SESSION['logged_in'], ['username'], ['role']
//  so check_session.php returns { logged_in: true }.
// ============================================================

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

$username = trim($body['username'] ?? '');
$password = $body['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

require_once __DIR__ . '/db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT id, password_hash, full_name, role FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit;
    }

    // Regenerate session ID to prevent fixation attacks
    session_regenerate_id(true);

    $_SESSION['logged_in'] = true;
    $_SESSION['user_id']   = (int)$user['id'];
    $_SESSION['username']  = $username;
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    echo json_encode([
        'success'   => true,
        'message'   => 'Login successful.',
        'full_name' => $user['full_name'],
        'role'      => $user['role'],
    ]);

} catch (PDOException $e) {
    error_log('login.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Login failed due to a server error.',
    ]);
}
