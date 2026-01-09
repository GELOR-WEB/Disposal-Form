<?php
// api/approve_forms.php
header('Content-Type: application/json');
session_start();
require_once '../db/conn.php';

$data = json_decode(file_get_contents("php://input"), true);

// 1. GET USER ROLE & DETAILS
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job  = strtolower($_SESSION['job_level'] ?? '');
$my_dept = $_SESSION['department'] ?? ''; 
$approver_id = $_SESSION['user_id'] ?? $_SESSION['username'] ?? 'Unknown';

$full_role_string = $my_role . ' ' . $my_job;

// Define specific booleans
// NOTE: "Admin" roles are now treated as "Facilities Head" for the workflow
$is_fac_head  = (strpos($full_role_string, 'admin') !== false); 
$is_dept_head = (strpos($full_role_string, 'head') !== false || strpos($full_role_string, 'department head') !== false);
$is_executive = (strpos($full_role_string, 'executive') !== false);

if (!$data['form_ids']) {
    echo json_encode(['success' => false, 'message' => 'No forms selected.']);
    exit();
}

try {
    $conn->beginTransaction();

    foreach ($data['form_ids'] as $form_id) {
        // 2. FETCH CURRENT STATUS
        $stmt = $conn->prepare("SELECT status, department FROM dsp_forms WHERE id = ?");
        $stmt->execute([$form_id]);
        $form = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$form) continue;

        $current_status = $form['status'];
        
        $new_status = "";
        $column_to_update = "";
        $date_column = "";

        // --- UPDATED HIERARCHY LOGIC ---

        // STEP 1: Dept Head approves 'Pending' (Was Admin)
        // Now writes to: dept_head_approved_by
        if ($current_status == 'Pending' && $is_dept_head) {
            $new_status = 'Dept Head Approved';
            $column_to_update = 'dept_head_approved_by';
            $date_column = 'dept_head_approved_date';
        } 
        
        // STEP 2: Fac Head (Admin) approves 'Dept Head Approved' (Was Dept Head)
        // Now writes to: fac_head_approved_by
        elseif ($current_status == 'Dept Head Approved' && $is_fac_head) {
            $new_status = 'Fac Head Approved';
            $column_to_update = 'fac_head_approved_by';
            $date_column = 'fac_head_approved_date';
        }

        // STEP 3: Executive approves 'Fac Head Approved' (Was Dept Head Approved)
        // Now writes to: executive_approved_by
        elseif ($current_status == 'Fac Head Approved' && $is_executive) {
            $new_status = 'Executive Approved';
            $column_to_update = 'executive_approved_by';
            $date_column = 'executive_approved_date';
        }

        // STEP 4: Fac Head (Admin) FINAL approval (Was Dept Head Final)
        // Now writes to: final_fac_head_approved_by
        elseif ($current_status == 'Executive Approved' && $is_fac_head) {
            $new_status = 'Approved'; // Completed
            $column_to_update = 'final_fac_head_approved_by';
            $date_column = 'final_fac_head_approved_date';
        } 
        
        // IF NO MATCH
        else {
            throw new Exception("You are not authorized to approve this form at its current stage ($current_status).");
        }

        // 3. EXECUTE UPDATE
        $sql = "UPDATE dsp_forms SET status = ?, $column_to_update = ?, $date_column = GETDATE() WHERE id = ?";
        $updateStmt = $conn->prepare($sql);
        $updateStmt->execute([$new_status, $approver_id, $form_id]);
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>