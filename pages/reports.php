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
$my_job = strtolower($_SESSION['job_level'] ?? '');
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

$dept_filter = isset($_GET['dept']) ? trim($_GET['dept']) : '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 10; // Items per page
$offset = ($page - 1) * $per_page;
$total_pages = 1;

try {
    // A. TOTAL DISPOSAL PER DAY (Today's Count)
    $sql = "SELECT COUNT(*) as total FROM dsp_forms 
            WHERE CAST(created_date AS DATE) = CAST(GETDATE() AS DATE)";
    $stmt = $conn->query($sql);
    $stats['today_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // B. TOTAL DISPOSAL PER ITEM (Total Quantity)
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

    // D. FETCH DISTINCT DEPARTMENTS FOR FILTER
    $dept_sql = "SELECT DISTINCT department FROM dsp_forms ORDER BY department ASC";
    $dept_stmt = $conn->query($dept_sql);
    $all_depts = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);

    // E. TABLE: DISPOSAL FORMS BY DEPARTMENT (PER DAY)
    // 1. Build Where Clause
    $where_sql = "";
    $params = [];
    if (!empty($dept_filter)) {
        $where_sql = " WHERE department = :dept";
        $params[':dept'] = $dept_filter;
    }

    // 2. Count Total Groups (for Pagination)
    // We need to count the number of resulting rows (grouped by date/dept)
    $count_sql = "SELECT COUNT(*) as total_rows FROM (
                    SELECT CAST(created_date AS DATE) as report_date, department
                    FROM dsp_forms
                    $where_sql
                    GROUP BY CAST(created_date AS DATE), department
                  ) as sub";

    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_rows'];
    $total_pages = ceil($total_rows / $per_page);
    $page = max(1, min($page, $total_pages)); // Ensure page is valid
    $offset = ($page - 1) * $per_page;
    if ($offset < 0)
        $offset = 0; // Fix potential negative offset if total_rows is 0

    // 3. Fetch Data
    $sql = "SELECT 
                CAST(created_date AS DATE) as report_date, 
                department, 
                COUNT(*) as total_forms
            FROM dsp_forms
            $where_sql
            GROUP BY CAST(created_date AS DATE), department
            ORDER BY report_date DESC, total_forms DESC
            OFFSET $offset ROWS FETCH NEXT $per_page ROWS ONLY";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
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
            body {
                padding-left: 20px !important;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            body {
                padding-left: 240px !important;
            }
        }

        /* Custom Scrollbar for Table */
        .table-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #fdf2f8;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background-color: #f472b6;
            border-radius: 4px;
            border: 2px solid #fdf2f8;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background-color: #ec4899;
        }
    </style>
</head>

