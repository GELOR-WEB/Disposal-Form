<?php
// api/export_reports.php
session_start();
require_once '../db/conn.php';

// 1. ROBUST SECURITY CHECK
// Only Admin, Dept Head, and Executive can export reports
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job  = strtolower($_SESSION['job_level'] ?? '');
$full_role_string = $my_role . ' ' . $my_job;

$is_admin = (strpos($full_role_string, 'admin') !== false);
$is_dept_head = (strpos($full_role_string, 'head') !== false || strpos($full_role_string, 'department head') !== false);
$is_executive = (strpos($full_role_string, 'executive') !== false);

$can_export = $is_admin || $is_dept_head || $is_executive;

if (!isset($_SESSION['username']) || !$can_export) {
    die("Access Denied: Only Admin, Department Head, and Executive roles can export reports.");
}

// 2. Set Headers to force download as Excel (.xls)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Disposal_Report_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// 3. Output Column Headers
echo "Control No.\tDate Created\tDepartment\tCreated By\tItem Code\tDescription\tQty\tUOM\tStatus\n";

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
                df.status
            FROM dsp_forms df
            JOIN dsp_items di ON df.id = di.form_id
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
            
            // Format status for display
            $display_status = $row['status'] == 'Approved' ? 'Fully Approved' : $row['status'];
            
            echo "{$row['control_no']}\t{$date}\t{$row['department']}\t{$row['created_by']}\t{$row['code']}\t{$desc}\t{$row['quantity']}\t{$row['unit_of_measure']}\t{$display_status}\n";
        }
    } else {
        // Output nothing (or a message) if no rows, but usually blank is better for excel parsing
        // echo "No approved disposal records found.\n";
    }

} catch (PDOException $e) {
    echo "Error generating report: " . $e->getMessage();
}
?>