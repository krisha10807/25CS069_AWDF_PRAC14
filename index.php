<?php
/**
 * Practical 14 - Index Router
 */
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

header("Location: login.php");
exit();
