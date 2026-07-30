<?php
// =====================================================================
// dashboard_stats.php — returns summary stats for Home_Page.html
// Returns JSON: { success, total_members, this_month, this_week, today }
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
    // Total members
    $total = (int)$pdo->query('SELECT COUNT(*) AS c FROM members')->fetch()['c'];

    // Registered this month
    $month = (int)$pdo->query(
        "SELECT COUNT(*) AS c FROM members
         WHERE YEAR(created_at) = YEAR(CURDATE())
           AND MONTH(created_at) = MONTH(CURDATE())"
    )->fetch()['c'];

    // Registered this week (current week, Monday-Sunday)
    $week = (int)$pdo->query(
        "SELECT COUNT(*) AS c FROM members
         WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)"
    )->fetch()['c'];

    // Registered today
    $today = (int)$pdo->query(
        "SELECT COUNT(*) AS c FROM members
         WHERE DATE(created_at) = CURDATE()"
    )->fetch()['c'];

    echo json_encode([
        'success'       => true,
        'total_members' => $total,
        'this_month'    => $month,
        'this_week'     => $week,
        'today'         => $today,
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
}
