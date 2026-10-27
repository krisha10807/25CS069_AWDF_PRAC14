<?php
/**
 * StudentHub Portal - Practical 14: Security Audit Trail & Logs (audit_logs.php)
 * Course: Web Development Frameworks (WDF)
 * Student Name: Krisha Patel
 * Student ID: 25CS069
 * 
 * Checklist compliance:
 * 1. Non-admin access restricted (403 error page screenshot)
 * 2. Audit logs table showing recorded admin actions
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

// ==============================================================
// RBAC CHECK: NON-ADMIN ACCESS RESTRICTION (403 ERROR SCREEN)
// ==============================================================
if ($role !== 'Admin') {
    // Record the unauthorized security access violation in the audit trail!
    recordAuditLog(
        $pdo,
        $user_id,
        $email,
        $role,
        'UNAUTHORIZED_ACCESS_VIOLATION',
        "Student {$full_name} ({$email}) attempted direct URL navigation to restricted administrative resource /audit_logs.php (Blocked by RBAC guard)."
    );

    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>403 Forbidden - Security Access Restriction</title>
        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8fafc;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }
            .forbidden-card {
                max-width: 650px;
                margin: 4rem auto;
                background: #ffffff;
                border: 1px solid #fecaca;
                border-radius: 16px;
                padding: 3rem 2.5rem;
                text-align: center;
                box-shadow: 0 10px 25px -5px rgba(220, 38, 38, 0.1);
            }
            .forbidden-icon {
                font-size: 4rem;
                margin-bottom: 1rem;
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
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark" href="dashboard.php">
                    <span class="badge bg-danger text-white">ACCESS GUARD</span>
                    StudentHub Security
                </a>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                        <?= htmlspecialchars($full_name) ?> (<?= htmlspecialchars($role) ?>)
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Sign Out</a>
                </div>
            </div>
        </nav>

        <main class="container">
            <div class="forbidden-card" id="forbiddenCard">
                <div class="forbidden-icon">&#128683;</div>
                <h2 class="fw-bold text-danger mb-2">403 - Forbidden: Access Restricted</h2>
                <h5 class="text-secondary fw-semibold mb-3">Role-Based Access Control (RBAC) Enforcement</h5>

                <div class="alert alert-danger text-start mb-4" role="alert">
                    <div class="fw-bold mb-1">&#9888; Security Incident Logged:</div>
                    <div>
                        User <strong><?= htmlspecialchars($full_name) ?></strong> with role <span class="badge bg-success">Student</span> is not authorized to inspect system-level security audit logs.
                        The requested resource <code>/audit_logs.php</code> is restricted exclusively to users holding the <strong>Administrator</strong> role.
                    </div>
                </div>

                <p class="text-muted small mb-4">
                    Your unauthorized access attempt has been permanently logged in the audit trail database with your account identity and IP address.
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <a href="dashboard.php" class="btn btn-primary px-4 fw-semibold" style="background-color: #4338ca; border-color: #4338ca;">
                        &larr; Return to Student Dashboard
                    </a>
                    <a href="logout.php" class="btn btn-outline-danger px-4 fw-semibold">
                        Sign Out
                    </a>
                </div>
            </div>
        </main>

        <footer>
            StudentHub Portal &bull; Practical 14: Role-Based Dashboard & Audit Trail &bull; Krisha Patel (25CS069)
        </footer>
    </body>
    </html>
    <?php
    exit();
}

// ==============================================================
// ADMIN AUTHORIZED: FETCH & DISPLAY AUDIT LOGS TABLE
// ==============================================================

// Filter by Action if provided
$filter_action = trim($_GET['action_filter'] ?? '');
$query = "SELECT * FROM audit_logs WHERE 1=1";
$params = [];

if ($filter_action !== '') {
    $query .= " AND action = ?";
    $params[] = $filter_action;
}

$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Unique actions for filter dropdown
$distinct_actions = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 14: Audit Trail Records</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .action-badge {
            font-family: ui-monospace, SFMono-Regular, monospace;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark" href="dashboard.php">
                <span class="badge bg-primary" style="font-size: 0.72rem; letter-spacing: 0.05em;">ADMIN AUDIT</span>
                StudentHub Security
            </a>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <a href="dashboard.php" class="btn btn-outline-primary btn-sm">&larr; Return to Dashboard</a>
                <div class="bg-light border rounded-pill px-3 py-1 small fw-semibold text-dark">
                    <?= htmlspecialchars($full_name) ?> <span class="badge bg-danger-subtle text-danger border ms-1">Admin</span>
                </div>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Sign Out</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">&#128220; System Security & Administrative Audit Logs</h3>
                <p class="text-muted small mb-0">Practical 14: Recorded administrative activities, authentication events, and RBAC violations.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <form method="GET" class="d-flex gap-2">
                    <select name="action_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Action Types (<?= count($logs) ?>)</option>
                        <?php foreach ($distinct_actions as $act): ?>
                            <option value="<?= htmlspecialchars($act) ?>" <?= $filter_action === $act ? 'selected' : '' ?>>
                                <?= htmlspecialchars($act) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($filter_action): ?>
                        <a href="audit_logs.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 4. AUDIT LOG PROOF: AUDIT_LOGS TABLE WITH ADMIN ACTIONS         -->
        <!-- ============================================================== -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden" id="auditLogsCard">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">&#128274; Audit Trail Log Roster</h5>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                    <?= count($logs) ?> Events Logged
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="auditTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 70px;">Log ID</th>
                            <th style="width: 220px;">Recorded Action</th>
                            <th>User Email</th>
                            <th>Role</th>
                            <th>Action Details / Operational Summary</th>
                            <th style="width: 120px;">IP Address</th>
                            <th class="text-end pe-3" style="width: 180px;">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <?php 
                                $is_unauthorized = ($log['action'] === 'UNAUTHORIZED_ATTEMPT' || $log['action'] === 'UNAUTHORIZED_ACCESS_VIOLATION' || $log['action'] === 'LOGIN_FAILED');
                                $is_admin_action = (strpos($log['action'], 'VERIFIED') !== false || strpos($log['action'], 'CREATED') !== false || strpos($log['action'], 'UPDATE') !== false || $log['action'] === 'SYSTEM_INITIALIZATION');
                            ?>
                            <tr class="<?= $is_unauthorized ? 'table-danger-subtle' : '' ?>">
                                <td class="ps-3 fw-bold text-muted">#<?= $log['id'] ?></td>
                                <td>
                                    <?php if ($is_unauthorized): ?>
                                        <span class="action-badge bg-danger-subtle text-danger border border-danger-subtle">
                                            &#9888; <?= htmlspecialchars($log['action']) ?>
                                        </span>
                                    <?php elseif ($is_admin_action): ?>
                                        <span class="action-badge bg-primary-subtle text-primary border border-primary-subtle">
                                            &#10004; <?= htmlspecialchars($log['action']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="action-badge bg-secondary-subtle text-secondary border">
                                            <?= htmlspecialchars($log['action']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code class="text-dark small fw-semibold"><?= htmlspecialchars($log['user_email']) ?></code>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?= $log['user_role'] === 'Admin' ? 'bg-danger text-white' : ($log['user_role'] === 'Student' ? 'bg-success text-white' : 'bg-secondary') ?>">
                                        <?= htmlspecialchars($log['user_role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-secondary"><?= htmlspecialchars($log['details']) ?></span>
                                </td>
                                <td>
                                    <code class="small text-muted"><?= htmlspecialchars($log['ip_address']) ?></code>
                                </td>
                                <td class="text-end pe-3">
                                    <span class="small text-muted"><?= $log['created_at'] ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer>
        StudentHub Portal &bull; Practical 14: Role-Based Dashboard & Audit Trail &bull; Krisha Patel (25CS069)
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
