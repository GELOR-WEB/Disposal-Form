<?php
// pages/dashboard.php
session_start();

// 1. If not logged in, kick to login page
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] === 'Invalid') {
    // If role is invalid, log out the user
    header("Location: ../auth/logout.php");
    exit();
}

// 2. Get User Role info
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job  = strtolower($_SESSION['job_level'] ?? '');
$full_role_string = $my_role . ' ' . $my_job;

// 3. Check if Admin/Supervisor
$is_admin_level = (
    strpos($full_role_string, 'admin') !== false || 
    strpos($full_role_string, 'supervisor') !== false || 
    strpos($full_role_string, 'manager') !== false || 
    strpos($full_role_string, 'leader') !== false
);

// 4. Redirect
if ($is_admin_level) {
    // If Admin -> Go to View Forms
    header("Location: view_forms.php");
} else {
    // If Employee -> Go to Create Form
    header("Location: staff_entry.php");
}
exit();
?>