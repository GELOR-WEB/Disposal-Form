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
    display_unavailable();
}

function display_unavailable() {
    die('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Service Unavailable</title>
        <style>
            body {
                background-color: #fce5f0; /* Matches your app theme */
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .error-card {
                background: white;
                padding: 40px;
                border-radius: 24px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                text-align: center;
                max-width: 400px;
                border: 1px solid rgba(255, 255, 255, 0.8);
            }
            .icon {
                font-size: 60px;
                margin-bottom: 20px;
            }
            h2 {
                color: #333;
                margin: 0 0 10px 0;
            }
            p {
                color: #666;
                line-height: 1.6;
                margin-bottom: 25px;
            }
            .btn {
                background: #ff3385;
                color: white;
                text-decoration: none;
                padding: 12px 25px;
                border-radius: 12px;
                font-weight: 600;
                display: inline-block;
                transition: background 0.2s;
            }
            .btn:hover {
                background: #e01b6b;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="icon">🔌</div>
            <h2>System Unavailable</h2>
            <p>We are currently unable to connect to the database. Please check your internet connection or try again later.</p>
            <a href="javascript:location.reload()" class="btn">Try Again</a>
        </div>
    </body>
    </html>
    ');
}
?>
