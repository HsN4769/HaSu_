-- Wedding Registration System Database Schema
-- Updated for user registration system

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `harusi_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `harusi_db`;

-- Users table (for authentication and registration)
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `email` varchar(255) NOT NULL UNIQUE,
    `phone` varchar(20) DEFAULT NULL,
    `relationship` varchar(50) NOT NULL,
    `guest_count` int(11) DEFAULT 1,
    `message` text DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('admin','guest') DEFAULT 'guest',
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `email_index` (`email`),
    KEY `role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Guests table (for RSVP registrations)
CREATE TABLE IF NOT EXISTS `guests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `full_name` varchar(200) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `guest_count` int(11) DEFAULT 1,
    `message` text DEFAULT NULL,
    `qr_code` varchar(255) DEFAULT NULL,
    `status` enum('pending','confirmed','declined') DEFAULT 'pending',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id_index` (`user_id`),
    KEY `email_index` (`email`),
    KEY `status_index` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table (for gift contributions)
CREATE TABLE IF NOT EXISTS `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `guest_id` int(11) DEFAULT NULL,
    `transaction_id` varchar(100) UNIQUE NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `currency` varchar(3) DEFAULT 'TZS',
    `payment_method` varchar(50) NOT NULL,
    `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
    `qr_code` varchar(255) DEFAULT NULL,
    `message` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id_index` (`user_id`),
    KEY `guest_id_index` (`guest_id`),
    KEY `transaction_id_index` (`transaction_id`),
    KEY `status_index` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`guest_id`) REFERENCES `guests`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR Codes table (for tracking generated QR codes)
CREATE TABLE IF NOT EXISTS `qr_codes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `type` enum('guest','payment') NOT NULL,
    `reference_id` int(11) NOT NULL,
    `qr_data` text NOT NULL,
    `filename` varchar(255) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `type_index` (`type`),
    KEY `reference_id_index` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table (for system configuration)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) UNIQUE NOT NULL,
    `setting_value` text DEFAULT NULL,
    `description` text DEFAULT NULL,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `setting_key_index` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Log table (for tracking user actions)
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `action` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id_index` (`user_id`),
    KEY `action_index` (`action`),
    KEY `created_at_index` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `phone`, `relationship`, `guest_count`, `password`, `role`, `is_active`) VALUES
('Admin', 'User', 'admin@harusi.com', '+255123456789', 'admin', 1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('wedding_date', '2025-12-20', 'Date of the wedding'),
('wedding_time', '10:00:00', 'Time of the wedding'),
('wedding_venue', 'Dar es Salaam, Tanzania', 'Venue of the wedding'),
('max_guests', '500', 'Maximum number of guests'),
('registration_enabled', '1', 'Whether guest registration is enabled'),
('payment_enabled', '1', 'Whether payment system is enabled'),
('system_name', 'Harusi ya HAMISI na SUBIRA', 'Name of the wedding system'),
('contact_email', 'info@harusi.com', 'Contact email for the wedding');

-- Create views for easier reporting
CREATE OR REPLACE VIEW `guest_summary` AS
SELECT 
    COUNT(*) as total_guests,
    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_guests,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_guests,
    COUNT(CASE WHEN status = 'declined' THEN 1 END) as declined_guests,
    SUM(guest_count) as total_guest_count
FROM guests;

CREATE OR REPLACE VIEW `payment_summary` AS
SELECT 
    COUNT(*) as total_payments,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount,
    AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as average_amount
FROM payments;

CREATE OR REPLACE VIEW `user_summary` AS
SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_users,
    COUNT(CASE WHEN role = 'guest' THEN 1 END) as guest_users,
    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_users
FROM users;
