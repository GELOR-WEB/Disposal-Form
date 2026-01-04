<?php
session_start();
require_once '../db/conn.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Form ID required']);
    exit();
}

$form_id = $_GET['id'];

try {
    // Get form details
    $sql = "SELECT * FROM disposal_forms WHERE id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$form_id]);
    
    if ($stmt === false) {
        throw new Exception('Database error: ' . print_r(sqlsrv_errors(), true));
    }
    
    $form = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if (!$form) {
        throw new Exception('Form not found');
    }
    
    // Convert datetime to string
    if (isset($form['created_date']) && is_object($form['created_date'])) {
        $form['created_date'] = $form['created_date']->format('Y-m-d H:i:s');
    }
    
    sqlsrv_free_stmt($stmt);
    
    // Get items
    $sql = "SELECT * FROM disposal_items WHERE form_id = ? ORDER BY id";
    $stmt = sqlsrv_query($conn, $sql, [$form_id]);
    
    if ($stmt === false) {
        throw new Exception('Database error: ' . print_r(sqlsrv_errors(), true));
    }
    
    $items = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }
    
    sqlsrv_free_stmt($stmt);
    
    echo json_encode([
        'success' => true,
        'form' => $form,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>