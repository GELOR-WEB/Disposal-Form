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
    if (!isset($_SESSION['username'])) {
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

    $sql = "INSERT INTO dsp_forms (full_name, department, created_date, status) 
            OUTPUT INSERTED.id 
            VALUES (?, ?, GETDATE(), 'Pending')";

    $stmt = $conn->prepare($sql);

    // Clean inputs (Defensive code from before)
    $nameToSave = $full_name ?? $_SESSION['fullname'] ?? 'Unknown User';
    // Prioritize the Scoping Department from our config, so it matches the view filter
    $deptToSave = $_SESSION['scoping_dept'] ?? $department ?? $_SESSION['department'] ?? 'Unknown Dept';

    $stmt->execute([$nameToSave, $deptToSave]);

    // Now we can just 'fetch' the ID like a normal row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $form_id = $row['id']; // Success! We have the ID.

    if (!$row || !isset($row['id'])) {
        // If this fails, we throw the actual database error to help debug
        $err = $stmt->errorInfo();
        throw new Exception("Failed to create form. DB Error: " . ($err[2] ?? 'Unknown'));
    }

    $form_id = $row['id'];

    // 5. Insert Items
    if (!isset($_POST['items_data'])) {
        throw new Exception("No items found in the submission.");
    }

    $items = json_decode($_POST['items_data'], true);
    if (!$items) {
        throw new Exception("Item data is invalid.");
    }

    // FIX: Changed 'reason_for_disposal' to 'reason' to match your Database
    $sql_item = "INSERT INTO dsp_items (form_id, code, description, serial_no, unit_of_measure, quantity, reason, image_path) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_item = $conn->prepare($sql_item);

    foreach ($items as $item) {
        // Handle File Upload
        $fileName = "No Image";
        $fileKey = 'item_file_' . $item['temp_id'];

        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0777, true); // Create folder if missing

            $newFileName = $form_id . '_' . uniqid() . '_' . basename($_FILES[$fileKey]['name']);
            $targetPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetPath)) {
                $fileName = $newFileName;
            }
        }

        // Insert Item Row
        $stmt_item->execute([
            $form_id,
            $item['code'] ?? '',
            $item['desc'] ?? 'No Description',
            $item['serial'] ?? '',
            $item['uom'] ?? 'PCS',
            $item['qty'] ?? 0,
            $item['reason'] ?? '', // This maps to the 'reason' column above
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