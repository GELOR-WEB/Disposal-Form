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

                // Load User Roles Configuration
                $config = include __DIR__ . '/user_roles.php';
                $user_roles = $config['user_map'];
                $department_groups = $config['department_groups'];

                // Check if user exists in the configuration
                if (array_key_exists($username, $user_roles)) {
                    $roles = $user_roles[$username];
                    $_SESSION['roles'] = $roles; // Store full array of roles

                    // Determine Scoping Department (The "Respective Department")
                    // Iterate through groups to find which one contains this user
                    $scoping_dept = '';
                    foreach ($department_groups as $dept_name => $group) {
                        $all_in_group = array_merge($group['employees'] ?? [], $group['heads'] ?? []);
                        // Values in config are strings, ensures type match
                        if (in_array((string) $username, $all_in_group)) {
                            $scoping_dept = $dept_name;
                            break;
                        }
                    }
                    $_SESSION['scoping_dept'] = $scoping_dept;

                    // Determine Primary Role for Hierarchy (Exec > Admin > FacHead > DeptHead > Emp)
                    // This maintains backward compatibility for simple checks, while 'roles' array allows multi-role logic
                    if (in_array('Executive', $roles)) {
                        $_SESSION['role'] = 'Executive';
                    } elseif (in_array('Admin', $roles)) { // Admin isn't explicitly in user_roles keys but checking just in case
                        $_SESSION['role'] = 'Admin';
                    } elseif (in_array('Facilities Head', $roles)) {
                        $_SESSION['role'] = 'Facilities Head';
                    } elseif (in_array('Department Head', $roles)) {
                        $_SESSION['role'] = 'Department Head';
                    } else {
                        $_SESSION['role'] = 'Employee';
                    }

                    // Redirect based on roles
                    // If they have ANY role above Employee, go to supervisor_dashboard (approver view)
                    // Only pure Employees go to dashboard
                    if ($_SESSION['role'] === 'Employee') {
                        header("Location: ../pages/dashboard.php");
                    } else {
                        // All other roles (Department Head, Facilities Head, Executive) go to supervisor_dashboard for approvals
                        header("Location: ../pages/supervisor_dashboard.php");
                    }
                    exit();
                } else {
                    // User not in allowlist
                    header("Location: ../login.php?error=unauthorized");
                    exit();
                }

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