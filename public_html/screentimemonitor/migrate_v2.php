<?php
/**
 * Phase 2 — Database Migration
 * Adds: projects, invitations tables, users.project_id + users.designation
 */
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    // 1. Create projects table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `projects` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `description` TEXT,
            `status` ENUM('active','archived') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Created projects table\n";
    
    // 2. Create invitations table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `invitations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(150) NOT NULL,
            `token` VARCHAR(64) NOT NULL UNIQUE,
            `project_id` INT DEFAULT NULL,
            `invited_by` INT NOT NULL,
            `status` ENUM('pending','accepted','expired') NOT NULL DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `expires_at` DATETIME NOT NULL,
            FOREIGN KEY (`invited_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Created invitations table\n";
    
    // 3. Add project_id and designation to users
    // Check if columns exist first
    $cols = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('project_id', $cols)) {
        $db->exec("ALTER TABLE `users` ADD COLUMN `project_id` INT DEFAULT NULL");
        $db->exec("ALTER TABLE `users` ADD FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL");
        echo "✅ Added users.project_id\n";
    } else {
        echo "⏭️  users.project_id already exists\n";
    }
    
    if (!in_array('designation', $cols)) {
        $db->exec("ALTER TABLE `users` ADD COLUMN `designation` VARCHAR(50) DEFAULT NULL AFTER `role`");
        echo "✅ Added users.designation\n";
    } else {
        echo "⏭️  users.designation already exists\n";
    }
    
    // 4. Create alerts table for close notifications
    $db->exec("
        CREATE TABLE IF NOT EXISTS `alerts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `alert_type` VARCHAR(50) NOT NULL,
            `message` TEXT,
            `is_read` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Created alerts table\n";
    
    // 5. Insert a default project
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM projects");
    if ($stmt->fetch()['cnt'] == 0) {
        $db->exec("INSERT INTO projects (name, description) VALUES ('Default Project', 'Default project for all employees')");
        echo "✅ Created default project\n";
    }
    
    echo "\n🎉 Migration complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
