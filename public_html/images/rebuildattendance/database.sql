-- Fitness Attendance App Database Schema
-- Import this file via phpMyAdmin on Hostinger

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` VARCHAR(150) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `password` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  `latitude` DECIMAL(10,7) NOT NULL,
  `longitude` DECIMAL(10,7) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_date` (`user_id`, `date`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('location_override', 'off') ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- Default admin user (password: admin123)
INSERT INTO `users` (`name`, `email`, `password`, `is_admin`) VALUES
('Admin', 'admin@rebuildtime.com', '$2y$10$C8.cM/pQGfNqL3XEqnK8PeE9t8GkI2Qo5oF6yE5zJ7Y8eRuD6X9G.', 1);
