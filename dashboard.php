<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

// Redirect based on role
if ($role == 'admin') {
    header("Location: admin/dashboard_admin.php");
} else {
    header("Location: calon_karyawan/dashboard_karyawan.php");
}
exit();
?>