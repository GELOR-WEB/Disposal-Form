<?php
session_start();
echo "<h1>Session Debug</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

$roles = include 'auth/user_roles.php';
echo "<h2>Config Check for " . $_SESSION['username'] . "</h2>";
$found_dept = 'None';
foreach ($roles['department_groups'] as $dept => $group) {
    if (
        in_array((string) $_SESSION['username'], $group['employees'] ?? []) ||
        in_array((string) $_SESSION['username'], $group['heads'] ?? [])
    ) {
        $found_dept = $dept;
        break;
    }
}
echo "Calculated Scoping Dept: " . $found_dept;
?>