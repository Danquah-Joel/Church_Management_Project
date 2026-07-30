<?php
// =====================================================================
// member_records.php — returns the member list for Members.html
// Expects GET (optional): search, status (ministry filter)
// Returns JSON: { success, stats: { total }, records: [ {...}, ... ] }
// =====================================================================

session_start();
require 'db_config.php';
$pdo = getDBConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

try {
    // Total members count
    $totalStmt = $pdo->query('SELECT COUNT(*) AS total FROM members');
    $total = (int)$totalStmt->fetch()['total'];

    // Fetch member records
    $stmt = $pdo->query(
        "SELECT
            member_id,
            CONCAT(
                position, ' ',
                first_name,
                IF(middle_name IS NOT NULL AND middle_name <> '', CONCAT(' ', middle_name), ''),
                ' ', surname
            ) AS member_name,
            COALESCE(ministry, 'N/A')           AS ministry,
            COALESCE(status, 'N/A')             AS status,
            COALESCE(location, zone, 'N/A')     AS location,
            phone1                              AS contact
         FROM members
         ORDER BY member_id DESC"
    );
    $rows = $stmt->fetchAll();

    $records = array_map(function ($row) {
        return [
            'member_id'   => (int)$row['member_id'],
            'member_name' => $row['member_name'],
            'ministry'    => $row['ministry'],
            'status'      => $row['status'],
            'location'    => $row['location'],
            'contact'     => $row['contact'],
        ];
    }, $rows);

    echo json_encode([
        'success' => true,
        'stats'   => [
            'total' => $total,
        ],
        'records' => $records,
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
}
