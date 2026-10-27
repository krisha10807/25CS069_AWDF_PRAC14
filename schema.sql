-- ============================================================================
-- StudentHub Portal - Practical 14: Role-Based Dashboard & Audit Trail
-- Course: Web Development Frameworks (WDF)
-- Student Name: Krisha Patel
-- Student ID: 25CS069
-- Database Name: studenthub_p14
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub_p14` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_p14`;

-- ----------------------------------------------------------------------------
-- Table 1: `users`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin', 'Student') NOT NULL DEFAULT 'Student',
    `department` VARCHAR(100) DEFAULT 'Computer Science & Engineering',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table 2: `events`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `event_date` DATETIME NOT NULL,
    `venue` VARCHAR(150) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table 3: `audit_logs` (Stores security events and administrative actions)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `user_email` VARCHAR(100) NOT NULL,
    `user_role` VARCHAR(50) NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL DEFAULT '127.0.0.1',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_action` (`action`),
    INDEX `idx_user_email` (`user_email`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Initial Seed Records
-- Passwords: Admin@123 / Student@123 (BCRYPT hashed)
-- ----------------------------------------------------------------------------
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `department`, `created_at`) VALUES
(1, 'Krisha Patel (Admin)', 'admin@charusat.ac.in', '$2y$10$XK58foVH77BwuIoym5BzLeCoLALi5BV44DbPEcBK3SWKNLUoTQNb2', 'Admin', 'Department of Computer Science', '2026-10-20 09:00:00'),
(2, 'Krisha Patel (25CS069)', 'student@charusat.ac.in', '$2y$10$a1OFKaxlroTWBQtdOGoXx.0.cZz9aGk24EahRM0/RsdgtSQSMnKDi', 'Student', 'Computer Science & Engineering', '2026-10-21 10:15:00'),
(3, 'Rohan Shah', 'rohan.shah@charusat.ac.in', '$2y$10$a1OFKaxlroTWBQtdOGoXx.0.cZz9aGk24EahRM0/RsdgtSQSMnKDi', 'Student', 'Information Technology', '2026-10-22 11:30:00'),
(4, 'Aarav Sharma', 'aarav.sharma@charusat.ac.in', '$2y$10$a1OFKaxlroTWBQtdOGoXx.0.cZz9aGk24EahRM0/RsdgtSQSMnKDi', 'Student', 'Artificial Intelligence', '2026-10-23 14:00:00'),
(5, 'Diya Mehta', 'diya.mehta@charusat.ac.in', '$2y$10$a1OFKaxlroTWBQtdOGoXx.0.cZz9aGk24EahRM0/RsdgtSQSMnKDi', 'Student', 'Computer Science & Engineering', '2026-10-24 16:45:00');

INSERT INTO `events` (`id`, `title`, `category`, `event_date`, `venue`, `created_at`) VALUES
(1, 'CHARUSAT Annual Hackathon 2026', 'Technical', '2026-11-15 09:00:00', 'CSPIT Lab 301 & 302', '2026-10-20 10:00:00'),
(2, 'Gen-AI & Autonomous Agents Masterclass', 'Workshop', '2026-11-22 10:30:00', 'Seminar Hall 1, CSPIT', '2026-10-21 11:30:00'),
(3, 'Spandan Youth Cultural Gala 2026', 'Cultural', '2026-11-28 17:30:00', 'Central University Amphitheatre', '2026-10-22 12:00:00'),
(4, 'Cloud Infrastructure & DevOps Summit', 'Technical', '2026-12-05 10:00:00', 'Auditorium B', '2026-10-23 15:00:00');

INSERT INTO `audit_logs` (`id`, `user_id`, `user_email`, `user_role`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'admin@charusat.ac.in', 'Admin', 'SYSTEM_INITIALIZATION', 'System tables and initial RBAC security initialized by Administrator.', '127.0.0.1', '2026-10-20 09:05:00'),
(2, 1, 'admin@charusat.ac.in', 'Admin', 'EVENT_CREATED', 'Admin published new campus event: "CHARUSAT Annual Hackathon 2026".', '127.0.0.1', '2026-10-20 10:02:00'),
(3, 2, 'student@charusat.ac.in', 'Student', 'USER_REGISTRATION', 'Student account self-registered: Krisha Patel (25CS069).', '127.0.0.1', '2026-10-21 10:15:00'),
(4, 1, 'admin@charusat.ac.in', 'Admin', 'ROLE_VERIFIED', 'Admin verified academic credentials for student account 25CS069.', '127.0.0.1', '2026-10-21 10:30:00'),
(5, 1, 'admin@charusat.ac.in', 'Admin', 'EVENT_CREATED', 'Admin scheduled workshop: "Gen-AI & Autonomous Agents Masterclass".', '127.0.0.1', '2026-10-21 11:31:00'),
(6, 3, 'rohan.shah@charusat.ac.in', 'Student', 'USER_REGISTRATION', 'Student registration recorded: Rohan Shah (IT Department).', '127.0.0.1', '2026-10-22 11:30:00'),
(7, 1, 'admin@charusat.ac.in', 'Admin', 'SECURITY_POLICY_UPDATE', 'Session inactivity timeout updated to 300 seconds across all role portals.', '127.0.0.1', '2026-10-22 14:10:00'),
(8, 2, 'student@charusat.ac.in', 'Student', 'UNAUTHORIZED_ATTEMPT', 'Student Krisha Patel attempted direct access to restricted admin resource /audit_logs.php (Blocked: 403 Forbidden).', '127.0.0.1', '2026-10-23 09:45:00');
