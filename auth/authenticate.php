<?php
// Temporarily redirect to dashboard for testing purposes
// header("Location: ../pages/overview.php");
// exit();
 
session_start();
 
// Initialize Variables
$username = $password = "";
$username_error = $password_error = "";
$login_error = "";
 
// Connect to DB (Seperate from conn.php)
$auth_server_name = "10.2.0.9";
$connectionOptions = [
    "UID" => "sa",           // Database username
    "PWD" => "S3rverDB02lrn25",       // Database password
    "Database" => "LRNPH"    // Database name
];
 
 
// Establish the connection to the SQL Server
$auth_conn = sqlsrv_connect($auth_server_name, $connectionOptions);
 
// Check if the connection was successful
if (!$auth_conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}
 
// Handle POST request for login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    if (empty($_POST["username"])) {
        $username_error = "Username is required";
    } else {
        $username = $_POST["username"];
    }
 
    if (empty($_POST["password"])) {
        $password_error = "Password is required";
    } else {
        $password = $_POST["password"];
    }
 
    // If no errors, proceed with the login attempt
    if (empty($username_error) && empty($password_error)) {
        // Prepare the SQL query to fetch the user from the database
        $query = "SELECT
                lu.username,
                lu.password,
                lu.role,
                lu.empcode,
                lu.login_token,
                ml.FirstName + ' ' + ml.LastName as fullname,
                ml.Department
                FROM dbo.lrnph_users lu
                LEFT JOIN LRNPH_E.dbo.lrn_master_list ml
                    on lu.username = ml.BiometricsID
                WHERE lu.username = ?";
        $params = array($username);
 
        // Execute the query
        $stmt = sqlsrv_query($auth_conn, $query, $params);
 
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
 
        // Check if user exists
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Verify the password
            if (password_verify($password, $row['password'])) {
                // Password is correct, start session and store user data
                $_SESSION['user_id'] = !empty($row['empcode']) ? $row['empcode'] : $row['username'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['empcode'] = $row['empcode'];
                $_SESSION['department'] = $row['Department'];

                // FOR DEBUGGING ----
                if ($username === '40970'){
                    $_SESSION['role'] = 'Employee';
                    header("Location: ../pages/dashboard.php");
                    exit();
                }
                elseif ($username === '40021'){
                    $_SESSION['role'] = 'Admin';
                    header("Location: ../pages/view_forms.php");
                    exit();
                }
                elseif ($username === '40235'){
                    $_SESSION['role'] = 'Department Head';
                    header("Location: ../pages/view_forms.php");
                    exit();
                }
                elseif ($username === '2604'){
                    $_SESSION['role'] = 'Executive';
                    header("Location: ../pages/view_forms.php");
                    exit();
                }
                else{
                    // Default role
                    $_SESSION['role'] = 'Invalid';
                }
                // ----------
                header("Location: ../pages/dashboard.php");
                exit();
 
            } else {
                header("Location: ../login.php?error=invalid");
                exit();
            }
        } else {
            header("Location: ../login.php?error=invalid");
            exit();
        }
 
        // Close the statement
        sqlsrv_free_stmt($stmt);
    }
}
 
// Close the connection
sqlsrv_close($auth_conn);
 
 
?>