<body class="bg-[#fdf2f8]">
    <?php include '../includes/sidebar.php'; ?>

    <div class="animate-fade-in max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">Reports Summary</h1><button onclick="exportReports()"
                class="btn-primary flex items-center gap-2"><i class="fas fa-file-export"></i><span>Export
                    Reports</span></button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card p-6 bg-white rounded-2xl shadow-lg border-l-4 border-pink-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Disposal Forms Today</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo $stats['today_count']; ?></h3>
                        <p class="text-xs text-pink-500 mt-1 font-medium"><?php echo date('F j, Y'); ?></p>
                    </div>
                    <div class="p-4 bg-pink-50 rounded-2xl text-pink-500"><i class="fas fa-calendar-day text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="card p-6 bg-white rounded-2xl shadow-lg border-l-4 border-blue-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Items Disposed</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2">
                            <?php echo number_format($stats['total_items']); ?></h3>
                        <p class="text-xs text-blue-500 mt-1 font-medium">Accumulated Quantity</p>
                    </div>
                    <div class="p-4 bg-grey-50 rounded-2xl text-blue-500"><i class="fas fa-cubes text-2xl"></i></div>
                </div>
            </div>
            <div class="card p-6 bg-white rounded-2xl shadow-lg border-l-4 border-purple-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Departments</p>
                        <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo $stats['total_depts']; ?></h3>
                        <p class="text-xs text-purple-500 mt-1 font-medium">With Submission Records</p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-2xl text-purple-500"><i class="fas fa-building text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white/80 p-6 mb-8 rounded-2xl shadow border border-white/50 backdrop-blur-sm">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h2 class="text-xl font-bold text-gray-800">Disposal Forms by Department (Daily Breakdown)</h2>
                <form method="GET" class="flex items-center gap-3">
                    <div class="relative"><select name="dept" onchange="this.form.submit()"
                            class="pl-4 pr-10 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-400 focus:border-pink-400 outline-none appearance-none bg-white text-sm text-gray-600 font-medium shadow-sm transition-all cursor-pointer hover:border-pink-300">
                            <option value="">All Departments</option>
                            <?php foreach ($all_depts as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo $dept_filter === $dept ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-pink-400"><i
                                class="fas fa-chevron-down text-xs"></i></div>
                    </div>
                </form>
            </div>
            <div
                class="overflow-x-auto rounded-xl border border-gray-100 max-h-[500px] overflow-y-auto table-container">
                <table class="w-full relative">
                    <thead class="bg-gray-50/50 sticky top-0 z-10 shadow-sm">
                        <tr class="border-b border-gray-100">
                            <th
                                class="text-left py-4 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50/50 backdrop-blur-sm">
                                Date</th>
                            <th
                                class="text-left py-4 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50/50 backdrop-blur-sm">
                                Department</th>
                            <th
                                class="text-right py-4 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50/50 backdrop-blur-sm">
                                Total Forms</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($stats['report_data'])): ?>
                            <tr>
                                <td colspan="3" class="text-center py-12 text-gray-400 text-sm">No records found matching
                                    your criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stats['report_data'] as $row): ?>
                                <tr class="hover:bg-pink-50/30 transition-colors group">
                                    <td
                                        class="py-4 px-4 font-mono text-sm text-gray-600 group-hover:text-pink-600 transition-colors">
                                        <?php echo date('M j, Y', strtotime($row['report_date'])); ?>
                                    </td>
                                    <td class="py-4 px-4 font-semibold text-gray-700">
                                        <?php echo htmlspecialchars($row['department']); ?>
                                    </td>
                                    <td class="py-4 px-4 text-right"><span
                                            class="inline-flex items-center justify-center min-w-[2rem] h-8 px-3 rounded-full text-sm font-bold bg-pink-100 text-pink-600 shadow-sm border border-pink-200">
                                            <?php echo $row['total_forms']; ?>
                                        </span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_pages > 1): ?>
                <div class="mt-6 flex justify-center">
                    <div class="flex items-center gap-2 bg-white p-1 rounded-xl shadow-sm border border-gray-100">
                        < !-- Prev Page -->
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&dept=<?php echo urlencode($dept_filter); ?>"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-pink-50 hover:text-pink-600 transition-colors"><i
                                        class="fas fa-chevron-left text-xs"></i></a>
                            <?php else: ?>
                                <span
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300 cursor-not-allowed"><i
                                        class="fas fa-chevron-left text-xs"></i></span>
                            <?php endif; ?>

                            < !-- Page Numbers -->
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&dept=<?php echo urlencode($dept_filter); ?>"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-bold transition-all <?php echo $i == $page ? 'bg-pink-500 text-white shadow-md' : 'text-gray-600 hover:bg-pink-50 hover:text-pink-600'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                < !-- Next Page -->
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&dept=<?php echo urlencode($dept_filter); ?>"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-pink-50 hover:text-pink-600 transition-colors"><i
                                                class="fas fa-chevron-right text-xs"></i></a>
                                    <?php else: ?>
                                        <span
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300 cursor-not-allowed"><i
                                                class="fas fa-chevron-right text-xs"></i></span>
                                    <?php endif; ?>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
    <script>function exportReports() {
            window.location.href = '../api/export_reports.php';
        }

    </script>
</body>

</html>