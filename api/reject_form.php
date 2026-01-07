<?php
// api/reject_form.php
header('Content-Type: application/json');
session_start();
require_once '../db/conn.php';

$data = json_decode(file_get_contents("php://input"), true);

// 1. ROBUST SECURITY CHECK
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job  = strtolower($_SESSION['job_level'] ?? '');
$full_role_string = $my_role . ' ' . $my_job;

$is_admin = (strpos($full_role_string, 'admin') !== false);
$is_dept_head = (strpos($full_role_string, 'head') !== false || strpos($full_role_string, 'department head') !== false);
$is_executive = (strpos($full_role_string, 'executive') !== false);

$is_admin_level = $is_admin || $is_dept_head || $is_executive ||
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
            $stmt = $conn->prepare("SELECT status FROM dsp_forms WHERE id = ?");
            $stmt->execute([$form_id]);
            $current_status = $stmt->fetchColumn();
            
            // Check if user can reject (same as approve conditions)
            $can_reject = false;
            if (($current_status == 'Pending' && $is_admin) ||
                ($current_status == 'Admin Approved' && $is_dept_head) ||
                ($current_status == 'Dept Head Approved' && $is_executive) ||
                ($current_status == 'Executive Approved' && $is_dept_head)) {
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