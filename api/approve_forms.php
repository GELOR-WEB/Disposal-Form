<?php
// api/approve_forms.php
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

// 2. Validate Input
if (isset($data['form_ids']) && is_array($data['form_ids'])) {
    try {
        // 3. Prepare SQL with Correct Table & Column Names
        $placeholders = str_repeat('?,', count($data['form_ids']) - 1) . '?';
        
        $sql = "UPDATE disposal_forms 
                SET status = 'Approved', 
                    approved_by = ?,       -- Uses User ID (Int)
                    approved_date = GETDATE() 
                WHERE id IN ($placeholders)";
        
        $stmt = $conn->prepare($sql);
        
        // Combine User ID with the list of Form IDs
        $params = array_merge([$_SESSION['user_id']], $data['form_ids']);
        $stmt->execute($params);

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No IDs provided']);
}
?>