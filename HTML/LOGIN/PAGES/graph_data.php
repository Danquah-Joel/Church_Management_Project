<?php
// =====================================================================
// graph_data.php — returns membership growth data for Home_Page.html
// Chart.js line chart (cumulative total members over last 30 days)
// Returns JSON array: [ { "date": "2026-05-16", "total": 120 }, ... ]
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
    $days = 30;

    // Members registered on or before the count count gets reset to 0
    $baselineStmt = $pdo->prepare(
        'SELECT COUNT(*) AS c FROM members WHERE DATE(created_at) < :start_date'
    );

    $startDate = (new DateTime())->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
    $baselineStmt->execute(['start_date' => $startDate]);
    $runningTotal = (int)$baselineStmt->fetch()['c'];

    // Daily new-member counts for the last $days days
    $stmt = $pdo->prepare(
        "SELECT DATE(created_at) AS day, COUNT(*) AS c
         FROM members
         WHERE DATE(created_at) >= :start_date
         GROUP BY DATE(created_at)"
    );
    $stmt->execute(['start_date' => $startDate]);

    $dailyCounts = [];
    foreach ($stmt->fetchAll() as $row) {
        $dailyCounts[$row['day']] = (int)$row['c'];
    }

    $result = [];
    $date = new DateTime($startDate);
    for ($i = 0; $i < $days; $i++) {
        $dayStr = $date->format('Y-m-d');
        if (isset($dailyCounts[$dayStr])) {
            $runningTotal += $dailyCounts[$dayStr];
        }
        $result[] = [
            'date'  => $dayStr,
            'total' => $runningTotal,
        ];
        $date->modify('+1 day');
    }

    echo json_encode($result);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
}
