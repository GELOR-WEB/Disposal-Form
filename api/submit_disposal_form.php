<?php
// api/submit_disposal_form.php
header('Content-Type: application/json');
session_start();

// 1. Error Handling Setup
ini_set('display_errors', 0); 
error_reporting(E_ALL);

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    // 2. Database Connection
    require_once '../db/conn.php';

    // 3. Validation
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not logged in.");
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // Decode items
    $items = json_decode($_POST['items_data'], true);
    if (!$items || count($items) === 0) {
        throw new Exception("No items provided.");
    }

    // 4. Start Transaction
    $conn->beginTransaction();

    // =========================================================
    // FIX IS HERE: Use OUTPUT INSERTED.id for SQL Server
    // =========================================================
    $sqlHeader = "INSERT INTO disposal_forms (user_id, full_name, department, status, created_date) 
                  OUTPUT INSERTED.id 
                  VALUES (?, ?, ?, 'Pending', GETDATE())";
    
    $stmt = $conn->prepare($sqlHeader);
    $stmt->execute([
        $_SESSION['user_id'],
        $_SESSION['full_name'],
        $_SESSION['dept']
    ]);
    
    // Fetch the ID directly from the INSERT command results
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row || !isset($row['id'])) {
        // If this fails, we throw the actual database error to help debug
        $err = $stmt->errorInfo();
        throw new Exception("Failed to create form. DB Error: " . ($err[2] ?? 'Unknown'));
    }
    
    $form_id = $row['id'];

    // 5. Insert Items
    $sqlItem = "INSERT INTO disposal_items 
                (form_id, code, description, serial_no, unit_of_measure, quantity, reason_for_disposal, attachment_pictures) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtItem = $conn->prepare($sqlItem);

    foreach ($items as $item) {
        // Handle File Upload
        $fileName = "No Image";
        $fileKey = 'item_file_' . $item['temp_id']; 

        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true); // Create folder if missing
            
            $newFileName = $form_id . '_' . uniqid() . '_' . basename($_FILES[$fileKey]['name']);
            $targetPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetPath)) {
                $fileName = $newFileName;
            }
        }

        // Insert Item Row
        $stmtItem->execute([
            $form_id,
            $item['code'],
            $item['desc'],
            $item['serial'],
            $item['uom'],
            $item['qty'],
            $item['reason'],
            $fileName
        ]);
    }

    // 6. Commit Transaction
    $conn->commit();

    $response['success'] = true;
    $response['form_id'] = $form_id;

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>