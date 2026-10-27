<?php
/**
 * StudentHub Portal - Practical 14: Database Connection & Seed Setup (db.php)
 * Course: Web Development Frameworks (WDF)
 * Student Name: Krisha Patel
 * Student ID: 25CS069
 * 
 * Provides database connection using PDO with native MySQL support
 * and seamless resilient portable SQLite fallback.
 */

$host     = 'localhost';
$db_name  = 'studenthub_p14';
$username = 'root';
$password = '';
$charset  = 'utf8mb4';

$pdo = null;
$db_driver = 'MySQL PDO';

try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (Throwable $e) {
    // Resilient portable SQLite fallback
    $sqlite_file = __DIR__ . '/studenthub_p14.db';
    $is_new = !file_exists($sqlite_file) || filesize($sqlite_file) === 0;

    try {
        $pdo = new PDO("sqlite:" . $sqlite_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db_driver = 'SQLite Portable PDO';

        if ($is_new) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    full_name TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    role TEXT NOT NULL DEFAULT 'Student',
                    department TEXT DEFAULT 'Computer Science & Engineering',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS events (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    category TEXT NOT NULL,
                    event_date DATETIME NOT NULL,
                    venue TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS audit_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER,
                    user_email TEXT NOT NULL,
                    user_role TEXT NOT NULL,
                    action TEXT NOT NULL,
                    details TEXT NOT NULL,
                    ip_address TEXT NOT NULL DEFAULT '127.0.0.1',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");

            // Seed Users
            $admin_hash = password_hash('Admin@123', PASSWORD_BCRYPT);
            $student_hash = password_hash('Student@123', PASSWORD_BCRYPT);

            $stmt_u = $pdo->prepare("INSERT INTO users (full_name, email, password, role, department, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_u->execute(['Krisha Patel (Admin)', 'admin@charusat.ac.in', $admin_hash, 'Admin', 'Department of Computer Science', '2026-10-20 09:00:00']);
            $stmt_u->execute(['Krisha Patel (25CS069)', 'student@charusat.ac.in', $student_hash, 'Student', 'Computer Science & Engineering', '2026-10-21 10:15:00']);
            $stmt_u->execute(['Rohan Shah', 'rohan.shah@charusat.ac.in', $student_hash, 'Student', 'Information Technology', '2026-10-22 11:30:00']);
            $stmt_u->execute(['Aarav Sharma', 'aarav.sharma@charusat.ac.in', $student_hash, 'Student', 'Artificial Intelligence', '2026-10-23 14:00:00']);
            $stmt_u->execute(['Diya Mehta', 'diya.mehta@charusat.ac.in', $student_hash, 'Student', 'Computer Science & Engineering', '2026-10-24 16:45:00']);

            // Seed Events
            $stmt_e = $pdo->prepare("INSERT INTO events (title, category, event_date, venue, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt_e->execute(['CHARUSAT Annual Hackathon 2026', 'Technical', '2026-11-15 09:00:00', 'CSPIT Lab 301 & 302', '2026-10-20 10:00:00']);
            $stmt_e->execute(['Gen-AI & Autonomous Agents Masterclass', 'Workshop', '2026-11-22 10:30:00', 'Seminar Hall 1, CSPIT', '2026-10-21 11:30:00']);
            $stmt_e->execute(['Spandan Youth Cultural Gala 2026', 'Cultural', '2026-11-28 17:30:00', 'Central University Amphitheatre', '2026-10-22 12:00:00']);
            $stmt_e->execute(['Cloud Infrastructure & DevOps Summit', 'Technical', '2026-12-05 10:00:00', 'Auditorium B', '2026-10-23 15:00:00']);

            // Seed Audit Logs
            $stmt_a = $pdo->prepare("INSERT INTO audit_logs (user_id, user_email, user_role, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_a->execute([1, 'admin@charusat.ac.in', 'Admin', 'SYSTEM_INITIALIZATION', 'System tables and initial RBAC security initialized by Administrator.', '127.0.0.1', '2026-10-20 09:05:00']);
            $stmt_a->execute([1, 'admin@charusat.ac.in', 'Admin', 'EVENT_CREATED', 'Admin published new campus event: "CHARUSAT Annual Hackathon 2026".', '127.0.0.1', '2026-10-20 10:02:00']);
            $stmt_a->execute([2, 'student@charusat.ac.in', 'Student', 'USER_REGISTRATION', 'Student account self-registered: Krisha Patel (25CS069).', '127.0.0.1', '2026-10-21 10:15:00']);
            $stmt_a->execute([1, 'admin@charusat.ac.in', 'Admin', 'ROLE_VERIFIED', 'Admin verified academic credentials for student account 25CS069.', '127.0.0.1', '2026-10-21 10:30:00']);
            $stmt_a->execute([1, 'admin@charusat.ac.in', 'Admin', 'EVENT_CREATED', 'Admin scheduled workshop: "Gen-AI & Autonomous Agents Masterclass".', '127.0.0.1', '2026-10-21 11:31:00']);
            $stmt_a->execute([3, 'rohan.shah@charusat.ac.in', 'Student', 'USER_REGISTRATION', 'Student registration recorded: Rohan Shah (IT Department).', '127.0.0.1', '2026-10-22 11:30:00']);
            $stmt_a->execute([1, 'admin@charusat.ac.in', 'Admin', 'SECURITY_POLICY_UPDATE', 'Session inactivity timeout updated to 300 seconds across all role portals.', '127.0.0.1', '2026-10-22 14:10:00']);
            $stmt_a->execute([2, 'student@charusat.ac.in', 'Student', 'UNAUTHORIZED_ATTEMPT', 'Student Krisha Patel attempted direct access to restricted admin resource /audit_logs.php (Blocked: 403 Forbidden).', '127.0.0.1', '2026-10-23 09:45:00']);
        }
    } catch (Throwable $sqle) {
        die("Database setup failed: " . htmlspecialchars($sqle->getMessage()));
    }
}

/**
 * Global Audit Logging Function
 */
function recordAuditLog($pdo, $user_id, $user_email, $user_role, $action, $details) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if ($ip === '::1') $ip = '127.0.0.1';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, user_email, user_role, action, details, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))
        ");
        $stmt->execute([$user_id, $user_email, $user_role, $action, $details, $ip]);
    } catch (Throwable $e) {
        // Fallback for MySQL NOW()
        try {
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, user_email, user_role, action, details, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $user_email, $user_role, $action, $details, $ip]);
        } catch (Throwable $ignore) {}
    }
}
