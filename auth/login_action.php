<?php
session_start();
// FIX 1: Go up one level to find the db folder
require_once '../db/conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = $_POST['username'];
    $pass_input = $_POST['password'];

    // Join tables to get JobLevel
    $sql = "SELECT m.id, m.FullName, m.JobLevel, m.Department 
            FROM accounts a
            JOIN lrn_master_list m ON a.EmployeeID = m.id
            WHERE a.Username = ? AND a.Password = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_input, $pass_input]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['full_name'] = $user['FullName'];
        $_SESSION['job_level'] = $user['JobLevel'];
        $_SESSION['dept'] = $user['Department'];

        // FIX 2: Point to the 'pages' folder for redirects
        if ($user['JobLevel'] === 'Supervisor' || $user['JobLevel'] === 'Team Leader') {
            header("Location: ../pages/supervisor_dashboard.php");
        } else {
            header("Location: ../pages/staff_entry.php");
        }
        exit();
    } else {
        header("Location: login.php?error=invalid_credentials");
        exit();
    }
}
?>