<?php
// =====================================================================
// register.php — registers a new user account after admin code approval
// Expects POST: full_name, username, password, admin_code
// Returns JSON: { success } or { success:false, message }
// =====================================================================

// FIX: ob_start() captures ANY stray output (PHP warnings, notices, HTML
//      error pages from require) so they never corrupt the JSON response.
ob_start();

// Suppress PHP warnings/notices from being output — they go to error_log only.
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();

// Discard anything PHP may have printed before we send our header
ob_clean();
header('Content-Type: application/json');

// ── Helper: send JSON and exit cleanly ───────────────────────────────────────
function send(array $payload): void {
    ob_end_clean(); // discard any buffered stray output
    echo json_encode($payload);
    exit;
}

// ── Method guard ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    send(['success' => false, 'message' => 'Method not allowed.']);
}

// ── Load db_config safely ─────────────────────────────────────────────────────
// If db_config.php doesn't exist or throws, the error goes to error_log (not
// the browser) because display_errors is off and ob_start() ate any output.
$config_path = __DIR__ . '/db_config.php';
if (!file_exists($config_path)) {
    error_log('register.php: db_config.php not found at ' . $config_path);
    send(['success' => false, 'message' => 'Server configuration error. Please contact an administrator.']);
}

require $config_path;

// ── Admin code check ──────────────────────────────────────────────────────────
// Add this line to db_config.php (never put the code in any client-side file):
//   define('ADMIN_REGISTRATION_CODE', '12345');
//
// FIX: a 5-digit numeric code only has 100,000 possible combinations and had
//      no attempt limiting, so it could be brute-forced with repeated POSTs.
//      A short session-based lockout closes that off without needing a DB table.
$max_admin_attempts = 5;
$lockout_seconds     = 300; // 5 minutes

if (!isset($_SESSION['admin_code_attempts'])) {
    $_SESSION['admin_code_attempts']     = 0;
    $_SESSION['admin_code_locked_until'] = 0;
}

if ($_SESSION['admin_code_locked_until'] > time()) {
    $wait_seconds = $_SESSION['admin_code_locked_until'] - time();
    send(['success' => false, 'message' => "Too many incorrect attempts. Please try again in {$wait_seconds} seconds."]);
}

$submitted_code = trim($_POST['admin_code'] ?? '');

if (!defined('ADMIN_REGISTRATION_CODE')) {
    error_log('register.php: ADMIN_REGISTRATION_CODE is not defined in db_config.php');
    send(['success' => false, 'message' => 'Server configuration error. Please contact an administrator.']);
}

if ($submitted_code === '' || $submitted_code !== ADMIN_REGISTRATION_CODE) {
    $_SESSION['admin_code_attempts']++;
    if ($_SESSION['admin_code_attempts'] >= $max_admin_attempts) {
        $_SESSION['admin_code_locked_until'] = time() + $lockout_seconds;
        $_SESSION['admin_code_attempts']     = 0;
    }
    send(['success' => false, 'message' => 'Incorrect admin code. Please try again.']);
}

// Correct code — clear the attempt counter
$_SESSION['admin_code_attempts'] = 0;

// ── Database connection ───────────────────────────────────────────────────────
try {
    $pdo = getDBConnection();
} catch (Throwable $e) {
    error_log('register.php DB connection error: ' . $e->getMessage());
    send(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
}

// ── Input validation ──────────────────────────────────────────────────────────
$full_name = trim($_POST['full_name'] ?? '');
$username  = trim($_POST['username']  ?? '');
$password  = $_POST['password'] ?? '';

if ($full_name === '' || $username === '' || $password === '') {
    send(['success' => false, 'message' => 'All fields are required.']);
}

// FIX: matches the schema's column sizes (full_name VARCHAR(150),
// username VARCHAR(50)) — without this, an over-long value hits MySQL's
// "Data too long for column" error under strict mode, which fell through
// to the generic "A database error occurred" message instead of telling
// the person what was actually wrong.
if (strlen($full_name) > 150) {
    send(['success' => false, 'message' => 'Full name must be 150 characters or fewer.']);
}

if (strlen($username) > 50) {
    send(['success' => false, 'message' => 'Username must be 50 characters or fewer.']);
}

if (strlen($password) < 6) {
    send(['success' => false, 'message' => 'Password must be at least 6 characters.']);
}

// ── Duplicate username check ──────────────────────────────────────────────────
try {
    $check = $pdo->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
    $check->execute([$username]);

    if ($check->fetch()) {
        send(['success' => false, 'message' => 'Username is already taken.']);
    }

    // ── Insert user ───────────────────────────────────────────────────────────
    // is_active = 1 because the admin code was already verified above.
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, username, password_hash, role, is_active)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$full_name, $username, $password_hash, 'member', 1]);

    send([
        'success' => true,
        'user_id' => (int) $pdo->lastInsertId(),
        'message' => 'Account created successfully.',
    ]);

} catch (Throwable $e) {
    // FIX: race condition — if two requests both pass the duplicate-username
    // SELECT check above before either INSERT runs, the second INSERT will
    // fail on the DB's UNIQUE constraint instead of being caught above.
    // SQLSTATE 23000 = integrity constraint violation, so translate that
    // specific case back into the friendly message instead of a generic error.
    if ($e instanceof PDOException && $e->getCode() === '23000') {
        send(['success' => false, 'message' => 'Username is already taken.']);
    }
    error_log('register.php DB query error: ' . $e->getMessage());
    send(['success' => false, 'message' => 'A database error occurred. Please try again.']);
}
