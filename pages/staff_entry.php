<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../db/conn.php';

// Reuse the main dashboard layout for staff entry
include 'dashboard.php'; 
?>