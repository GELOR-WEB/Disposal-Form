<?php
// api/approve_forms.php
header('Content-Type: application/json');
session_start();
require_once '../db/conn.php';

$data = json_decode(file_get_contents("php://input"), true);

// 1. GET USER ROLE & DETAILS
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job  = strtolower($_SESSION['job_level'] ?? '');
$my_dept = $_SESSION['department'] ?? ''; // Important for Dept Head check
$approver_id = $_SESSION['user_id'] ?? $_SESSION['username'] ?? 'Unknown';

$full_role_string = $my_role . ' ' . $my_job;

// Define specific booleans for clarity
$is_admin     = (strpos($full_role_string, 'admin') !== false);
$is_dept_head = (strpos($full_role_string, 'head') !== false);
$is_executive = (strpos($full_role_string, 'executive') !== false);

if (!$data['form_ids']) {
    echo json_encode(['success' => false, 'message' => 'No forms selected.']);
    exit();
}

try {
    $conn->beginTransaction();

    foreach ($data['form_ids'] as $form_id) {
        // 2. FETCH CURRENT STATUS & DEPT OF THE FORM
        // We need the form's department to ensure Dept Head only approves THEIR OWN department
        $stmt = $conn->prepare("SELECT status, department FROM dsp_forms WHERE id = ?");
        $stmt->execute([$form_id]);
        $form = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$form) continue;

        $current_status = $form['status'];
        $form_dept      = $form['department'];

        $new_status = "";
        $column_to_update = "";
        $date_column = "";

        // --- THE HIERARCHY LOGIC ---

        // STEP 1: Admin approves 'Pending'
        if ($current_status == 'Pending' && $is_admin) {
            $new_status = 'Admin Approved';
            $column_to_update = 'admin_approved_by';
            $date_column = 'admin_approved_date';
        } 
        
        // STEP 2: Dept Head approves 'Admin Approved'
        elseif ($current_status == 'Admin Approved' && $is_dept_head) {
            // Optional: Strict check ensuring Dept Head matches Form Dept
            // if ($my_dept !== $form_dept) throw new Exception("You can only approve for your department.");
            
            $new_status = 'Dept Head Approved';
            $column_to_update = 'dept_head_approved_by';
            $date_column = 'dept_head_approved_date';
        }

        // STEP 3: Executive approves 'Dept Head Approved'
        elseif ($current_status == 'Dept Head Approved' && $is_executive) {
            $new_status = 'Executive Approved';
            $column_to_update = 'executive_approved_by';
            $date_column = 'executive_approved_date';
        }

        // STEP 4: Dept Head FINAL approval (After Executive)
        elseif ($current_status == 'Executive Approved' && $is_dept_head) {
            $new_status = 'Approved'; // Completed
            $column_to_update = 'final_dept_head_approved_by';
            $date_column = 'final_dept_head_approved_date';
        } 
        
        // IF NO MATCH, REJECT THE ATTEMPT
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