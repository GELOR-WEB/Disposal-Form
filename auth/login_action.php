<?php
// auth/login_action.php
session_start();
require_once '../db/conn.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Use the Safe Tables (dsp_) and the Working User (kcruz)
        $sql = "SELECT a.AccountID, a.Password, e.id as UserID, e.FullName, e.JobLevel, e.Department 
                FROM dsp_accounts a
                JOIN dsp_employees e ON a.EmployeeID = e.id
                WHERE a.Username = :username";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify Password (Plain text for OJT)
        if ($user && $password === $user['Password']) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['full_name'] = $user['FullName'];
            $_SESSION['job_level'] = $user['JobLevel'];
            $_SESSION['dept'] = $user['Department'];

            // Redirect based on role
            if ($user['JobLevel'] == 'Supervisor' || $user['JobLevel'] == 'Team Leader') {
                header("Location: ../pages/supervisor_dashboard.php");
            } else {
                header("Location: ../pages/view_forms.php");
            }
            exit();
        } else {
            // Send back to design page with error
            header("Location: login.php?error=invalid_credentials");
            exit();
        }

    } catch (PDOException $e) {
        die("System Error: " . $e->getMessage());
    }
} else {
    // If someone tries to open this file directly without submitting, send them back
    header("Location: login.php");
    exit();
}
?>