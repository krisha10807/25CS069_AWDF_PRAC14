<?php
/**
 * Practical 14 - Logout Handler with Audit Logging
 */
session_start();
require_once __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) {
    recordAuditLog(
        $pdo,
        $_SESSION['user_id'],
        $_SESSION['email'] ?? 'User',
        $_SESSION['role'] ?? 'User',
        'LOGOUT_SUCCESS',
        "User {$_SESSION['full_name']} logged out cleanly. Session destroyed."
    );
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: login.php?msg=logged_out");
exit();
