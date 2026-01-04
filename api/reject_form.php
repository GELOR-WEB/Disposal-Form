<?php
// api/reject_form.php
header('Content-Type: application/json');
session_start();
require_once '../db/conn.php';

// Get JSON Input
$data = json_decode(file_get_contents("php://input"), true);

// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['job_level'], ['Supervisor', 'Team Leader'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// 2. Validate Input (We expect 'form_ids' array from the dashboard)
if (isset($data['form_ids']) && is_array($data['form_ids'])) {
    try {
        // 3. Prepare SQL with Correct Table & Column Names
        // We create placeholders (?,?,?) dynamically based on how many items are sent
        $placeholders = str_repeat('?,', count($data['form_ids']) - 1) . '?';
        
        $sql = "UPDATE disposal_forms 
                SET status = 'Rejected', 
                    approved_by = ?,       -- Uses User ID (Int), not Name
                    approved_date = GETDATE() 
                WHERE id IN ($placeholders)";
        
        $stmt = $conn->prepare($sql);
        
        // Combine User ID with the list of Form IDs for the execute params
        $params = array_merge([$_SESSION['user_id']], $data['form_ids']);
        $stmt->execute($params);

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    // Fallback for singular requests (just in case)
    echo json_encode(['success' => false, 'message' => 'No IDs provided']);
}
?>