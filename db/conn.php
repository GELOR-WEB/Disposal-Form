<?php
$local_development = true; // Set to true for XAMPP

if ($local_development) {
    // --- LOCAL CONNECTION (PDO) ---
    $serverName = "localhost\SQLEXPRESS";
    $db_name = "GMP";
    $username = ""; // Empty for Windows Auth
    $password = ""; 
} else {
    // --- PRODUCTION CONNECTION (PDO) ---
    $serverName = "10.2.0.9";
    $db_name = "LRNPH_OJT";
    $username = "adejesus";
    $password = "Admin?!@#";
}

try {
    // Connect using PDO
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$db_name;TrustServerCertificate=true", $username, $password);
    
    // Set error mode to exception (Crucial for debugging)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>