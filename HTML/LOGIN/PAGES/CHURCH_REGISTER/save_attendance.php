<?php
// ============================================================
//  save_attendance.php
//  Method : POST
//  Body   : JSON payload from buildPayload() in the register
//  Returns: { success: bool, id: int, message: string }
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

require_once __DIR__ . '/db_connect.php';

// -- Read & decode request body --
$raw = file_get_contents('php://input');
$p   = json_decode($raw, true);

if (!$p) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

// -- Helper: safe int / float / string, reading $arr[$key] without
//    triggering "Undefined array key" warnings when the key is absent.
//    (Previously these took the value directly, e.g. si($p, 'cnt_apostles') —
//    but PHP evaluates that array access at the call site before the
//    function ever runs, so a missing key warned regardless of the
//    isset() check inside the function body.)
function si(array $arr, string $key, int $default = 0): int {
    return isset($arr[$key]) ? (int)$arr[$key] : $default;
}
function sf(array $arr, string $key, float $default = 0.0): float {
    return isset($arr[$key]) ? (float)$arr[$key] : $default;
}
function ss(array $arr, string $key, string $default = ''): string {
    return isset($arr[$key]) ? trim((string)$arr[$key]) : $default;
}

// -- Compute derived totals (mirrors JS logic) --
$adultTotal = si($p, 'cnt_apostles')  + si($p, 'cnt_apostles_wife')
            + si($p, 'cnt_pastors')   + si($p, 'cnt_pastors_wife')
            + si($p, 'cnt_elders')    + si($p, 'cnt_dcn')
            + si($p, 'cnt_dcns')      + si($p, 'cnt_men')
            + si($p, 'cnt_women')     + si($p, 'cnt_youth')
            + si($p, 'cnt_visitors')  + si($p, 'cnt_new_converts')
            + si($p, 'cnt_guests');

$grandTotal    = $adultTotal + si($p, 'cnt_children');
$officersTotal = si($p, 'cnt_elders') + si($p, 'cnt_dcn') + si($p, 'cnt_dcns');
$communionTotal = si($p, 'com_officers') + si($p, 'com_male') + si($p, 'com_female');
$bsTotal       = si($p, 'bs_male') + si($p, 'bs_female');

try {
    $pdo->beginTransaction();

    // -- Insert main record --
    $stmt = $pdo->prepare("
        INSERT INTO attendance_records (
            service_date, service_type, service_time, minister,
            cnt_apostles, cnt_apostles_wife,
            cnt_pastors,  cnt_pastors_wife,
            cnt_elders,   cnt_dcn,  cnt_dcns,
            cnt_men,      cnt_women, cnt_youth, cnt_children,
            cnt_visitors, cnt_new_converts, cnt_guests,
            adult_total,  grand_total, officers_total,
            offering_amount, tithe_amount,
            com_officers, com_male, com_female, communion_total,
            bs_male, bs_female, bs_total,
            activities, prayer_request, notes
        ) VALUES (
            :service_date, :service_type, :service_time, :minister,
            :cnt_apostles, :cnt_apostles_wife,
            :cnt_pastors,  :cnt_pastors_wife,
            :cnt_elders,   :cnt_dcn,  :cnt_dcns,
            :cnt_men,      :cnt_women, :cnt_youth, :cnt_children,
            :cnt_visitors, :cnt_new_converts, :cnt_guests,
            :adult_total,  :grand_total, :officers_total,
            :offering_amount, :tithe_amount,
            :com_officers, :com_male, :com_female, :communion_total,
            :bs_male, :bs_female, :bs_total,
            :activities, :prayer_request, :notes
        )
    ");

    $stmt->execute([
        ':service_date'      => ss($p, 'service_date'),
        ':service_type'      => ss($p, 'service_type'),
        ':service_time'      => ss($p, 'service_time'),
        ':minister'          => ss($p, 'minister'),
        ':cnt_apostles'      => si($p, 'cnt_apostles'),
        ':cnt_apostles_wife' => si($p, 'cnt_apostles_wife'),
        ':cnt_pastors'       => si($p, 'cnt_pastors'),
        ':cnt_pastors_wife'  => si($p, 'cnt_pastors_wife'),
        ':cnt_elders'        => si($p, 'cnt_elders'),
        ':cnt_dcn'           => si($p, 'cnt_dcn'),
        ':cnt_dcns'          => si($p, 'cnt_dcns'),
        ':cnt_men'           => si($p, 'cnt_men'),
        ':cnt_women'         => si($p, 'cnt_women'),
        ':cnt_youth'         => si($p, 'cnt_youth'),
        ':cnt_children'      => si($p, 'cnt_children'),
        ':cnt_visitors'      => si($p, 'cnt_visitors'),
        ':cnt_new_converts'  => si($p, 'cnt_new_converts'),
        ':cnt_guests'        => si($p, 'cnt_guests'),
        ':adult_total'       => $adultTotal,
        ':grand_total'       => $grandTotal,
        ':officers_total'    => $officersTotal,
        ':offering_amount'   => sf($p, 'offering_amount'),
        ':tithe_amount'      => sf($p, 'tithe_amount'),
        ':com_officers'      => si($p, 'com_officers'),
        ':com_male'          => si($p, 'com_male'),
        ':com_female'        => si($p, 'com_female'),
        ':communion_total'   => $communionTotal,
        ':bs_male'           => si($p, 'bs_male'),
        ':bs_female'         => si($p, 'bs_female'),
        ':bs_total'          => $bsTotal,
        ':activities'        => ss($p, 'activities'),
        ':prayer_request'    => ss($p, 'prayer_request'),
        ':notes'             => ss($p, 'notes'),
    ]);

    $recordId = (int)$pdo->lastInsertId();

    // -- Insert others (guests / visitors / new converts) --
    if (!empty($p['others']) && is_array($p['others'])) {
        $othersStmt = $pdo->prepare("
            INSERT INTO attendance_others
                (record_id, category, first_name, second_name, location, phone, status)
            VALUES
                (:record_id, :category, :first_name, :second_name, :location, :phone, :status)
        ");

        foreach ($p['others'] as $o) {
            if (!is_array($o)) continue;
            $othersStmt->execute([
                ':record_id'   => $recordId,
                ':category'    => trim((string)($o['category']    ?? '')),
                ':first_name'  => trim((string)($o['first_name']  ?? '')),
                ':second_name' => trim((string)($o['second_name'] ?? '')),
                ':location'    => trim((string)($o['location']    ?? '')),
                ':phone'       => trim((string)($o['phone']       ?? '')),
                ':status'      => trim((string)($o['status']      ?? '')),
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'id'      => $recordId,
        'message' => 'Record saved successfully.',
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('save_attendance.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save record.',
    ]);
}
