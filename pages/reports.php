<?php
session_start();
require_once '../db/conn.php';

// 1. CHECK LOGIN
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] === 'Invalid') {
    // If role is invalid, log out the user
    header("Location: ../auth/logout.php");
    exit();
}

// 2. ROBUST SECURITY CHECK
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job  = strtolower($_SESSION['job_level'] ?? '');
$full_role_string = $my_role . ' ' . $my_job;

$is_admin_level = (
    strpos($full_role_string, 'admin') !== false || 
    strpos($full_role_string, 'facilities head') !== false ||
    strpos($full_role_string, 'supervisor') !== false || 
    strpos($full_role_string, 'manager') !== false || 
    strpos($full_role_string, 'leader') !== false ||
    $my_role === 'executive' ||
    $my_role === 'department head' ||
    $my_role === 'facilities head'
);

if (!$is_admin_level) {
    header("Location: staff_entry.php");
    exit();
}

// Initialize stats
$stats = [
    'today_count' => 0,
    'total_items' => 0,
    'total_depts' => 0,
    'report_data' => []
];

try {
    // A. TOTAL DISPOSAL PER DAY (Today's Count)
    // We check specifically for records created today
    $sql = "SELECT COUNT(*) as total FROM dsp_forms 
            WHERE CAST(created_date AS DATE) = CAST(GETDATE() AS DATE)";
    $stmt = $conn->query($sql);
    $stats['today_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // B. TOTAL DISPOSAL PER ITEM (Total Quantity of all items ever disposed)
    // Only counting Approved forms to be accurate
    $sql = "SELECT SUM(di.quantity) as total 
            FROM dsp_items di
            JOIN dsp_forms df ON di.form_id = df.id
            WHERE df.status = 'Approved'";
    $stmt = $conn->query($sql);
    $stats['total_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // C. TOTAL DISPOSAL PER DEPT (Count of unique active departments)
    $sql = "SELECT COUNT(DISTINCT department) as total FROM dsp_forms";
    $stmt = $conn->query($sql);
    $stats['total_depts'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // TABLE: DISPOSAL FORMS BY DEPARTMENT (PER DAY)
    // Groups by Date AND Department so duplicates sum up
    $sql = "SELECT 
                CAST(created_date AS DATE) as report_date, 
                department, 
                COUNT(*) as total_forms
            FROM dsp_forms
            GROUP BY CAST(created_date AS DATE), department
            ORDER BY report_date DESC, total_forms DESC";
    $stmt = $conn->query($sql);
    $stats['report_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Reports • La Rose Noire</title>
    <link rel="icon" type="image/jpg" href="../assets/images/favicon.jpg">
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
        body { 
            padding-left: 280px; 
            padding-top: 20px; 
            padding-right: 20px; 
        }

        @media (max-width: 768px) {
            body { padding-left: 20px !important; }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            body { padding-left: 240px !important; }
        }
    </style>
</head>
<body class="bg-[#fdf2f8]">
    
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
            
            <div class="card p-6 bg-white rounded-2xl shadow-lg border-l-4 border-pink-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Disposal Forms Today</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo $stats['today_count']; ?></h3>
                        <p class="text-xs text-pink-500 mt-1 font-medium"><?php echo date('F j, Y'); ?></p>
                    </div>
                    <div class="p-4 bg-pink-50 rounded-2xl text-pink-500">
                        <i class="fas fa-calendar-day text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="card p-6 bg-white rounded-2xl shadow-lg border-l-4 border-blue-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Items Disposed</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo number_format($stats['total_items']); ?></h3>
                        <p class="text-xs text-blue-500 mt-1 font-medium">Accumulated Quantity</p>
                    </div>
                    <div class="p-4 bg-grey-50 rounded-2xl text-blue-500">
                        <i class="fas fa-cubes text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="card p-6 bg-white rounded-2xl shadow-lg border-l-4 border-purple-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Departments</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo $stats['total_depts']; ?></h3>
                        <p class="text-xs text-purple-500 mt-1 font-medium">With Submission Records</p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-2xl text-purple-500">
                        <i class="fas fa-building text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white/80 p-6 mb-8 rounded-2xl shadow border border-white/50 backdrop-blur-sm">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Disposal Forms by Department (Daily Breakdown)</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-4 px-4 text-xs font-bold text-gray-500 uppercase">Date</th>
                            <th class="text-left py-4 px-4 text-xs font-bold text-gray-500 uppercase">Department</th>
                            <th class="text-right py-4 px-4 text-xs font-bold text-gray-500 uppercase">Total Forms</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats['report_data'])): ?>
                            <tr><td colspan="3" class="text-center py-8 text-gray-400">No records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($stats['report_data'] as $row): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-4 font-mono text-sm text-gray-600">
                                        <?php echo date('M j, Y', strtotime($row['report_date'])); ?>
                                    </td>
                                    
                                    <td class="py-4 px-4 font-semibold text-gray-700">
                                        <?php echo htmlspecialchars($row['department']); ?>
                                    </td>
                                    
                                    <td class="py-4 px-4 text-right">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold bg-pink-100 text-pink-600 shadow-sm">
                                            <?php echo $row['total_forms']; ?>
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
            window.location.href = '../api/export_reports.php';
        }
    </script>
</body>
</html>