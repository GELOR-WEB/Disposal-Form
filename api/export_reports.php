<?php
// api/export_reports.php
session_start();
require_once '../db/conn.php';

// 1. Security Check
if (!isset($_SESSION['username']) || ($_SESSION['job_level'] !== 'Supervisor' && $_SESSION['job_level'] !== 'Team Leader')) {
    die("Access Denied");
}

// 2. Set Headers to force download as Excel (.xls)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Disposal_Report_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// 3. Output Column Headers
echo "Control No.\tDate Created\tDepartment\tCreated By\tItem Code\tDescription\tQty\tUOM\tStatus\tApproved By\n";

try {
    // 4. Fetch Data using PDO (The Fix)
    // We join the Forms table with the Items table to get a complete list
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
            FROM disposal_forms df
            JOIN disposal_items di ON df.id = di.form_id
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
            
            echo "{$row['control_no']}\t{$date}\t{$row['department']}\t{$row['created_by']}\t{$row['code']}\t{$desc}\t{$row['quantity']}\t{$row['unit_of_measure']}\t{$row['status']}\t{$row['approved_by']}\n";
        }
    } else {
        echo "No approved disposal records found.\n";
    }

} catch (PDOException $e) {
    echo "Error generating report: " . $e->getMessage();
}
?>