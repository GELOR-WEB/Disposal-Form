<?php
session_start();
// 1. FIX PATH: Go up one level
require_once '../db/conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Verify user is supervisor
if ($_SESSION['job_level'] !== 'Supervisor' && $_SESSION['job_level'] !== 'Team Leader') {
    header("Location: staff_entry.php");
    exit();
}

// Initialize stats
$stats = [
    'total_disposal_forms' => 0,
    'total_items_disposed' => 0,
    'total_by_department' => [],
    'items_by_code' => []
];

try {
    // 2. FIX QUERIES: Convert sqlsrv_query to PDO
    
    // Total disposal forms
    $sql = "SELECT COUNT(*) as total FROM disposal_forms WHERE status = 'Approved'";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_disposal_forms'] = $row['total'];

    // Total items disposed
    $sql = "SELECT SUM(di.quantity) as total 
            FROM disposal_items di
            JOIN disposal_forms df ON di.form_id = df.id
            WHERE df.status = 'Approved'";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_items_disposed'] = $row['total'] ?? 0;

    // Total by department
    $sql = "SELECT department, COUNT(*) as total
            FROM disposal_forms
            WHERE status = 'Approved'
            GROUP BY department
            ORDER BY total DESC";
    $stmt = $conn->query($sql);
    $stats['total_by_department'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Items by code
    $sql = "SELECT di.code, di.description, SUM(di.quantity) as total_quantity, COUNT(*) as disposal_count
            FROM disposal_items di
            JOIN disposal_forms df ON di.form_id = df.id
            WHERE df.status = 'Approved'
            GROUP BY di.code, di.description
            ORDER BY total_quantity DESC";
    $stmt = $conn->query($sql);
    $stats['items_by_code'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports • La Rose Noire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#f472b6',
                        secondary: '#a78bfa',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        /* 4. FIX LAYOUT: Add padding for sidebar */
        body { 
            padding-left: 280px; 
            padding-top: 20px; 
            padding-right: 20px; 
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <?php include '../includes/sidebar.php'; ?>

    <div class="animate-fade-in max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">Reports Summary</h1>
            <button onclick="exportReports()" class="btn-primary flex items-center gap-2">
                <i class="fas fa-file-export"></i>
                <span>Export Reports</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card p-8 bg-white rounded-2xl shadow-lg">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-yellow-400 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-file-alt text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_disposal_forms']; ?></h3>
                        <p class="text-sm text-gray-600 font-semibold">Total Disposal Forms</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Approved forms only</p>
            </div>

            <div class="card p-8 bg-white rounded-2xl shadow-lg">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-blue-400 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-box-open text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_items_disposed']); ?></h3>
                        <p class="text-sm text-gray-600 font-semibold">Total Items Disposed</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500">All approved items</p>
            </div>

            <div class="card p-8 bg-white rounded-2xl shadow-lg">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-teal-400 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-building text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo count($stats['total_by_department']); ?></h3>
                        <p class="text-sm text-gray-600 font-semibold">Active Departments</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500">With disposal forms</p>
            </div>
        </div>

        <div class="bg-white/80 p-6 mb-8 rounded-2xl shadow border border-white/50 backdrop-blur-sm">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Disposal Forms by Department</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4">Department</th>
                            <th class="text-right py-3 px-4">Total Forms</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats['total_by_department'])): ?>
                            <tr><td colspan="2" class="text-center py-8 text-gray-500">No data available</td></tr>
                        <?php else: ?>
                            <?php foreach ($stats['total_by_department'] as $dept): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 font-semibold"><?php echo htmlspecialchars($dept['department']); ?></td>
                                    <td class="py-3 px-4 text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-pink-100 text-pink-600">
                                            <?php echo $dept['total']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function exportReports() {
            // Updated path to API
            window.location.href = '../api/export_reports.php';
        }
    </script>
</body>
</html>