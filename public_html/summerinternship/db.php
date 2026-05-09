<?php
$host = '193.203.184.197';
$dbname = 'u527069138_test_db';
$username = 'u527069138_test_user';
$password = 'H@*EbX+z7';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create applications table if it doesn't exist (Original schema)
    $createTableQuery = "
    CREATE TABLE IF NOT EXISTS applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        education VARCHAR(255) NOT NULL,
        program VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($createTableQuery);

    // Dynamically update schema with new fields to avoid dropping table
    $columnsToAdd = [
        "resume_path VARCHAR(255)",
        "gender VARCHAR(50)",
        "location VARCHAR(255)",
        "college VARCHAR(255)",
        "qualification VARCHAR(255)",
        "course_branch VARCHAR(255)",
        "semester VARCHAR(50)",
        "payment_screenshot VARCHAR(255)"
    ];
    
    foreach ($columnsToAdd as $colDef) {
        $parts = explode(" ", $colDef);
        $colName = $parts[0];
        try {
            $pdo->exec("ALTER TABLE applications ADD COLUMN $colDef");
        } catch (PDOException $e) {
            // Column already exists or error, safely ignore to continue execution.
        }
    }

} catch (PDOException $e) {
    die("Database connection failed. <strong>Error Details:</strong> " . $e->getMessage() . "<br><br><em>Note: If the error says 'Connection timed out' or 'Access denied', your current internet IP address is likely blocked or not whitelisted in your server's Remote MySQL settings.</em>");
}
?>
