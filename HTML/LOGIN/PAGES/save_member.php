<?php
// =====================================================================
// save_member.php — saves a new member record
// (Register_New_Member.html "regForm" submission)
// Expects POST (multipart/form-data): all member fields + family_name[],
//          family_age[], family_relation[]
// Returns JSON: { success, member_id } or { success:false, errors/message }
// =====================================================================

session_start();
require 'db_config.php';
$pdo = getDBConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ---------------------------------------------------------------------
// Helper: get trimmed POST value or null if empty
// ---------------------------------------------------------------------
function val($key) {
    $v = trim($_POST[$key] ?? '');
    return $v === '' ? null : $v;
}

// ---------------------------------------------------------------------
// Validation
// ---------------------------------------------------------------------
$errors = [];

$position    = val('position');
$firstName   = val('first_name');
$middleName  = val('middle_name');
$surname     = val('surname');
$phone1      = val('phone1');
$phone2      = val('phone2');
$emergency   = val('emergency');
$email       = val('email');
$dob         = val('dob');
$gender      = val('gender');
$hometown    = val('hometown');
$nationality = val('nationality');
$occupation  = val('occupation');
$maritalStatus = val('marital_status');
$address     = val('address');
$location    = val('location');

if (!$position)    $errors[] = 'Position is required.';
if (!$firstName)   $errors[] = 'First name is required.';
if (!$surname)     $errors[] = 'Surname is required.';
if (!$phone1)      $errors[] = 'Phone No. 1 is required.';
if (!$emergency)   $errors[] = 'Emergency contact is required.';
if (!$dob)         $errors[] = 'Date of birth is required.';
if (!$gender)      $errors[] = 'Gender is required.';
if (!$hometown)    $errors[] = 'Hometown is required.';
if (!$nationality) $errors[] = 'Nationality is required.';
if (!$occupation)  $errors[] = 'Occupation is required.';
if (!$address)     $errors[] = 'Residential address is required.';
if (!$location)    $errors[] = 'Town / City of Residence is required.';

if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email address is not valid.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Auto-calculate age from DOB (server-side, authoritative)
$age = null;
try {
    $dobDate = new DateTime($dob);
    $today   = new DateTime();
    $age     = $today->diff($dobDate)->y;
} catch (Exception $e) {
    $age = null;
}

// Parents Information
$motherName   = val('mother_name');
$motherStatus = val('mother_status');
$fatherName   = val('father_name');
$fatherStatus = val('father_status');

// Church Information
$waterBaptised      = val('water_baptised');
$waterBaptismDate   = val('water_baptism_date');
$holyspiritBaptised = val('holyspirit_baptised');
$ministry           = val('ministry');
$status             = val('status');
$zone               = val('zone');
$gps                = val('gps');

// Spouse Details
$spouseName       = val('spouse_name');
$spousePhone      = val('spouse_phone');
$spouseOccupation = val('spouse_occupation');
$spouseHometown   = val('spouse_hometown');
$spouseReligion   = val('spouse_religion');
$placeOfWorship   = val('place_of_worship');

// Family members (dynamic arrays)
$familyNames     = $_POST['family_name']     ?? [];
$familyAges      = $_POST['family_age']      ?? [];
$familyRelations = $_POST['family_relation'] ?? [];

// ---------------------------------------------------------------------
// Insert into database (transaction)
// ---------------------------------------------------------------------
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO members (
            position, first_name, middle_name, surname,
            phone1, phone2, emergency_contact, email,
            date_of_birth, age, gender, hometown, nationality,
            occupation, marital_status, residential_address, location,
            mother_name, mother_status, father_name, father_status,
            water_baptised, water_baptism_date, holyspirit_baptised,
            ministry, status, zone, gps_address,
            spouse_name, spouse_phone, spouse_occupation, spouse_hometown,
            spouse_religion, place_of_worship,
            created_by
        ) VALUES (
            :position, :first_name, :middle_name, :surname,
            :phone1, :phone2, :emergency_contact, :email,
            :date_of_birth, :age, :gender, :hometown, :nationality,
            :occupation, :marital_status, :residential_address, :location,
            :mother_name, :mother_status, :father_name, :father_status,
            :water_baptised, :water_baptism_date, :holyspirit_baptised,
            :ministry, :status, :zone, :gps_address,
            :spouse_name, :spouse_phone, :spouse_occupation, :spouse_hometown,
            :spouse_religion, :place_of_worship,
            :created_by
        )'
    );

    $stmt->execute([
        'position'             => $position,
        'first_name'           => $firstName,
        'middle_name'          => $middleName,
        'surname'              => $surname,
        'phone1'               => $phone1,
        'phone2'               => $phone2,
        'emergency_contact'    => $emergency,
        'email'                => $email,
        'date_of_birth'        => $dob,
        'age'                  => $age,
        'gender'               => $gender,
        'hometown'             => $hometown,
        'nationality'          => $nationality,
        'occupation'           => $occupation,
        'marital_status'       => $maritalStatus,
        'residential_address'  => $address,
        'location'             => $location,
        'mother_name'          => $motherName,
        'mother_status'        => $motherStatus,
        'father_name'          => $fatherName,
        'father_status'        => $fatherStatus,
        'water_baptised'       => $waterBaptised,
        'water_baptism_date'   => $waterBaptismDate,
        'holyspirit_baptised'  => $holyspiritBaptised,
        'ministry'             => $ministry,
        'status'               => $status,
        'zone'                 => $zone,
        'gps_address'          => $gps,
        'spouse_name'          => $spouseName,
        'spouse_phone'         => $spousePhone,
        'spouse_occupation'    => $spouseOccupation,
        'spouse_hometown'      => $spouseHometown,
        'spouse_religion'      => $spouseReligion,
        'place_of_worship'     => $placeOfWorship,
        'created_by'           => $_SESSION['user_id'] ?? null,
    ]);

    $memberId = (int)$pdo->lastInsertId();

    // Insert family members (if any)
    if (!empty($familyNames)) {
        $famStmt = $pdo->prepare(
            'INSERT INTO family_members (member_id, full_name, age, relationship)
             VALUES (:member_id, :full_name, :age, :relationship)'
        );

        foreach ($familyNames as $i => $famName) {
            $famName = trim($famName);
            if ($famName === '') continue;

            $famAge      = isset($familyAges[$i]) && $familyAges[$i] !== '' ? (int)$familyAges[$i] : null;
            $famRelation = $familyRelations[$i] ?? null;

            if (!$famRelation) continue; // relationship is required per row

            $famStmt->execute([
                'member_id'    => $memberId,
                'full_name'    => $famName,
                'age'          => $famAge,
                'relationship' => $famRelation,
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success'   => true,
        'member_id' => $memberId,
        'message'   => 'Member registered successfully.',
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
}
