<?php
// db/conn.php
$local_development = false; // Set to true if you go back to XAMPP at home

if ($local_development) {
    // --- LOCAL SETTINGS ---
    $serverName = "localhost\SQLEXPRESS";
    $db_name = "GMP";
    $username = null;
    $password = null;
} else {
    // --- PRODUCTION SETTINGS (Company Server) ---
    $serverName = "10.2.0.9";
    $db_name = "LRNPH_OJT";
    
    // WE USE THE CREDENTIALS FROM YOUR COWORKER'S SCRIPT
    $username = "kcruz";        // The user that actually exists!
    $password = "Admin?!@#";    // The correct password
}

try {
    // Connect using PDO (The modern way our app needs)
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$db_name;TrustServerCertificate=true", $username, $password);
    
    // Set options to crash nicely on errors
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>