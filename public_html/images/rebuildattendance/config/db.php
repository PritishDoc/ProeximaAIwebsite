<?php
// Set timezone to Asia/Kolkata for attendance window checks
date_default_timezone_set('Asia/Kolkata');
// Database Configuration
$host = "localhost";
$port = 3306;
$db   = "u527069138_proeximaai_db"; 
$user = "u527069138_proeximaai";
$pass = '!cB>3ReP;Pe8';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );

    // --- AUTO-CREATE TABLES IF MISSING ---
    $check = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if (!$check) {
        $sqlPath = __DIR__ . '/../database.sql';
        if (file_exists($sqlPath)) {
            $sql = file_get_contents($sqlPath);
            // Replace old admin email if present in SQL
            $sql = str_replace('admin@fitness.com', 'admin@rebuildtime.com', $sql);
            
            // Split and execute
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    $pdo->exec($stmt);
                }
            }
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("DB Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
