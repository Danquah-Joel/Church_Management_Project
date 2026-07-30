<?php
// ============================================================
//  delete_attendance.php
//  Method : DELETE
//  Query  : ?id=<record_id>
//  Returns: { success: bool, message: string }
//
//  The attendance_others rows are deleted automatically via
//  the ON DELETE CASCADE foreign key constraint.
// ============================================================

session_start();
header('Content-Type: application/json');

// -- Auth check --
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

// -- CSRF check --
$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfHeader)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

// -- Only accept DELETE method --
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// -- Validate id --
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing record ID.']);
    exit;
}

require_once __DIR__ . '/db_connect.php';

try {
    $stmt = $pdo->prepare("DELETE FROM attendance_records WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Record deleted successfully.',
    ]);

} catch (PDOException $e) {
    error_log('delete_attendance.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete record.',
    ]);
}
