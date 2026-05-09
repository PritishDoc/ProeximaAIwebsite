-- ============================================
-- Employee Monitoring System — Database Schema
-- ============================================
-- Run this SQL in phpMyAdmin or MySQL client
-- to create all required tables.
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";

-- -----------------------------------------------
-- Table: users
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('employee','admin') NOT NULL DEFAULT 'employee',
  `status` ENUM('active','idle','offline') NOT NULL DEFAULT 'offline',
  `last_activity` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: sessions
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `login_time` DATETIME NOT NULL,
  `logout_time` DATETIME DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: screenshots
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `screenshots` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `image_path` VARCHAR(500) NOT NULL,
  `file_size` INT DEFAULT 0,
  `captured_at` DATETIME NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_captured` (`user_id`, `captured_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: activity_logs
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `mouse_clicks` INT DEFAULT 0,
  `mouse_distance` FLOAT DEFAULT 0,
  `key_presses` INT DEFAULT 0,
  `idle_seconds` INT DEFAULT 0,
  `period_start` DATETIME NOT NULL,
  `period_end` DATETIME NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_period` (`user_id`, `period_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: settings
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Default settings
-- -----------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('screenshot_interval', '10'),
('retention_days', '30'),
('idle_threshold', '120'),
('activity_send_interval', '30');

-- -----------------------------------------------
-- NOTE: Default users are created by setup.php
-- Run setup.php in your browser after importing
-- this schema to create the admin account.
-- -----------------------------------------------
