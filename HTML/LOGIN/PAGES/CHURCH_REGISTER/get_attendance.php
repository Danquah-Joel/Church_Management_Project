<?php
// ============================================================
//  get_attendance.php
//  Method : GET
//  Returns: { success: bool, records: [...] }
//
//  Each record includes an `others` array of Guest/Visitor/
//  New Convert entries so mapApiRecord() in the register can
//  populate the detail column in the attendance sheet.
// ============================================================

session_start();
header('Content-Type: application/json');

// -- Auth check --
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

require_once __DIR__ . '/db_connect.php';

try {
    // -- Fetch all attendance records newest-first --
    $stmt = $pdo->query("
        SELECT
            id,
            service_date,
            service_type,
            service_time,
            minister,
            cnt_apostles,
            cnt_apostles_wife,
            cnt_pastors,
            cnt_pastors_wife,
            cnt_elders,
            cnt_dcn,
            cnt_dcns,
            cnt_men,
            cnt_women,
            cnt_youth,
            cnt_children,
            cnt_visitors,
            cnt_new_converts,
            cnt_guests,
            adult_total,
            grand_total,
            officers_total,
            offering_amount,
            tithe_amount,
            com_officers,
            com_male,
            com_female,
            communion_total,
            bs_male,
            bs_female,
            bs_total,
            activities,
            prayer_request,
            notes,
            created_at
        FROM attendance_records
        ORDER BY service_date DESC, id DESC
    ");

    $records = $stmt->fetchAll();

    // -- Fetch all others rows in one query, group by record_id --
    $othersStmt = $pdo->query("
        SELECT record_id, category, first_name, second_name,
               location, phone, status
        FROM attendance_others
        ORDER BY id ASC
    ");

    $othersMap = [];
    foreach ($othersStmt->fetchAll() as $row) {
        $rid = (int)$row['record_id'];
        unset($row['record_id']);
        $othersMap[$rid][] = $row;
    }

    // -- Attach others to their parent records --
    foreach ($records as &$rec) {
        $rid = (int)$rec['id'];
        $rec['others'] = $othersMap[$rid] ?? [];
        // Cast numeric strings to proper types for the JS mapApiRecord()
        $rec['id']               = $rid;
        $rec['cnt_apostles']     = (int)$rec['cnt_apostles'];
        $rec['cnt_apostles_wife']= (int)$rec['cnt_apostles_wife'];
        $rec['cnt_pastors']      = (int)$rec['cnt_pastors'];
        $rec['cnt_pastors_wife'] = (int)$rec['cnt_pastors_wife'];
        $rec['cnt_elders']       = (int)$rec['cnt_elders'];
        $rec['cnt_dcn']          = (int)$rec['cnt_dcn'];
        $rec['cnt_dcns']         = (int)$rec['cnt_dcns'];
        $rec['cnt_men']          = (int)$rec['cnt_men'];
        $rec['cnt_women']        = (int)$rec['cnt_women'];
        $rec['cnt_youth']        = (int)$rec['cnt_youth'];
        $rec['cnt_children']     = (int)$rec['cnt_children'];
        $rec['cnt_visitors']     = (int)$rec['cnt_visitors'];
        $rec['cnt_new_converts'] = (int)$rec['cnt_new_converts'];
        $rec['cnt_guests']       = (int)$rec['cnt_guests'];
        $rec['adult_total']      = (int)$rec['adult_total'];
        $rec['grand_total']      = (int)$rec['grand_total'];
        $rec['officers_total']   = (int)$rec['officers_total'];
        $rec['offering_amount']  = (float)$rec['offering_amount'];
        $rec['tithe_amount']     = (float)$rec['tithe_amount'];
        $rec['com_officers']     = (int)$rec['com_officers'];
        $rec['com_male']         = (int)$rec['com_male'];
        $rec['com_female']       = (int)$rec['com_female'];
        $rec['communion_total']  = (int)$rec['communion_total'];
        $rec['bs_male']          = (int)$rec['bs_male'];
        $rec['bs_female']        = (int)$rec['bs_female'];
        $rec['bs_total']         = (int)$rec['bs_total'];
    }
    unset($rec);

    echo json_encode([
        'success' => true,
        'records' => $records,
    ]);

} catch (PDOException $e) {
    error_log('get_attendance.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load records.',
    ]);
}
