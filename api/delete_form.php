<?php
session_start();
require_once '../db/conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['form_id'])) {
    echo json_encode(['success' => false, 'message' => 'Form ID required']);
    exit();
}

$form_id = $input['form_id'];
$user_id = $_SESSION['user_id'];

try {
    // Verify the form belongs to the user and is pending
    $sql = "SELECT status FROM disposal_forms WHERE id = ? AND user_id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$form_id, $user_id]);
    
    if ($stmt === false) {
        throw new Exception('Database error: ' . print_r(sqlsrv_errors(), true));
    }
    
    $form = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if (!$form) {
        throw new Exception('Form not found or you do not have permission to delete it');
    }
    
    if ($form['status'] !== 'Pending') {
        throw new Exception('Only pending forms can be deleted');
    }
    
    sqlsrv_free_stmt($stmt);
    
    // Start transaction
    if (function_exists('sqlsrv_begin_transaction')) {
        sqlsrv_begin_transaction($conn);
    }
    
    // Delete items first
    $sql = "DELETE FROM disposal_items WHERE form_id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$form_id]);
    
    if ($stmt === false) {
        throw new Exception('Failed to delete items: ' . print_r(sqlsrv_errors(), true));
    }
    
    sqlsrv_free_stmt($stmt);
    
    // Delete form
    $sql = "DELETE FROM disposal_forms WHERE id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$form_id]);
    
    if ($stmt === false) {
        throw new Exception('Failed to delete form: ' . print_r(sqlsrv_errors(), true));
    }
    
    sqlsrv_free_stmt($stmt);
    
    // Delete uploaded files
    $upload_dir = '../uploads/disposal_forms/' . $form_id;
    if (is_dir($upload_dir)) {
        $files = glob($upload_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($upload_dir);
    }
    
    // Commit transaction
    if (function_exists('sqlsrv_commit')) {
        sqlsrv_commit($conn);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Form deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    if (function_exists('sqlsrv_rollback')) {
        sqlsrv_rollback($conn);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>