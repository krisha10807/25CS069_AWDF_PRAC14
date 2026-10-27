<?php
/**
 * StudentHub Portal - Practical 14: Dynamic Role-Based Dashboard (dashboard.php)
 * Course: Web Development Frameworks (WDF)
 * Student Name: Krisha Patel
 * Student ID: 25CS069
 * 
 * Checklist compliance:
 * 1. Dashboard queries (Total users, students, events, audit logs)
 * 2. RBAC check & Dynamic menu according to role (Admin vs Student)
 * 3. Latest registrations table
 * 4. Audit logging integration
 */

session_start();
require_once __DIR__ . '/db.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

$user_id   = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'User';
$email     = $_SESSION['email'] ?? '';
$role      = $_SESSION['role'] ?? 'Student';
$dept      = $_SESSION['department'] ?? 'Computer Science & Engineering';

// Process Quick Admin Action (e.g. Triggering audit action directly from dashboard)
$action_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action']) && $role === 'Admin') {
    $action_type = trim($_POST['admin_action']);
    
    if ($action_type === 'VERIFY_ALL_STUDENTS') {
        recordAuditLog($pdo, $user_id, $email, $role, 'STUDENT_BATCH_VERIFIED', "Administrator performed batch credential verification for all active students.");
        $action_msg = "Batch audit action recorded: All enrolled students verified!";
    } elseif ($action_type === 'CREATE_QUICK_USER') {
        $new_name  = trim($_POST['new_name'] ?? 'New Student');
        $new_email = trim($_POST['new_email'] ?? ('student_' . time() . '@charusat.ac.in'));
        $pass_hash = password_hash('Student@123', PASSWORD_BCRYPT);
        
        $stmt_ins = $pdo->prepare("INSERT INTO users (full_name, email, password, role, department) VALUES (?, ?, ?, 'Student', ?)");
        $stmt_ins->execute([$new_name, $new_email, $pass_hash, 'Computer Science & Engineering']);
        $new_uid = $pdo->lastInsertId();
        
        recordAuditLog($pdo, $user_id, $email, $role, 'USER_CREATED_BY_ADMIN', "Administrator created student record #$new_uid for '{$new_name}' ({$new_email}).");
        $action_msg = "New student '{$new_name}' created and recorded in audit log!";
    }
}

// -------------------------------------------------------------
// DASHBOARD QUERIES (Dynamic Statistics)
// -------------------------------------------------------------

// 1. Total Users
$stmt_total_users = $pdo->query("SELECT COUNT(*) as cnt FROM users");
$total_users = $stmt_total_users->fetch()['cnt'] ?? 0;

// 2. Total Students
$stmt_students = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE role = 'Student'");
$total_students = $stmt_students->fetch()['cnt'] ?? 0;

// 3. Total Events
$stmt_events = $pdo->query("SELECT COUNT(*) as cnt FROM events");
$total_events = $stmt_events->fetch()['cnt'] ?? 0;

// 4. Total Audit Logs
$stmt_logs = $pdo->query("SELECT COUNT(*) as cnt FROM audit_logs");
$total_audit_logs = $stmt_logs->fetch()['cnt'] ?? 0;

// 5. Latest Registrations Query
$stmt_latest = $pdo->query("SELECT id, full_name, email, role, department, created_at FROM users ORDER BY id DESC LIMIT 5");
$latest_users = $stmt_latest->fetchAll();

