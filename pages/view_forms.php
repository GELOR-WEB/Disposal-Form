<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../db/conn.php';

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $per_page;

// Get total count for pagination
try {
    $total_sql = "SELECT COUNT(*) as total FROM dsp_forms";
    $total_stmt = $conn->query($total_sql);
    $total_forms = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_forms / $per_page);
} catch (PDOException $e) {
    $total_forms = 0;
    $total_pages = 1;
}

// Get all departments for filter
try {
    $dept_sql = "SELECT DISTINCT department FROM dsp_forms ORDER BY department";
    $dept_stmt = $conn->query($dept_sql);
    $all_depts = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $all_depts = [];
}

// Get paginated forms
try {
    $sql = "SELECT * FROM dsp_forms ORDER BY created_date DESC OFFSET $offset ROWS FETCH NEXT $per_page ROWS ONLY";
    $stmt = $conn->query($sql);
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Check if forms exist
    if (empty($forms)) {
        echo "<!-- DEBUG: No forms found in database -->";
    } else {
        echo "<!-- DEBUG: Found " . count($forms) . " forms -->";
    }
} catch (PDOException $e) {
    echo "<!-- DEBUG: Database error: " . $e->getMessage() . " -->";
    $forms = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Forms • Facilities</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#f472b6',
                        'primary-dark': '#ec4899',
                        secondary: '#a78bfa',
                        success: '#34d399',
                        warning: '#fbbf24',
                        danger: '#f87171'
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/jpg" href="../assets/favicon.jpg">
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        /* 3. UPDATED: Adjusted padding to match dashboard */
        body { 
            padding-left: 280px; 
            padding-top: 20px; 
            padding-right: 20px; 
            overflow-y: hidden;
        }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; text-align: center; min-width: 120px; display: inline-block; white-space: nowrap; }
        .status-Pending { background: #fef3c7; color: #92400e; }
        .status-Approved { background: #dcfce7; color: #166534; }
        .status-Rejected { background: #fee2e2; color: #991b1b; }
        .status-Completed { background: #dcfce7; color: #166534; }
        .status-AdminApproved { background: #dbeafe; color: #1e40af; }
        .status-DeptHeadApproved { background: #ede9fe; color: #7c3aed; }
        .status-ExecutiveApproved { background: #fed7aa; color: #9a3412; }
        .btn { 
            padding: 8px 16px; 
            border: 1px solid #e5e7eb; 
            background: white; 
            cursor: pointer; 
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
        }
        .btn:active {
            background: #f3f4f6;
            transform: translateY(1px);
        }
        .table-container {
            max-height: calc(80vh - 200px);
            overflow-y: auto;
            overflow-x: hidden;
        }
        .table thead th {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="animate-fade-in max-w-7xl mx-auto">
        <div class="glass-panel p-6 mb-8 flex justify-between items-center" style="border-radius: 16px;">
            <div>
                <h1 style="margin:0; font-size: 1.8rem;">Submitted Disposals</h1>
                <p style="margin:0; color:#6b7280;">Manage and track facility disposal requests</p>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap gap-4">
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                <input type="text" id="controlNoSearch" class="form-input" placeholder="Search Control No..." style="padding-left: 30px; width: 200px;">
            </div>
            <button id="sortDateAsc" class="btn">Latest ↑</button>
            <button id="sortDateDesc" class="btn">Oldest ↓</button>
            <select id="deptFilter" class="form-input" style="width: 200px;">
                <option value="">All Departments</option>
                <?php
                foreach ($all_depts as $dept) {
                    echo "<option value=\"$dept\">$dept</option>";
                }
                ?>
            </select>
            <input type="text" id="nameSearch" placeholder="Search Name..." class="form-input" style="width: 200px;">
            <select id="statusFilter" class="form-input" style="width: 150px;">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Admin Approved">Admin Approved</option>
                <option value="Dept Head Approved">Dept Head Approved</option>
                <option value="Executive Approved">Executive Approved</option>
                <option value="Completed">Completed</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>

        <div class="card p-6 overflow-x-auto">
            <div class="table w-full">
                <table class="w-full" style="border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[10%]">Control No.</th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[17%]">Date Created</th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[25%]">Department</th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[25%]">Created By</th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[18%]">Status</th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[12%]">Details</th>
                        </tr>
                    </thead>
                </table>
                <div class="table-container">
                    <table class="w-full" style="border-collapse: collapse; min-width: 800px;">
                        <tbody class="divide-y divide-gray-50">
                        <?php if (count($forms) > 0): ?>
                            <?php foreach ($forms as $form): ?>
                                <tr class="hover:bg-gray-50 transition-colors" data-date="<?php echo strtotime($form['created_date']); ?>">
                                    <td class="py-4 px-4 font-mono text-pink-600 font-bold text-sm">
                                        CN-<?php echo date('Y', strtotime($form['created_date'])); ?>-<?php echo str_pad($form['id'], 4, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600 text-sm">
                                        <?php echo date('M d, Y h:i A', strtotime($form['created_date'])); ?>
                                    </td>
                                    <td class="py-4 px-4 text-gray-800 font-medium text-sm">
                                        <?php echo $form['department']; ?>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs text-gray-500">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700"><?php echo $form['full_name']; ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <?php 
                                        $statusClass = match($form['status']) {
                                            'Approved' => 'status-Completed',
                                            'Rejected' => 'status-Rejected',
                                            'Admin Approved' => 'status-AdminApproved',
                                            'Dept Head Approved' => 'status-DeptHeadApproved',
                                            'Executive Approved' => 'status-ExecutiveApproved',
                                            default => 'status-Pending'
                                        };
                                        $displayStatus = $form['status'] == 'Approved' ? 'Completed' : $form['status'];
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo $displayStatus; ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <a href="view_details.php?id=<?php echo $form['id']; ?>" class="bg-white border border-gray-200 text-gray-600 hover:text-pink-600 hover:border-pink-300 rounded-lg text-xs font-bold transition-all shadow-sm">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-8 text-gray-400">No forms found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="mt-6 flex justify-center">
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="px-3 py-2 <?php echo $i == $page ? 'bg-pink-500 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'; ?> rounded-md text-sm font-medium">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
    function filterTable() {
        const controlNo = document.getElementById('controlNoSearch').value.toLowerCase();
        const status = document.getElementById('statusFilter').value;
        const dept = document.getElementById('deptFilter').value;
        const name = document.getElementById('nameSearch').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const control = cells[0].textContent.toLowerCase();
            const deptCell = cells[2].textContent;
            const nameCell = cells[3].textContent.toLowerCase();
            const statusCell = cells[4].textContent.trim();
            const show = (!controlNo || control.includes(controlNo)) &&
                         (!status || statusCell === status) &&
                         (!dept || deptCell === dept) &&
                         (!name || nameCell.includes(name));
            row.style.display = show ? '' : 'none';
        });
    }

    function sortTable(asc) {
        const tbody = document.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const dateA = parseInt(a.dataset.date);
            const dateB = parseInt(b.dataset.date);
            return asc ? dateA - dateB : dateB - dateA;
        });
        rows.forEach(row => tbody.appendChild(row));
    }

    document.getElementById('controlNoSearch').addEventListener('input', filterTable);
    document.getElementById('statusFilter').addEventListener('change', filterTable);
    document.getElementById('deptFilter').addEventListener('change', filterTable);
    document.getElementById('nameSearch').addEventListener('input', filterTable);
    document.getElementById('sortDateAsc').addEventListener('click', () => sortTable(true));
    document.getElementById('sortDateDesc').addEventListener('click', () => sortTable(false));
    </script>
</body>
</html>