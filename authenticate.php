<?php
/**
 * StudentHub Portal - Practical 14: Authentication Processor (authenticate.php)
 * Course: Web Development Frameworks (WDF)
 * Student Name: Krisha Patel
 * Student ID: 25CS069
 */

session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    header("Location: login.php?error=invalid");
    exit();
}

// Prepared Statement Query
$stmt = $pdo->prepare("SELECT id, full_name, email, password, role, department FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Session Fixation Mitigation
    session_regenerate_id(true);

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['full_name']  = $user['full_name'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['department'] = $user['department'];

    // Record Audit Log for successful login
    recordAuditLog(
        $pdo, 
        $user['id'], 
        $user['email'], 
        $user['role'], 
        'LOGIN_SUCCESS', 
        "User authenticated successfully via credentials verification. Active role: {$user['role']}."
    );

    header("Location: dashboard.php");
    exit();
} else {
    // Record Audit Log for failed login attempt
    recordAuditLog(
        $pdo, 
        null, 
        $email, 
        'Guest', 
        'LOGIN_FAILED', 
        "Failed authentication attempt for email '{$email}'. Invalid password or non-existent record."
    );

    header("Location: login.php?error=invalid");
    exit();
}
