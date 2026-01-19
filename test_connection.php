<?php
// test_connection.php
$server = "10.2.0.9";
$db = "LRNPH_OJT";

echo "<h3>Testing Connection to $server...</h3>";

// TEST 1: Windows Authentication
echo "<p><strong>Attempt 1: Windows Authentication...</strong><br>";
try {
    $conn = new PDO("sqlsrv:Server=$server;Database=$db;TrustServerCertificate=true", null, null);
    echo "<span style='color:green'>SUCCESS! Windows Auth works. Use this in conn.php.</span></p>";
} catch (PDOException $e) {
    echo "<span style='color:red'>FAILED: " . $e->getMessage() . "</span></p>";
}

// TEST 2: SQL Authentication (User: adejesus)
echo "<p><strong>Attempt 2: SQL Login (adejesus)...</strong><br>";
try {
    $user = "adejesus";
    $pass = "Admin?!@#"; // Double check this password!
    $conn = new PDO("sqlsrv:Server=$server;Database=$db;TrustServerCertificate=true", $user, $pass);
    echo "<span style='color:green'>SUCCESS! SQL Login works. Use this in conn.php.</span></p>";
} catch (PDOException $e) {
    echo "<span style='color:red'>FAILED: " . $e->getMessage() . "</span></p>";
}
?>