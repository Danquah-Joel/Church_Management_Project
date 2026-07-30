<?php
// =====================================================================
// signup.php — DEPRECATED
//
// This file has been merged into register.php which is the single
// canonical endpoint for creating new user accounts.
//
// FIX: signup.php and register.php were duplicates with conflicting
//      is_active values (signup.php set 0, register.php set 1).
//      register.php is now the authoritative version with:
//        - Server-side admin code validation
//        - REQUEST_METHOD guard
//        - DB error handling
//        - is_active = 1 (admin code verified before account is created)
//
// All registration requests should go to register.php.
// You can safely delete this file once you've confirmed nothing else
// in your codebase references signup.php.
// =====================================================================

header('Content-Type: application/json');

echo json_encode([
    'success' => false,
    'message' => 'This endpoint is deprecated. Please use register.php.',
]);
