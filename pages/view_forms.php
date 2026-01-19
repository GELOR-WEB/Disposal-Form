<?php
session_start();

// Redirect Logic:
// Executives, Admins, and Facilities Heads should land on the Dashboard.
// Department Heads and Employees stay here to view the list.
if (isset($_SESSION['role'])) {
    $r = $_SESSION['role'];
    if ($r === 'Executive' || $r === 'Admin' || $r === 'Facilities Head' || strpos(strtolower($r), 'admin') !== false) {
        header("Location: supervisor_dashboard.php");
        exit();
    }
}

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../db/conn.php';

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $per_page;

// Get filter parameters
$dept_filter = isset($_GET['dept']) ? trim($_GET['dept']) : '';

// ---------------------------------------------------------
// ENFORCE DEPARTMENT FILTERING FOR EMPLOYEES
// ---------------------------------------------------------
// If the user reaches this page and is NOT an Admin, Executive, or Facilities Head,
// they should only see their own department's forms.
// Department Heads are usually redirected to supervisor_dashboard, but if they land here,
// this logic also applies (which is consistent).
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job = strtolower($_SESSION['job_level'] ?? '');
$full_role = $my_role . ' ' . $my_job;

$is_privileged = (
    strpos($full_role, 'admin') !== false ||
    strpos($full_role, 'facilities head') !== false ||
    strpos($full_role, 'executive') !== false ||
    $my_role === 'facilities head' ||
    $my_role === 'executive'
);

if (!$is_privileged) {
    // Force the department filter to their own SCIPED department (defined in user_roles.php)
    // If we have a scoped department, use it. Otherwise fallback to the DB one.
    $scoped = $_SESSION['scoping_dept'] ?? '';
    if (!empty($scoped)) {
        $dept_filter = $scoped;
    } else {
        $dept_filter = $_SESSION['department'] ?? '';
    }
}
// ---------------------------------------------------------

$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$name_filter = isset($_GET['name']) ? trim($_GET['name']) : '';
$control_filter = isset($_GET['control']) ? trim($_GET['control']) : '';
$sort_order = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'ASC' : 'DESC';

// Build WHERE clause based on filters
$where_conditions = [];
$params = [];

if (!empty($dept_filter)) {
    $where_conditions[] = "department = :dept";
    $params[':dept'] = $dept_filter;
}

if (!empty($status_filter)) {
    // Map display filters to database values
    $db_status = $status_filter;
    if ($status_filter === 'Completed') {
        $db_status = 'Approved';
    } elseif ($status_filter === 'Department Head') {
        $db_status = 'Dept Head Approved';
    } elseif ($status_filter === 'Facilities Head') {
        $db_status = 'Fac Head Approved'; // Primary DB status
        // Note: If DB uses 'Admin Approved', we might need OR logic in query or just map here if strict 1:1
        // But for filter params, usually exact match. Let's try 'Fac Head Approved' based on dashboard code.
        // If legacy data uses 'Admin Approved', we might miss it. 
        // Let's stick to what the user effectively asked for: Dept->Dept, Fac->Fac.
    }

    $where_conditions[] = "status = :status";
    $params[':status'] = $db_status;
}

if (!empty($name_filter)) {
    $where_conditions[] = "full_name LIKE :name";
    $params[':name'] = '%' . $name_filter . '%';
}

if (!empty($control_filter)) {
    $where_conditions[] = "id LIKE :control";
    $params[':control'] = '%' . $control_filter . '%';
}

$where_clause = count($where_conditions) > 0 ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination (with filters applied)
try {
    $total_sql = "SELECT COUNT(*) as total FROM dsp_forms $where_clause";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->execute($params);
    $total_forms = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_forms / $per_page);
} catch (PDOException $e) {
    $total_forms = 0;
    $total_pages = 1;
}

