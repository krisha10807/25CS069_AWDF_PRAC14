<?php
/**
 * StudentHub Portal - Practical 14: Login Interface (login.php)
 * Course: Web Development Frameworks (WDF)
 * Student Name: Krisha Patel
 * Student ID: 25CS069
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error_msg   = '';
$success_msg = '';

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'invalid') {
        $error_msg = "Invalid institutional credentials! Please verify email and password.";
    } elseif ($_GET['error'] === 'unauthorized') {
        $error_msg = "Access Denied (403)! You must log in with appropriate role permissions to access that portal.";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $success_msg = "Logged Out Successfully! Your secure session has been terminated and audit logged.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 14: Authentication Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .login-card {
            max-width: 460px;
            margin: 3.5rem auto;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.06);
            background: #ffffff;
            padding: 2.5rem;
        }
        .brand-badge {
            background-color: #4338ca;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            letter-spacing: 0.05em;
        }
        .demo-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 0.85rem;
        }
        .demo-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 0.6rem;
            border-radius: 6px;
            font-size: 0.78rem;
            text-align: left;
            transition: all 0.15s;
            cursor: pointer;
            width: 100%;
        }
        .demo-btn:hover {
            border-color: #4338ca;
            background-color: #eef2ff;
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
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark" href="login.php">
                <span class="brand-badge">PRACTICAL 14</span>
                StudentHub Portal
            </a>
            <span class="badge bg-light text-secondary border px-3 py-2 fw-semibold">
                Krisha Patel &bull; 25CS069
            </span>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container">
        <div class="login-card">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-dark">Portal Authentication</h3>
                <p class="text-muted small">Role-Based Dashboard & Automated Audit Trail Logging</p>
            </div>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                    <span>&#9888;</span>
                    <div><?= htmlspecialchars($error_msg) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($success_msg): ?>
                <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                    <span>&#10004;</span>
                    <div><?= htmlspecialchars($success_msg) ?></div>
                </div>
            <?php endif; ?>

            <form action="authenticate.php" method="POST" id="loginForm">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary" for="email">Institutional Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="e.g. admin@charusat.ac.in" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm" id="submitLoginBtn" style="background-color: #4338ca; border-color: #4338ca;">
                    Sign In to Portal &rarr;
                </button>
            </form>

            <!-- Quick Evaluation Credentials -->
            <div class="demo-card">
                <div class="fw-bold text-dark mb-2">&#128273; Fast Evaluation Logins:</div>
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="demo-btn" id="btnAdminFill" onclick="fill('admin@charusat.ac.in', 'Admin@123')">
                            <strong class="d-block text-primary">Admin Account</strong>
                            <span class="d-block text-muted">admin@charusat.ac.in</span>
                            <span class="text-secondary">Pass: Admin@123</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="demo-btn" id="btnStudentFill" onclick="fill('student@charusat.ac.in', 'Student@123')">
                            <strong class="d-block text-success">Student Account</strong>
                            <span class="d-block text-muted">student@charusat.ac.in</span>
                            <span class="text-secondary">Pass: Student@123</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        StudentHub Portal &bull; Practical 14: Role-Based Dashboard & Audit Trail &bull; Krisha Patel (25CS069)
    </footer>

    <script>
        function fill(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
