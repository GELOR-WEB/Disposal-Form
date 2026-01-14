<?php
// api/reject_form.php
header('Content-Type: application/json');
session_start();
require_once '../db/conn.php';

$data = json_decode(file_get_contents("php://input"), true);

// 1. ROBUST SECURITY CHECK
// 1. ROBUST SECURITY CHECK
$my_primary_role = strtolower($_SESSION['role'] ?? '');
$my_roles = $_SESSION['roles'] ?? [$my_primary_role]; // Array of roles
$my_job = strtolower($_SESSION['job_level'] ?? '');
$my_dept = $_SESSION['department'] ?? '';

// Create a combined string of all roles for easier searching (or iterate array)
// Convert all roles to lowercase
$my_roles_lower = array_map('strtolower', $my_roles);
$full_role_string = implode(' ', $my_roles_lower) . ' ' . $my_job;

$is_fac_head = (strpos($full_role_string, 'admin') !== false || strpos($full_role_string, 'facilities head') !== false);
$is_dept_head = (strpos($full_role_string, 'department head') !== false);
$is_executive = (strpos($full_role_string, 'executive') !== false);

$is_admin_level = $is_fac_head || $is_dept_head || $is_executive ||
    strpos($full_role_string, 'supervisor') !== false ||
    strpos($full_role_string, 'manager') !== false ||
    strpos($full_role_string, 'leader') !== false;

// Determine the Rejector ID (Use user_id if set, otherwise username)
$rejector_id = $_SESSION['user_id'] ?? $_SESSION['username'] ?? null;

if (!$rejector_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or Missing ID. Please Logout and Login again.']);
    exit();
}

// 2. Execute Update
if (isset($data['form_ids']) && is_array($data['form_ids'])) {

    // NEW: Capture the optional reason sent from the JS prompt
    $reason = $data['reason'] ?? null;

    try {
        $conn->beginTransaction();

        foreach ($data['form_ids'] as $form_id) {
            // Get current status
            $stmt = $conn->prepare("SELECT status, department FROM dsp_forms WHERE id = ?");
            $stmt->execute([$form_id]);
            $form_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_status = $form_data['status'];
            $form_dept = $form_data['department'];

            // Check if user can reject (same as approve conditions)
            $can_reject = false;
            if (
                ($current_status == 'Pending' && $is_dept_head && $my_dept === $form_dept) ||
                ($current_status == 'Dept Head Approved' && $is_fac_head) ||
                (($current_status == 'Fac Head Approved' || $current_status == 'Admin Approved') && $is_executive) ||
                ($current_status == 'Executive Approved' && $is_fac_head)
            ) {
                $can_reject = true;
            }

            if ($can_reject) {
                // MODIFIED: Added rejection_reason to the SQL query
                $update_sql = "UPDATE dsp_forms SET status = 'Rejected', rejection_reason = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);

                // MODIFIED: Passed the $reason variable into the execution array
                $stmt->execute([$reason, $form_id]);
            } else {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Unauthorized rejection for form ' . $form_id]);
                exit();
            }
        }

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No IDs provided']);
}
?>