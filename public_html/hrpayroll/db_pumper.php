<?php

$sqliteDbFile = __DIR__ . '/database/database.sqlite';

try {
    echo "Connecting to SQLite...\n";
    $sqlite = new PDO('sqlite:' . $sqliteDbFile);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connecting to MySQL Remote...\n";
    $mysql = new PDO(
        'mysql:host=193.203.184.197;port=3306;dbname=u527069138_HRPayroll_db;charset=utf8mb4',
        'u527069138_HRPayroll_db',
        '1d+PbNG59r?A'
    );
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable foreign key checks on mysql
    $mysql->exec('SET FOREIGN_KEY_CHECKS=0;');

    // Get all tables
    $stmt = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT IN ('sqlite_sequence', 'migrations')");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "Exporting table: $table...\n";
        
        // Empty the MySQL table first
        $mysql->exec("TRUNCATE TABLE `$table`");
        
        $rows = $sqlite->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if(count($rows) === 0) {
            echo " - 0 rows\n";
            continue;
        }

        echo " - " . count($rows) . " rows found. Inserting...\n";

        // Insert rows into MySQL
        foreach(array_chunk($rows, 100) as $chunk) {
            $columns = array_keys($chunk[0]);
            $colStr = implode('`, `', $columns);
            
            $placeholdersList = [];
            $values = [];
            
            foreach ($chunk as $row) {
                $placeholders = [];
                foreach ($columns as $col) {
                    $placeholders[] = '?';
                    $values[] = $row[$col];
                }
                $placeholdersList[] = '(' . implode(',', $placeholders) . ')';
            }
            
            $sql = "INSERT INTO `$table` (`$colStr`) VALUES " . implode(', ', $placeholdersList);
            $insertStmt = $mysql->prepare($sql);
            $insertStmt->execute($values);
        }
    }

    $mysql->exec('SET FOREIGN_KEY_CHECKS=1;');
    echo "\nSUCCESS! All data perfectly migrated!\n";

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