// Get all departments for filter dropdown
try {
    $dept_sql = "SELECT DISTINCT department FROM dsp_forms WHERE department IS NOT NULL AND department != '' ORDER BY department";
    $dept_stmt = $conn->query($dept_sql);
    $all_depts = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $all_depts = [];
}

// Get paginated forms with filters
try {
    $sql = "SELECT * FROM dsp_forms $where_clause ORDER BY created_date $sort_order OFFSET $offset ROWS FETCH NEXT $per_page ROWS ONLY";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $forms = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
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
        /* Responsive body layout */
        body {
            padding-left: 280px;
            padding-top: 20px;
            padding-right: 20px;
            overflow-y: hidden;
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

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            min-width: 120px;
            display: inline-block;
            white-space: nowrap;
        }

        /* Status Colors */
        .status-Pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-Approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-Rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-Completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-AdminApproved {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-DeptHeadApproved {
            background: #ede9fe;
            color: #7c3aed;
        }

        .status-ExecutiveApproved {
            background: #fed7aa;
            color: #9a3412;
        }

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

        /* 2. Flexbox Table Container */
        .table-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            width: 100%;
            min-height: 0;
        }

        .table-container::-webkit-scrollbar {
            width: 10px;
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

        /* Tablet responsive adjustments (Landscape) */
        @media (min-width: 769px) and (max-width: 1440px) {

            /* Minimize filter inputs */
            .form-input {
                padding: 0.5rem 0.75rem !important;
                font-size: 0.875rem !important;
                width: 140px !important;
                /* Compact width for tablets */
            }

            /* Even smaller width for status filter */
            #statusFilter {
                width: 110px !important;
            }

            /* Minimize buttons */
            .btn {
                padding: 0.5rem 1rem !important;
                font-size: 0.875rem !important;
            }

            /* Adjust card margin to prevent pagination cutoff */
            .card {
                margin-bottom: 5rem !important;
            }
        }

        /* Portrait Tablet Responsive (768px - 1024px in portrait orientation) */
        @media (max-width: 1024px) and (orientation: portrait) {
            body {
                padding-left: 240px !important;
                padding-right: 15px !important;
                padding-top: 15px !important;
            }

            /* Header adjustments */
            .glass-panel h1 {
                font-size: 1.5rem !important;
            }

            .glass-panel p {
                font-size: 0.875rem !important;
            }

            /* Filter bar - stack in pairs */
            .mb-4.flex {
                gap: 0.5rem !important;
            }

            .form-input {
                padding: 0.5rem 0.65rem !important;
                font-size: 0.8rem !important;
                width: calc(50% - 0.25rem) !important;
                min-width: 120px !important;
            }

            #controlNoSearch {
                width: calc(50% - 0.25rem) !important;
                padding-left: 28px !important;
            }

            #deptFilter,
            #nameSearch,
            #statusFilter {
                width: calc(50% - 0.25rem) !important;
            }

            .btn {
                padding: 0.5rem 0.75rem !important;
                font-size: 0.8rem !important;
            }

            /* Table adjustments */
            table {
                font-size: 0.75rem !important;
            }

            table th {
                padding: 0.5rem 0.35rem !important;
                font-size: 0.65rem !important;
            }

            table td {
                padding: 0.5rem 0.35rem !important;
                font-size: 0.75rem !important;
            }

            .status-badge {
                padding: 4px 8px !important;
                font-size: 0.65rem !important;
                min-width: 90px !important;
            }

            /* Pagination adjustments */
            .py-4.flex.justify-center {
                padding: 0.75rem 0.5rem !important;
            }

            .h-8 {
                height: 32px !important;
            }

            .w-8 {
                width: 32px !important;
            }

            /* Card spacing */
            .card {
                margin-bottom: 4rem !important;
            }
        }

        /* Phone Portrait Responsive (max-width: 767px) */
        @media (max-width: 767px) {
            body {
                padding-left: 10px !important;
                padding-right: 10px !important;
                padding-top: 10px !important;
            }

            /* Header - more compact */
            .glass-panel {
                padding: 1rem !important;
                margin-bottom: 0.5rem !important;
            }

            .glass-panel h1 {
                font-size: 1.25rem !important;
                margin: 0 !important;
            }

            .glass-panel p {
                font-size: 0.75rem !important;
                margin: 0 !important;
            }

            /* HORIZONTAL SCROLLABLE FILTERS (Carousel) */
            .mb-4.flex.flex-wrap.gap-4 {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                gap: 10px !important;
                margin-bottom: 15px !important;
                padding-bottom: 5px !important;
                /* Space for scrollbar interaction */
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                /* Firefox */
            }

            .mb-4.flex.flex-wrap.gap-4::-webkit-scrollbar {
                display: none;
                /* Chrome/Safari - hide scrollbar for clean look */
            }

            /* Set fixed widths for direct children to prevent squishing */
            .mb-4.flex.flex-wrap.gap-4>* {
                flex: 0 0 auto !important;
            }

            /* 1. Control Search */
            .mb-4.flex.flex-wrap.gap-4>div:first-child {
                width: 200px !important;
                grid-column: auto !important;
                /* Reset grid */
            }

            #controlNoSearch {
                width: 100% !important;
                padding: 0.5rem 0.75rem 0.5rem 2rem !important;
                height: 40px !important;
                background: white !important;
                border-radius: 12px !important;
                /* Match rounded look */
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            .mb-4.flex>div:first-child i {
                top: 50% !important;
                transform: translateY(-50%) !important;
            }

            /* 2. Date Toggle */
            #sortDateToggle {
                width: auto !important;
                min-width: 80px !important;
                height: 40px !important;
                padding: 0 15px !important;
                display: flex !important;
                align-items: center !important;
                grid-column: auto !important;
                background: white !important;
                border-radius: 12px !important;
                border: 1px solid #e5e7eb !important;
            }

            /* 3. Inputs & Selects */
            #deptFilter,
            #nameSearch,
            #statusFilter {
                width: 160px !important;
                /* Consistent width */
                height: 40px !important;
                min-height: 40px !important;
                padding: 0 0.75rem !important;
                font-size: 0.85rem !important;
                border-radius: 12px !important;
                grid-column: auto !important;
                display: block !important;
                /* Ensure visible */
                background: white !important;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            /* Table container */
            .table-container {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
                margin-top: 0.5rem !important;
            }

            /* Table Compactness */
            table {
                min-width: 600px !important;
                font-size: 0.7rem !important;
            }

            table th,
            table td {
                padding: 0.5rem 0.25rem !important;
            }

            /* Status Badges */
            .status-badge {
                padding: 2px 6px !important;
                font-size: 0.6rem !important;
                min-width: 60px !important;
            }

            /* Card spacing */
            .card {
                margin-bottom: 5rem !important;
                /* Space for bottom nav */
                padding: 1rem !important;
                border-radius: 16px !important;
            }

            /* Pagination */
            .py-4.flex.justify-center {
                padding: 0.5rem !important;
                flex-wrap: wrap;
            }

            /* Fix body height for mobile browser chrome */
            .h-\[calc\(100vh-40px\)\] {
                height: auto !important;
                min-height: 100vh !important;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="animate-fade-in max-w-7xl mx-auto h-[calc(100vh-40px)] flex flex-col">

        <div class="flex-none">
            <div class="glass-panel p-6 mb-8 flex justify-between items-center" style="border-radius: 16px;">
                <div>
                    <h1 style="margin:0; font-size: 1.8rem;">Submitted Disposals</h1>
                    <p style="margin:0; color:#6b7280;">Manage and track facility disposal requests</p>
                </div>
            </div>

            <div class="mb-4 flex flex-wrap gap-4">
                <div style="position: relative;">
                    <i class="fas fa-search"
                        style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                    <input type="text" id="controlNoSearch" class="form-input" placeholder="Search Control No..."
                        style="padding-left: 30px; width: 200px;"
                        value="<?php echo htmlspecialchars($control_filter); ?>">
                </div>
                <button id="sortDateToggle" class="btn" title="Toggle date sort">Date ↕</button>
                <select id="deptFilter" class="form-input" style="width: 200px;" <?php echo (!$is_privileged) ? 'disabled' : ''; ?>>
                    <?php if (!$is_privileged): ?>
                        <option value="<?php echo htmlspecialchars($dept_filter); ?>" selected>
                            <?php echo htmlspecialchars($dept_filter); ?>
                        </option>
                    <?php else: ?>
                        <option value="">All Departments</option>
                        <?php
                        foreach ($all_depts as $dept) {
                            $selected = ($dept === $dept_filter) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($dept) . "\" $selected>" . htmlspecialchars($dept) . "</option>";
                        }
                        ?>
                    <?php endif; ?>
                </select>
                <input type="text" id="nameSearch" placeholder="Search Name..." class="form-input" style="width: 200px;"
                    value="<?php echo htmlspecialchars($name_filter); ?>">
                <select id="statusFilter" class="form-input" style="width: 150px;">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Department Head" <?php echo $status_filter === 'Department Head' ? 'selected' : ''; ?>>
                        Dept. Head Approved</option>
                    <option value="Facilities Head" <?php echo $status_filter === 'Facilities Head' ? 'selected' : ''; ?>>
                        Fac. Head Approved</option>
                    <option value="Executive Approved" <?php echo $status_filter === 'Executive Approved' ? 'selected' : ''; ?>>Executive Approved</option>
                    <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed
                    </option>
                    <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected
                    </option>
                </select>
            </div>
        </div>

        <div
            class="card p-0 flex-1 flex flex-col min-h-0 overflow-hidden shadow-lg border border-gray-100 bg-white rounded-2xl mb-4">

            <div class="table-container">
                <table class="w-full" style="border-collapse: collapse; min-width: 800px;">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[10%]">Control
                                No.</th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[17%]">Date
                                Created</th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[23%]">
                                Department</th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[22%]">Created
                                By</th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[16%]">Status
                            </th>
                            <th class="text-center py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[12%]">Details
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (count($forms) > 0): ?>
                            <?php foreach ($forms as $form): ?>
                                <tr class="hover:bg-gray-50 transition-colors"
                                    data-date="<?php echo strtotime($form['created_date']); ?>">
                                    <td class="py-4 px-4 font-mono text-pink-600 font-bold text-sm text-center w-[10%]">
                                        CN-<?php echo date('Y', strtotime($form['created_date'])); ?>-<?php echo str_pad($form['id'], 4, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600 text-sm text-center w-[17%]">
                                        <?php echo date('M d, Y h:i A', strtotime($form['created_date'])); ?>
                                    </td>
                                    <td class="py-4 px-4 text-gray-800 font-medium text-sm text-center w-[23%]">
                                        <?php echo $form['department']; ?>
                                    </td>
                                    <td class="py-4 px-4 w-[22%]">
                                        <div class="flex items-center justify-center gap-2">
                                            <div
                                                class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs text-gray-500">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span
                                                class="text-sm font-medium text-gray-700"><?php echo $form['full_name']; ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center w-[16%]">
                                        <?php
                                        $statusClass = match ($form['status']) {
                                            'Approved' => 'status-Completed',
                                            'Rejected' => 'status-Rejected',
                                            'Admin Approved', 'Fac Head Approved' => 'status-AdminApproved',
                                            'Dept Head Approved' => 'status-DeptHeadApproved',
                                            'Executive Approved' => 'status-ExecutiveApproved',
                                            default => 'status-Pending'
                                        };
                                        // Display Logic Mapping
                                        $displayStatus = $form['status'];
                                        if ($form['status'] == 'Approved')
                                            $displayStatus = 'Completed';
                                        elseif ($form['status'] == 'Dept Head Approved')
                                            $displayStatus = 'Dept. Head Approved';
                                        elseif ($form['status'] == 'Admin Approved' || $form['status'] == 'Fac Head Approved')
                                            $displayStatus = 'Fac. Head Approved';
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo $displayStatus; ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center w-[12%]">
                                        <a href="view_details.php?id=<?php echo $form['id']; ?>"
                                            class="bg-white border border-gray-200 text-gray-600 hover:text-pink-600 hover:border-pink-300 rounded-md text-[13px] font-bold transition-all shadow-sm px-3 py-1 whitespace-nowrap">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-400">No forms found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
            // Build query string for pagination links with filters
            $query_params = [];
            if (!empty($dept_filter))
                $query_params['dept'] = $dept_filter;
            if (!empty($status_filter))
                $query_params['status'] = $status_filter;
            if (!empty($name_filter))
                $query_params['name'] = $name_filter;
            if (!empty($control_filter))
                $query_params['control'] = $control_filter;
            if (isset($_GET['sort']))
                $query_params['sort'] = $_GET['sort'];

            function build_page_url($page_num, $params)
            {
                $params['page'] = $page_num;
                return '?' . http_build_query($params);
            }
            ?>
            <?php if ($total_pages > 1): ?>
                <div class="py-4 flex justify-center border-t border-gray-100 flex-none bg-white px-2">
                    <div class="flex items-center space-x-2 md:space-x-2 select-none flex-wrap justify-center gap-y-2">

                        <?php if ($page > 1): ?>
                            <a href="<?php echo build_page_url(1, $query_params); ?>"
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
                            <a href="<?php echo build_page_url($page - 1, $query_params); ?>"
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
                                <a href="<?php echo build_page_url($i, $query_params); ?>"
                                    class="h-8 w-8 flex items-center justify-center rounded-md text-xs md:text-sm font-bold transition-all border flex-shrink-0
                                   <?php echo $i == $page
                                       ? 'bg-pink-500 text-white border-pink-500 shadow-md'
                                       : 'bg-white border-gray-200 text-gray-600 hover:bg-pink-50 hover:text-pink-600 hover:border-pink-200'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo build_page_url($page + 1, $query_params); ?>"
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
                            <a href="<?php echo build_page_url($total_pages, $query_params); ?>"
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
        function applyFilters() {
            const params = new URLSearchParams();

            const controlNo = document.getElementById('controlNoSearch').value.trim();
            const status = document.getElementById('statusFilter').value;
            const dept = document.getElementById('deptFilter').value;
            const name = document.getElementById('nameSearch').value.trim();

            if (controlNo) params.set('control', controlNo);
            if (status) params.set('status', status);
            if (dept) params.set('dept', dept);
            if (name) params.set('name', name);

            // Reset to page 1 when filtering
            params.set('page', '1');

            window.location.href = '?' + params.toString();
        }

        // Debounce function for text inputs
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        const debouncedFilter = debounce(applyFilters, 500);

        document.getElementById('controlNoSearch').addEventListener('input', debouncedFilter);
        document.getElementById('statusFilter').addEventListener('change', applyFilters);
        document.getElementById('deptFilter').addEventListener('change', applyFilters);
        document.getElementById('nameSearch').addEventListener('input', debouncedFilter);

        // Toggle date sort button
        const urlParams = new URLSearchParams(window.location.search);
        let sortAscending = urlParams.get('sort') === 'asc';

        document.getElementById('sortDateToggle').addEventListener('click', function () {
            sortAscending = !sortAscending;

            const params = new URLSearchParams(window.location.search);
            params.set('sort', sortAscending ? 'asc' : 'desc');
            params.set('page', '1'); // Reset to page 1 when sorting

            window.location.href = '?' + params.toString();
        });
    </script>
</body>

</html>