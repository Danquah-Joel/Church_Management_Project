<?php
// =====================================================================
// db_config.php — Database connection settings
// Used by: login.php, register.php, save_member.php, member_records.php,
//          dashboard_stats.php, graph_data.php
// =====================================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'church_management'); // <-- change to your database name
define('DB_USER', 'root');       // <-- change to your MySQL username
define('DB_PASS', '');           // <-- change to your MySQL password
define('DB_CHARSET', 'utf8mb4');
define('ADMIN_REGISTRATION_CODE', '12345'); // use your real 5-digit code
/**
 * Returns a PDO connection to the database.
 * Throws PDOException on failure.
 *
 * FIX: this used to catch its own PDOException and echo $e->getMessage()
 * straight into the JSON response (then exit), which leaks DB host/user/
 * driver details to the client on any connection failure — and it also
 * meant the exception never reached login.php/register.php's own
 * try/catch, whose entire purpose is to log the real error server-side
 * via error_log() and show the client a generic message instead. Now it
 * just lets PDO's exception propagate so that intended handling actually
 * runs. Every caller listed above already wraps this call in a
 * try/catch — only update this function if you also update them.
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}
