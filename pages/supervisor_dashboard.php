<?php
session_start();
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db/conn.php';

// 1. CHECK LOGIN
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. ROBUST SECURITY CHECK
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job = strtolower($_SESSION['job_level'] ?? '');
$my_dept = $_SESSION['department'] ?? ''; // Added department check
$full_role_string = $my_role . ' ' . $my_job;

$is_admin = (strpos($full_role_string, 'admin') !== false || strpos($full_role_string, 'facilities head') !== false); // Acts as Fac Head
$is_dept_head = (strpos($full_role_string, 'department head') !== false); // Stricter check
$is_executive = (strpos($full_role_string, 'executive') !== false);

$is_admin_level = $is_admin || $is_dept_head || $is_executive ||
    strpos($full_role_string, 'supervisor') !== false ||
    strpos($full_role_string, 'manager') !== false ||
    strpos($full_role_string, 'leader') !== false ||
    $my_role === 'executive' ||
    $my_role === 'department head' ||
    $my_role === 'facilities head';

if (!$is_admin_level) {
    header("Location: view_forms.php");
    exit();
}

$forms = [];
$error_message = "";

// --- PAGINATION SETUP ---
$per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $per_page;
$total_pages = 1;

try {
    // 1. Get Total Count
    $total_sql = "SELECT COUNT(*) as total FROM dsp_forms";
    $total_stmt = $conn->query($total_sql);
    $total_forms = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_forms / $per_page);

    // 2. Fetch Forms with Pagination
    $sql = "SELECT df.id, df.full_name, df.created_date, df.department, df.status, df.rejection_reason,
            df.dept_head_approved_date, 
            df.fac_head_approved_date, 
            df.executive_approved_date, 
            df.final_fac_head_approved_date,
            COUNT(di.id) as item_count
            FROM dsp_forms df
            LEFT JOIN dsp_items di ON df.id = di.form_id
            GROUP BY df.id, df.full_name, df.created_date, df.department, df.status, df.rejection_reason,
                     df.dept_head_approved_date, df.fac_head_approved_date, 
                     df.executive_approved_date, df.final_fac_head_approved_date
            ORDER BY df.created_date DESC
            OFFSET $offset ROWS FETCH NEXT $per_page ROWS ONLY";

    $stmt = $conn->query($sql);

    if ($stmt === false) {
        throw new Exception(print_r($conn->errorInfo(), true));
    }

    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Supervisor Dashboard</title>
    <link rel="stylesheet" href="../styles/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/jpg" href="../assets/favicon.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Responsive body adjustments */
        @media (max-width: 768px) {
            .ml-64 {
                margin-left: 20px !important;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .ml-64 {
                margin-left: 240px !important;
            }
        }

        /* Flexbox table container */
        .table-container {
            flex: 1;
            /* This ensures it fills only available space */
            overflow-y: auto;
            /* Enable horizontal scroll for wide tables on tablets */
            overflow-x: auto;
            width: 100%;
            min-height: 0;
            /* Critical for Firefox flex containers */
        }

        /* Ensure table wrapper allows horizontal scroll */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Responsive table adjustments for tablets */
        @media (max-width: 1440px) {
            .table-container {
                overflow-x: auto;
            }
        }

        .table-container::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #fdf2f8;
            border-left: 1px solid #fce7f3;
        }

        .table-container::-webkit-scrollbar-thumb {
            background-image: linear-gradient(to bottom, #f472b6, #db2777);
            border-radius: 10px;
            border: 2px solid #fdf2f8;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background-image: linear-gradient(to bottom, #ec4899, #be185d);
        }

        /* Responsive container adjustments for tablets */
        @media (min-width: 769px) and (max-width: 1440px) {
            .glass-panel {
                margin-bottom: 2rem !important;
            }
        }
    </style>
</head>

<body class="bg-[#fdf2f8] text-gray-800 overflow-y-hidden">

    <?php if (file_exists('../includes/sidebar.php'))
        include '../includes/sidebar.php'; ?>

    <div class="ml-64 p-10 animate-fade-in h-screen flex flex-col box-border">
        <div class="flex-none">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Approvals Dashboard</h1>
            <p class="text-gray-500 mb-4 md:mb-8">Manage and review disposal requests.</p>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                    <strong class="font-bold">Database Error:</strong> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="mb-6 flex flex-wrap gap-4">
                <input type="text" id="controlNoSearch" placeholder="Search Crtl No..." class="form-input"
                    style="width: 150px;">
                <input type="text" id="createdBySearch" placeholder="Search Created By..." class="form-input"
                    style="width: 200px;">
                <select id="deptFilter" class="form-input" style="width: 200px;">
                    <option value="">All Departments</option>
                    <?php
                    $depts = array_unique(array_column($forms, 'department'));
                    foreach ($depts as $dept) {
                        echo "<option value=\"$dept\">$dept</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div
            class="glass-panel bg-white/80 rounded-2xl shadow-xl border border-white/50 pb-0 pr-0 pl-0 flex-1 flex flex-col min-h-0 mb-4 overflow-hidden">

            <!-- Single unified scrollable table -->
            <div class="table-container">
                <table class="w-full" style="table-layout: fixed; min-width: 1100px; border-collapse: collapse;">
                    <thead class="bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-[10%]">
                                Control No.</th>
                            <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-[15%]">
                                Created By</th>
                            <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-[15%]">
                                Department</th>
                            <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-[8%]">
                                Items</th>
                            <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-[12%]">
                                Status</th>
                            <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-[20%]">
                                Approval Progress</th>
                            <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-[20%]">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($forms)): ?>
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-400">No disposal forms found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($forms as $form): ?>
                                <tr class="hover:bg-pink-50/30 transition-colors"
                                    data-date="<?php echo strtotime($form['created_date']); ?>">
                                    <td class="p-5 font-mono text-sm text-pink-500 font-bold w-[10%]">
                                        CN-<?php echo date('Y', strtotime($form['created_date'])); ?>-<?php echo str_pad($form['id'], 4, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td class="p-5 font-medium w-[15%] text-sm"><?php echo $form['full_name']; ?></td>
                                    <td class="p-5 text-gray-500 w-[15%] text-sm"><?php echo $form['department']; ?></td>
                                    <td class="p-5 w-[8%]">
                                        <span
                                            class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold"><?php echo $form['item_count']; ?></span>
                                    </td>
                                    <td class="p-5 w-[50%]">
                                        <?php
                                        $statusColor = match ($form['status']) {
                                            'Approved' => 'bg-green-100 text-green-700',
                                            'Rejected' => 'bg-red-100 text-red-700',
                                            'Dept Head Approved' => 'bg-purple-100 text-purple-700',
                                            'Fac Head Approved' => 'bg-blue-100 text-blue-700',
                                            'Executive Approved' => 'bg-orange-100 text-orange-700',
                                            default => 'bg-yellow-100 text-yellow-700'
                                        };
                                        ?>
                                        <span
                                            class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide <?php echo $statusColor; ?>">
                                            <?php echo $form['status'] == 'Approved' ? 'Completed' : $form['status']; ?>
                                        </span>
                                    </td>

                                    <td class="p-5 w-[20%]">
                                        <div class="flex flex-col gap-1 text-[11px] font-medium text-gray-500">

                                            <div class="flex items-center justify-between">
                                                <span>Dept Head:</span>
                                                <?php if (!empty($form['dept_head_approved_date'])): ?>
                                                    <i class="fas fa-check-circle text-green-500"></i>
                                                <?php elseif ($form['status'] == 'Rejected' && empty($form['dept_head_approved_date'])): ?>
                                                    <i class="fas fa-times-circle text-red-500"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-circle text-gray-200"></i>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($form['status'] == 'Rejected' && empty($form['dept_head_approved_date']) && !empty($form['rejection_reason'])): ?>
                                            <?php endif; ?>

                                            <div class="flex items-center justify-between">
                                                <span>Fac Head:</span>
                                                <?php if (!empty($form['fac_head_approved_date'])): ?>
                                                    <i class="fas fa-check-circle text-green-500"></i>
                                                <?php elseif ($form['status'] == 'Rejected' && !empty($form['dept_head_approved_date']) && empty($form['fac_head_approved_date'])): ?>
                                                    <i class="fas fa-times-circle text-red-500"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-circle text-gray-200"></i>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($form['status'] == 'Rejected' && !empty($form['dept_head_approved_date']) && empty($form['fac_head_approved_date']) && !empty($form['rejection_reason'])): ?>
                                                <div class="text-[10px] text-red-400 italic mt-0.5 text-right">
                                                    "<?php echo htmlspecialchars($form['rejection_reason']); ?>"</div>
                                            <?php endif; ?>

                                            <div class="flex items-center justify-between">
                                                <span>Executive:</span>
                                                <?php if (!empty($form['executive_approved_date'])): ?>
                                                    <i class="fas fa-check-circle text-green-500"></i>
                                                <?php elseif ($form['status'] == 'Rejected' && !empty($form['fac_head_approved_date']) && empty($form['executive_approved_date'])): ?>
                                                    <i class="fas fa-times-circle text-red-500"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-circle text-gray-200"></i>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($form['status'] == 'Rejected' && !empty($form['fac_head_approved_date']) && empty($form['executive_approved_date']) && !empty($form['rejection_reason'])): ?>
                                                <div class="text-[10px] text-red-400 italic mt-0.5 text-right">
                                                    "<?php echo htmlspecialchars($form['rejection_reason']); ?>"</div>
                                            <?php endif; ?>

                                            <div class="flex items-center justify-between">
                                                <span>Final:</span>
                                                <?php if (!empty($form['final_fac_head_approved_date'])): ?>
                                                    <i class="fas fa-check-circle text-green-500"></i>
                                                <?php elseif ($form['status'] == 'Rejected' && !empty($form['executive_approved_date']) && empty($form['final_fac_head_approved_date'])): ?>
                                                    <i class="fas fa-times-circle text-red-500"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-circle text-gray-200"></i>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($form['status'] == 'Rejected' && !empty($form['executive_approved_date']) && empty($form['final_fac_head_approved_date']) && !empty($form['rejection_reason'])): ?>
                                                <div class="text-[10px] text-red-400 italic mt-0.5 text-right">
                                                    "<?php echo htmlspecialchars($form['rejection_reason']); ?>"</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="p-5 flex gap-2 w-[100%] flex-wrap content-start">
                                        <?php
                                        $can_approve = false;
                                        $button_text = "Approve";

                                        if ($form['status'] == 'Pending' && $is_dept_head && $my_dept == $form['department']) {
                                            $can_approve = true;
                                            $button_text = "Dept Approval";
                                        } elseif ($form['status'] == 'Dept Head Approved' && $is_admin) {
                                            $can_approve = true;
                                            $button_text = "Fac Head Check";
                                        } elseif (($form['status'] == 'Fac Head Approved' || $form['status'] == 'Admin Approved') && $is_executive) {
                                            $can_approve = true;
                                            $button_text = "Exec Approval";
                                        } elseif ($form['status'] == 'Executive Approved' && $is_admin) {
                                            $can_approve = true;
                                            $button_text = "Finalize";
                                        }
                                        ?>

                                        <?php if ($can_approve): ?>
                                            <button onclick="approveForm(<?php echo $form['id']; ?>)"
                                                class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1 w-full justify-center">
                                                <i class="fas fa-check"></i> <?php echo $button_text; ?>
                                            </button>

                                            <button onclick="rejectForm(<?php echo $form['id']; ?>)"
                                                class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1 w-full justify-center">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php else: ?>
                                            <?php if ($form['status'] == 'Approved'): ?>
                                                <span class="text-green-600 text-xs font-bold w-full text-left"><i
                                                        class="fas fa-check-circle"></i> Completed</span>

                                            <?php elseif ($form['status'] == 'Rejected'): ?>
                                                <div class="flex flex-col w-full">
                                                    <span class="text-red-400 text-xs font-bold text-left">Rejected</span>
                                                    <?php if (!empty($form['rejection_reason'])): ?>
                                                        <span class="text-red-400 text-[10px] italic mt-1 text-left" style="opacity: 0.8;">
                                                            reason: "<?php echo htmlspecialchars($form['rejection_reason']); ?>"
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                            <?php else: ?>
                                                <?php
                                                $waiting_for = match ($form['status']) {
                                                    'Pending' => 'Dept Head approval',
                                                    'Dept Head Approved' => 'Fac Head approval',
                                                    'Fac Head Approved' => 'Executive approval',
                                                    'Executive Approved' => 'Fac Head final approval',
                                                    default => 'approval'
                                                };
                                                ?>
                                                <span class="text-gray-400 text-xs italic w-full text-left">Waiting for
                                                    <?php echo $waiting_for; ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="py-4 flex justify-center border-t border-gray-100 flex-none bg-white px-2">
                    <div class="flex items-center space-x-2 md:space-x-2 select-none flex-wrap justify-center gap-y-2">

                        <?php if ($page > 1): ?>
                            <a href="?page=1"
                                class="h-8 w-8 flex items-center justify-center border border-gray-200 rounded-md text-gray-500 hover:bg-pink-50 hover:text-pink-600 hover:border-pink-200 transition-all shadow-sm flex-shrink-0"
                                title="First Page">
                                <i class="fas fa-angles-left"></i>
                            </a>
                        <?php else: ?>
                            <span
                                class="h-8 w-8 flex items-center justify-center border border-gray-100 rounded-md text-gray-300 cursor-not-allowed flex-shrink-0">
                                <i class="fas fa-angles-left"></i>
                            </span>
                        <?php endif; ?>

                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>"
                                class="h-8 px-2 md:px-3 flex items-center justify-center border border-gray-200 rounded-md text-xs md:text-sm font-medium text-gray-500 hover:bg-pink-50 hover:text-pink-600 hover:border-pink-200 transition-all shadow-sm gap-1 flex-shrink-0">
                                <i class="fas fa-angle-left"></i>
                                <span class="hidden sm:inline">Previous</span>
                            </a>
                        <?php else: ?>
                            <span
                                class="h-8 px-2 md:px-3 flex items-center justify-center border border-gray-100 rounded-md text-xs md:text-sm font-medium text-gray-300 cursor-not-allowed gap-1 flex-shrink-0">
                                <i class="fas fa-angle-left"></i>
                                <span class="hidden sm:inline">Previous</span>
                            </span>
                        <?php endif; ?>

                        <div class="flex items-center space-x-1 flex-shrink-0">
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>"
                                    class="h-8 w-8 flex items-center justify-center rounded-md text-xs md:text-sm font-bold transition-all border flex-shrink-0
                                   <?php echo $i == $page
                                       ? 'bg-pink-500 text-white border-pink-500 shadow-md'
                                       : 'bg-white border-gray-200 text-gray-600 hover:bg-pink-50 hover:text-pink-600 hover:border-pink-200'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>"
                                class="h-8 px-2 md:px-3 flex items-center justify-center border border-gray-200 rounded-md text-xs md:text-sm font-medium text-gray-500 hover:bg-pink-50 hover:text-pink-600 hover:border-pink-200 transition-all shadow-sm gap-1 flex-shrink-0">
                                <span class="hidden sm:inline">Next</span>
                                <i class="fas fa-angle-right"></i>
                            </a>
                        <?php else: ?>
                            <span
                                class="h-8 px-2 md:px-3 flex items-center justify-center border border-gray-100 rounded-md text-xs md:text-sm font-medium text-gray-300 cursor-not-allowed gap-1 flex-shrink-0">
                                <span class="hidden sm:inline">Next</span>
                                <i class="fas fa-angle-right"></i>
                            </span>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $total_pages; ?>"
                                class="h-8 w-8 flex items-center justify-center border border-gray-200 rounded-md text-gray-500 hover:bg-pink-50 hover:text-pink-600 hover:border-pink-200 transition-all shadow-sm flex-shrink-0"
                                title="Last Page">
                                <i class="fas fa-angles-right"></i>
                            </a>
                        <?php else: ?>
                            <span
                                class="h-8 w-8 flex items-center justify-center border border-gray-100 rounded-md text-gray-300 cursor-not-allowed flex-shrink-0">
                                <i class="fas fa-angles-right"></i>
                            </span>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        async function approveForm(id) {
            if (!confirm('Are you sure you want to approve this form?')) return;
            try {
                const res = await fetch('../api/approve_forms.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        form_ids: [id]
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Success!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('System Error: ' + e.message);
            }
        }

        async function rejectForm(id) {
            const reason = prompt('Are you sure you want to REJECT this request? (Optional: Enter a reason)');
            if (reason === null) return;
            try {
                const res = await fetch('../api/reject_form.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        form_ids: [id],
                        reason: reason
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Request Rejected.');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('System Error: ' + e.message);
            }
        }

        function filterTable() {
            const controlNo = document.getElementById('controlNoSearch').value.toLowerCase();
            // Check if elements exist before getting values to prevent errors
            const statusEl = document.getElementById('statusFilter');
            const deptEl = document.getElementById('deptFilter');
            const nameEl = document.getElementById('nameSearch');

            const status = statusEl ? statusEl.value : '';
            const dept = deptEl ? deptEl.value : '';
            const name = nameEl ? nameEl.value.toLowerCase() : '';

            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                // Ensure we have enough cells to avoid errors
                if (cells.length < 5) return;

                const control = cells[0].textContent.toLowerCase().trim();
                // ADDED .trim() HERE 
                const deptCell = cells[2].textContent.trim();
                const nameCell = cells[3].textContent.toLowerCase();
                const statusCell = cells[4].textContent.trim();

                // Logic: Show row if filter is empty OR if data matches filter
                const show = (!controlNo || control.includes(controlNo)) &&
                    (!status || statusCell === status) &&
                    (!dept || deptCell === dept) &&
                    (!name || nameCell.includes(name));

                row.style.display = show ? '' : 'none';
            });
        }
        document.getElementById('controlNoSearch').addEventListener('input', filterTable);
        document.getElementById('createdBySearch').addEventListener('input', filterTable);
        document.getElementById('deptFilter').addEventListener('change', filterTable);
    </script>
</body>

</html>