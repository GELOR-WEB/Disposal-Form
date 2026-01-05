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

$is_admin_level = (
    strpos($full_role_string, 'admin') !== false || 
    strpos($full_role_string, 'supervisor') !== false || 
    strpos($full_role_string, 'manager') !== false || 
    strpos($full_role_string, 'leader') !== false
);

// Determine the Rejector ID (Use user_id if set, otherwise username)
$rejector_id = $_SESSION['user_id'] ?? $_SESSION['username'] ?? null;

if (!$rejector_id || !$is_admin_level) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or Missing ID. Please Logout and Login again.']);
    exit();
}

// 2. Execute Update
if (isset($data['form_ids']) && is_array($data['form_ids'])) {
    try {
        $placeholders = str_repeat('?,', count($data['form_ids']) - 1) . '?';
        
        $sql = "UPDATE dsp_forms 
                SET status = 'Rejected', 
                    approved_by = ?, 
                    approved_date = GETDATE() 
                WHERE id IN ($placeholders)";
        
        $stmt = $conn->prepare($sql);
        
        // Use the safe rejector_id
        $params = array_merge([$rejector_id], $data['form_ids']);
        $stmt->execute($params);

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No IDs provided']);
}
?>