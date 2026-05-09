<?php
/**
 * Database Configuration
 * ----------------------
 * Update these credentials to match your MySQL server.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'u527069138_ScreenMonitor');
define('DB_USER', 'u527069138_ScreenMonitor');
define('DB_PASS', 'k3/xdV5V?');
define('DB_CHARSET', 'utf8mb4');

// Set PHP Timezone to IST
date_default_timezone_set('Asia/Kolkata');

/**
 * Get PDO database connection (singleton)
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . ", time_zone = '+05:30'"
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    }
    
    return $pdo;
}
