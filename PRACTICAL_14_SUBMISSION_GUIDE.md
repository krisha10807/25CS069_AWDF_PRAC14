# Practical 14: Role-Based Dashboard, Security Audit Trail & Access Restrictions

**Course:** Web Development Frameworks (WDF)  
**Project Base:** StudentHub Portal  
**Student Name:** Krisha Patel  
**Student ID:** 25CS069  
**Institution:** CHARUSAT - CSPIT (Computer Science & Engineering)  
**Submission Package:** `Practical_14_25CS069.zip`

---

## 1. Practical Overview & Objectives

Practical 14 focuses on implementing an enterprise-grade administrative dashboard integrating **Dynamic Database Queries**, **Role-Based Access Control (RBAC)**, and an automated **Security Audit Trail**.

### Checklist Compliance:
1. **Code Files**:
   - `db.php`: Database connection supporting MySQL and resilient portable SQLite fallback (`studenthub_p14.db`). Contains global `recordAuditLog()` telemetry function.
   - `dashboard.php`: Modern Bootstrap 5 UI displaying dynamic statistics, latest user registrations, role-based conditional navigation menus, and administrative event triggers.
   - `audit_logs.php`: Comprehensive audit log inspector with filter options, strictly protected by an RBAC guard.
   - `login.php` & `authenticate.php`: Secure authentication handler with BCRYPT hashing, session fixation defense (`session_regenerate_id(true)`), and automated login audit tracking.
   - `schema.sql`: Full MySQL export with table schemas (`users`, `events`, `audit_logs`) and pre-seeded records.
2. **Dashboard UI Screenshots**:
   - Dynamic stats: total registered users, enrolled students, scheduled events, and recorded audit logs.
   - Latest registrations table: real-time user records with role badges, department, and timestamps.
3. **RBAC & Security Screenshots**:
   - Dynamic menu according to role (Admin view vs Student view).
   - Non-admin access restricted (403 Forbidden error page) when a student attempts direct URL navigation to administrative resources.
4. **Audit Log Proof**:
   - `audit_logs` table screenshots showing recorded administrative actions, login events, and security violations.

---

## 2. Requirements & Checklist Compliance Matrix

| # | Practical Instruction Requirement | Implementation & Output Evidence | Status |
| :-: | :--- | :--- | :-: |
| 1 | **Code Files** | `db.php`, `dashboard.php`, `audit_logs.php`, `login.php`, `authenticate.php`, and `schema.sql`. | Verified Complete |
| 2 | **Dashboard UI: Dynamic Stats** | Live aggregated SQL `COUNT(*)` queries displaying total users, students, events, and audit actions. | Verified (`01_dashboard_dynamic_stats.png`) |
| 3 | **Dashboard UI: Latest Registrations** | Live SQL query displaying the 5 most recent user registrations with roles and timestamps. | Verified (`02_latest_registrations_table.png`) |
| 4 | **RBAC: Dynamic Menu (Admin)** | Navigation bar displaying Admin Dashboard, Security Audit Logs, User Directory, and Events Management. | Verified (`03_rbac_dynamic_menu_admin.png`) |
| 5 | **RBAC: Dynamic Menu (Student)** | Navigation bar tailored to Student role (Dashboard, Courses, Events, and RBAC guard test link). | Verified (`04_rbac_dynamic_menu_student.png`) |
| 6 | **Security: Non-admin Restriction (403)** | Student attempting to visit `/audit_logs.php` is intercepted with an HTTP 403 Forbidden screen. | Verified (`05_rbac_non_admin_403_restricted.png`) |
| 7 | **Audit Log Proof** | Table showing recorded actions (`SYSTEM_INITIALIZATION`, `EVENT_CREATED`, `ROLE_VERIFIED`, `STUDENT_BATCH_VERIFIED`, `UNAUTHORIZED_ATTEMPT`). | Verified (`06_audit_logs_recorded_admin_actions.png`) |

---

## 3. Database Schema Overview (`schema.sql`)

```sql
-- 1. Users Table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin', 'Student') NOT NULL DEFAULT 'Student',
    `department` VARCHAR(100) DEFAULT 'Computer Science & Engineering',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Events Table
CREATE TABLE `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `event_date` DATETIME NOT NULL,
    `venue` VARCHAR(150) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Audit Logs Table
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `user_email` VARCHAR(100) NOT NULL,
    `user_role` VARCHAR(50) NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL DEFAULT '127.0.0.1',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 4. Test Credentials

| Role | Institutional Email | Plain Password | Hashed Password Format | Accessible Features |
|---|---|---|---|---|
| **Administrator** | `admin@charusat.ac.in` | `Admin@123` | BCRYPT (`$2y$10$...`) | Admin Dashboard, Audit Logs, Quick Action Triggers |
| **Student** | `student@charusat.ac.in` | `Student@123` | BCRYPT (`$2y$10$...`) | Student Dashboard, Enrolled Courses, Campus Events |

---

## 5. Test Evidence & Screenshot Inventory

1. **Dashboard Dynamic Stats (`screenshots/01_dashboard_dynamic_stats.png`):**
   - Renders 4 dynamic metric cards calculating real-time counts from the database: Total Registered Users, Enrolled Students, Campus Events, and Audit Actions Logged.
2. **Latest Registrations Table (`screenshots/02_latest_registrations_table.png`):**
   - Shows the live user database roster sorted in descending order of registration.
3. **Admin Dynamic Menu (`screenshots/03_rbac_dynamic_menu_admin.png`):**
   - Header navigation shows administrative features: Admin Dashboard, Security Audit Logs, User Directory, Events Management.
4. **Student Dynamic Menu (`screenshots/04_rbac_dynamic_menu_student.png`):**
   - Header navigation shows student options: Student Dashboard, Enrolled Courses, Events, and the "Test Access Admin Logs" security guard test link.
5. **Non-admin Access Restricted 403 Screen (`screenshots/05_rbac_non_admin_403_restricted.png`):**
   - Student account attempting direct access to `audit_logs.php` receives an immediate HTTP 403 Forbidden warning notice detailing the privilege violation.
6. **Audit Logs Proof (`screenshots/06_audit_logs_recorded_admin_actions.png`):**
   - Audit trail table showing recorded administrative actions with action badges, user emails, operational details, IP addresses, and timestamps.
7. **Admin Quick Action & Live Audit Trigger (`screenshots/07_admin_quick_action_and_live_audit.png`):**
   - Administrator executing a batch verification action, immediately persisting an audit entry into the log table.

---

## 6. Viva Review Questions & Answers

### Q1: Why is an Audit Trail critical in secure web applications?
**Answer:** An audit trail provides non-repudiation and accountability. In the event of unauthorized data modification, security breaches, or privilege violations, the audit log records who performed the action, what was altered, from which IP address, and at what exact time.

### Q2: How does Role-Based Access Control (RBAC) differ from Authentication?
**Answer:** Authentication confirms **identity** ("Who are you?"), verified via email and password hash. RBAC enforces **authorization** ("What are you allowed to do?"), verified by checking `$_SESSION['role'] === 'Admin'` before granting access to sensitive dashboards and operations.

### Q3: Why should failed access attempts be logged in the audit trail?
**Answer:** Logging 403 Forbidden events (`UNAUTHORIZED_ACCESS_VIOLATION`) helps security administrators identify brute-force enumeration, privilege escalation attacks, or insider threats.

---
*Created for StudentHub Practical 14 Submission & Faculty Review 2026*  
*Student: Krisha Patel (25CS069)*