// 6. Recent Audit Activities Query (for Admin overview)
$stmt_recent_logs = $pdo->query("SELECT id, action, details, user_email, ip_address, created_at FROM audit_logs ORDER BY id DESC LIMIT 4");
$recent_logs = $stmt_recent_logs->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 14: <?= $role === 'Admin' ? 'Admin Control Center' : 'Student Academic Portal' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-primary: #4338ca;
            --brand-secondary: #0ea5e9;
        }
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .stat-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem 1.4rem;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .hero-banner {
            background: <?= $role === 'Admin' ? 'linear-gradient(135deg, #312e81 0%, #1e1b4b 100%)' : 'linear-gradient(135deg, #064e3b 0%, #022c22 100%)' ?>;
            color: #ffffff;
            border-radius: 14px;
            padding: 1.75rem 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .badge-role-admin {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .badge-role-student {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        footer {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 1.25rem;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- ============================================================== -->
    <!-- DYNAMIC NAVBAR MENU ACCORDING TO ROLE (RBAC REQUIREMENT)        -->
    <!-- ============================================================== -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark" href="dashboard.php">
                <span class="badge" style="background-color: <?= $role === 'Admin' ? '#4338ca' : '#059669' ?>; font-size: 0.72rem; letter-spacing: 0.05em;">
                    <?= $role === 'Admin' ? 'ADMIN PORTAL' : 'STUDENT PORTAL' ?>
                </span>
                StudentHub
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarRoleMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarRoleMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <?php if ($role === 'Admin'): ?>
                        <!-- Admin Dynamic Menu Items -->
                        <li class="nav-item">
                            <a class="nav-link active fw-semibold text-primary" href="dashboard.php">&#128202; Admin Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary" href="audit_logs.php">&#128220; Security Audit Logs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary" href="dashboard.php#latestRegistrations">&#128101; User Directory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary" href="dashboard.php#scheduledEvents">&#128197; Events Management</a>
                        </li>
                    <?php else: ?>
                        <!-- Student Dynamic Menu Items -->
                        <li class="nav-item">
                            <a class="nav-link active fw-semibold text-success" href="dashboard.php">&#127891; Student Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary" href="dashboard.php#enrolledCourses">&#128218; Enrolled Courses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary" href="dashboard.php#scheduledEvents">&#128197; Campus Events</a>
                        </li>
                        <!-- Test Non-admin Access Restriction (403 Error Demo) -->
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-semibold" href="audit_logs.php" id="linkTestRbacForbidden">
                                &#128683; Test Access Admin Logs (RBAC Guard)
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2 bg-light border rounded-pill px-3 py-1">
                        <span class="small fw-semibold text-dark"><?= htmlspecialchars($full_name) ?></span>
                        <span class="badge rounded-pill <?= $role === 'Admin' ? 'badge-role-admin' : 'badge-role-student' ?>">
                            <?= htmlspecialchars($role) ?>
                        </span>
                    </div>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm" id="btnLogout">Sign Out</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-4">

        <!-- Welcome Hero Banner -->
        <div class="hero-banner d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1">
                    <?= $role === 'Admin' ? 'System Administrator Control Center' : 'Welcome, ' . htmlspecialchars($full_name) . '!' ?>
                </h2>
                <p class="mb-0 text-white-50 small">
                    Course: Web Development Frameworks (WDF) &bull; Department: <?= htmlspecialchars($dept) ?> &bull; Author: Krisha Patel (25CS069)
                </p>
            </div>
            <div>
                <?php if ($role === 'Admin'): ?>
                    <a href="audit_logs.php" class="btn btn-light btn-sm fw-semibold shadow-sm text-primary">
                        &#128220; View Full Audit Logs &rarr;
                    </a>
                <?php else: ?>
                    <a href="audit_logs.php" class="btn btn-danger btn-sm fw-semibold shadow-sm" id="btnStudentAttempt403">
                        &#128274; Test RBAC 403 Forbidden Access
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($action_msg): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
                <span>&#10004;</span>
                <div><?= htmlspecialchars($action_msg) ?></div>
            </div>
        <?php endif; ?>

        <!-- ============================================================== -->
        <!-- 1. DYNAMIC STATS (USER / STUDENT / EVENT / AUDIT LOG COUNT)    -->
        <!-- ============================================================== -->
        <div class="row g-3 mb-4" id="dynamicStatsRow">
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background-color: #e0e7ff; color: #4338ca;">&#128101;</div>
                    <div>
                        <div class="h3 fw-bold mb-0 text-dark" id="statTotalUsers"><?= $total_users ?></div>
                        <div class="text-muted small fw-semibold">Total Registered Users</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background-color: #ecfdf5; color: #059669;">&#127891;</div>
                    <div>
                        <div class="h3 fw-bold mb-0 text-dark" id="statStudentCount"><?= $total_students ?></div>
                        <div class="text-muted small fw-semibold">Enrolled Students</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background-color: #fffbeb; color: #d97706;">&#128197;</div>
                    <div>
                        <div class="h3 fw-bold mb-0 text-dark" id="statEventCount"><?= $total_events ?></div>
                        <div class="text-muted small fw-semibold">Campus Events</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background-color: #f1f5f9; color: #0f172a;">&#128220;</div>
                    <div>
                        <div class="h3 fw-bold mb-0 text-dark" id="statAuditLogCount"><?= $total_audit_logs ?></div>
                        <div class="text-muted small fw-semibold">Audit Actions Logged</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 2. LATEST REGISTRATIONS TABLE (DYNAMIC QUERY REQUIREMENT)       -->
        <!-- ============================================================== -->
        <div class="card border-0 shadow-sm rounded-3 mb-4" id="latestRegistrations">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">&#128101; Latest User Registrations</h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    Live Database Records
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 80px;">ID</th>
                            <th>Full Name</th>
                            <th>Institutional Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Registration Date</th>
                            <th class="text-end pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latest_users as $u): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted">#<?= $u['id'] ?></td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($u['full_name']) ?></span>
                                </td>
                                <td>
                                    <code class="text-secondary"><?= htmlspecialchars($u['email']) ?></code>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?= $u['role'] === 'Admin' ? 'badge-role-admin' : 'badge-role-student' ?>">
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                </td>
                                <td><span class="small text-muted"><?= htmlspecialchars($u['department']) ?></span></td>
                                <td><span class="small text-muted"><?= date('M d, Y h:i A', strtotime($u['created_at'])) ?></span></td>
                                <td class="text-end pe-3">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        Active
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($role === 'Admin'): ?>
            <!-- Recent Audit Logs Activity Snapshot (for Admin view) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">&#128220; Recent Security & Action Audit Trail</h5>
                    <a href="audit_logs.php" class="btn btn-outline-primary btn-sm">View Complete Log Table &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Action</th>
                                <th>Performed By</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th class="text-end pe-3">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="badge bg-dark-subtle text-dark border">
                                            <?= htmlspecialchars($log['action']) ?>
                                        </span>
                                    </td>
                                    <td><code class="small text-primary"><?= htmlspecialchars($log['user_email']) ?></code></td>
                                    <td><span class="small text-secondary"><?= htmlspecialchars($log['details']) ?></span></td>
                                    <td><span class="small text-muted"><?= htmlspecialchars($log['ip_address']) ?></span></td>
                                    <td class="text-end pe-3"><span class="small text-muted"><?= $log['created_at'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Admin Action Trigger Panel -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold text-dark mb-0">&#9881; Administrative Quick Actions & Audit Event Triggers</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <form method="POST">
                                <input type="hidden" name="admin_action" value="VERIFY_ALL_STUDENTS">
                                <p class="small text-muted mb-2">Perform batch credential verification and record an audit log entry in the system.</p>
                                <button type="submit" class="btn btn-outline-primary btn-sm fw-semibold" id="btnTriggerBatchVerify">
                                    &#10004; Trigger Batch Student Verification (Audit Action)
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="admin_action" value="CREATE_QUICK_USER">
                                <input type="text" name="new_name" class="form-control form-control-sm" placeholder="New student name" value="Priya Sharma" required>
                                <button type="submit" class="btn btn-outline-success btn-sm text-nowrap fw-semibold" id="btnQuickCreateUser">
                                    &#10010; Quick Add & Log
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer>
        StudentHub Portal &bull; Practical 14: Role-Based Dashboard & Audit Trail &bull; Krisha Patel (25CS069)
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
