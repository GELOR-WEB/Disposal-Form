<?php
// api/export_reports.php
session_start();
require_once '../db/conn.php';

// 1. ROBUST SECURITY CHECK
// We use the same "Admin Detection" logic as the rest of your app
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job  = strtolower($_SESSION['job_level'] ?? '');
$full_role_string = $my_role . ' ' . $my_job;

$is_admin_level = (
    strpos($full_role_string, 'admin') !== false || 
    strpos($full_role_string, 'supervisor') !== false || 
    strpos($full_role_string, 'manager') !== false || 
    strpos($full_role_string, 'leader') !== false
);

if (!isset($_SESSION['username']) || !$is_admin_level) {
    die("Access Denied: You do not have permission to export reports.");
}

// 2. Set Headers to force download as Excel (.xls)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Disposal_Report_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// 3. Output Column Headers
echo "Control No.\tDate Created\tDepartment\tCreated By\tItem Code\tDescription\tQty\tUOM\tStatus\tApproved By\n";

try {
    // 4. Fetch Data using PDO (Updated Table Names)
    // Changed 'disposal_forms' -> 'dsp_forms' and 'disposal_items' -> 'dsp_items'
    $sql = "SELECT 
                df.id as control_no,
                df.created_date,
                df.department,
                df.full_name as created_by,
                di.code,
                di.description,
                di.quantity,
                di.unit_of_measure,
                df.status,
                df.approved_by
            FROM dsp_forms df
            JOIN dsp_items di ON df.id = di.form_id
            WHERE df.status = 'Approved'
            ORDER BY df.created_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Loop through data and print rows
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            // Clean data to prevent breaking the Excel format
            $desc = str_replace(["\t", "\n"], " ", $row['description']);
            $date = date('Y-m-d H:i', strtotime($row['created_date']));
            
            // Handle potentially missing approved_by
            $approver = $row['approved_by'] ?? 'System';

            echo "{$row['control_no']}\t{$date}\t{$row['department']}\t{$row['created_by']}\t{$row['code']}\t{$desc}\t{$row['quantity']}\t{$row['unit_of_measure']}\t{$row['status']}\t{$approver}\n";
        }
    } else {
        // Output nothing (or a message) if no rows, but usually blank is better for excel parsing
        // echo "No approved disposal records found.\n";
    }

} catch (PDOException $e) {
    echo "Error generating report: " . $e->getMessage();
}
?>