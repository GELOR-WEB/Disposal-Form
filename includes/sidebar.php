<style>
    /* Sidebar responsive styles */
    #app-sidebar {
        overflow-y: auto;
    }

    /* Prevent scrolling on tablets and larger screens */
    @media (min-width: 769px) {
        #app-sidebar {
            overflow-y: hidden;
        }
    }

    /* For very small tablets in portrait, keep scrolling */
    @media (max-width: 768px) {
        #app-sidebar {
            overflow-y: auto;
        }
    }

    /* Footer image display rules */
    #sidebar-footer {
        display: none;
    }

    /* Show footer on tablets in landscape orientation */
    @media (min-width: 769px) and (orientation: landscape) {
        #sidebar-footer {
            margin-top: 0px !important;
            display: block;
            /* Note: height: 100vh; on the container might be too tall, but I left it as is per your config */
            height: 100vh;
        }

        /* UPDATED: Apply the 10vh height ONLY here */
        #sidebar-footer img {
            height: 10vh;
            object-fit: cover;
            /* Ensures image doesn't look squashed */
        }
    }

    /* Show footer on desktop (always landscape) */
    @media (min-width: 1441px) {
        #sidebar-footer {
            display: block;
            margin-top: 1.5rem;
            /* Restore margin for desktop if needed */
            height: auto;
            /* Reset container height for desktop */
        }

        /* UPDATED: Let desktop height be natural (auto) */
        #sidebar-footer img {
            height: auto;
            max-height: 120px;
            /* Optional: Prevents it from getting too huge on big screens */
        }
    }

    /* Minimize buttons on tablets */
    @media (min-width: 769px) and (max-width: 1440px) {
        .nav-item {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
            gap: 0.5rem !important;
        }

        .nav-item i {
            font-size: 0.875rem;
        }
    }
</style>

<script>
    function handleImgError(img, empId) {
        // Debugging: Log what's happening
        console.log('Image failed to load:', img.src);

        const currentSrc = img.src.toLowerCase();
        // Ensure absolute path matching or just check extension
        // Using split to safely get extension ignoring query params if any

        const baseUrl = 'http://10.2.0.8/lrnph/emp_photos/' + empId;

        // Force sequence: jpg -> jpeg -> png -> gif -> hide
        if (currentSrc.includes('.jpg')) {
            console.log('Trying .jpeg');
            img.src = baseUrl + '.jpeg';
        } else if (currentSrc.includes('.jpeg')) {
            console.log('Trying .png');
            img.src = baseUrl + '.png';
        } else if (currentSrc.includes('.png')) {
            console.log('Trying .gif');
            img.src = baseUrl + '.gif';
        } else {
            console.log('All formats failed, hiding image.');
            img.style.display = 'none';
        }
    }
</script>

<div id="app-sidebar" class="glass-sidebar fixed left-0 top-0 h-full w-64 p-6 flex flex-col z-50">

    <div class="mb-8 flex items-center gap-3 px-2">
        <div
            class="w-12 h-12 bg-gradient-to-br from-pink-400 to-purple-400 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
            <i class="fas fa-spa text-xl text-white"></i>
        </div>

        <div class="text-left">
            <h2 class="text-lg font-bold text-gray-800 leading-tight">La Rose Noire</h2>
            <p class="text-xs text-gray-500 font-medium">Facilities Dept</p>
        </div>
    </div>

    <div class="glass-panel p-4 mb-6 text-center rounded-xl bg-white/50 border border-white/20 shadow-sm">
        <div
            class="w-10 h-10 bg-gradient-to-br from-pink-400 to-purple-400 rounded-full flex items-center justify-center mx-auto mb-2 text-white font-bold relative overflow-hidden">
            <!-- Initials (Fallback) -->
            <?php
            $name = $_SESSION['full_name'] ?? $_SESSION['fullname'] ?? 'User';
            $empId = $_SESSION['user_id'] ?? $_SESSION['empcode'] ?? '';
            echo strtoupper(substr(trim($name), 0, 1));
            ?>

            <!-- Profile Image (Overlay) -->
            <img src="http://10.2.0.8/lrnph/emp_photos/<?php echo $empId; ?>.jpg" alt="Profile"
                class="w-full h-full object-cover absolute top-0 left-0"
                onerror="handleImgError(this, '<?php echo $empId; ?>')">
        </div>
        <p class="font-bold text-gray-800 text-sm truncate">
            <?php echo $_SESSION['full_name'] ?? $_SESSION['fullname'] ?? 'User'; ?>
        </p>

        <p class="text-xs text-pink-500 font-semibold uppercase">
            <?php echo $_SESSION['role'] ?? 'Staff'; ?>
        </p>
        <p class="text-xs text-gray-500 mt-1"><?php echo $_SESSION['dept'] ?? ''; ?></p>
    </div>

    <nav class="space-y-3 flex-1">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);

        $my_role = strtolower($_SESSION['role'] ?? '');

        $is_admin_level = (
            strpos($my_role, 'admin') !== false ||
            strpos($my_role, 'supervisor') !== false ||
            strpos($my_role, 'manager') !== false ||
            strpos($my_role, 'leader') !== false
        );
        ?>

        <?php if (!$is_admin_level && $_SESSION['role'] === 'Employee'): ?>
            <a href="staff_entry.php"
                class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'staff_entry.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Create Form</span>
            </a>
        <?php endif; ?>

        <?php if (!$is_admin_level && $_SESSION['role'] === 'Employee'): ?>
            <a href="view_forms.php"
                class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'view_forms.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
                <i class="fas fa-list"></i>
                <span>View Forms</span>
            </a>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'Executive' || $_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Department Head' || $_SESSION['role'] === 'Facilities Head'): ?>
            <a href="supervisor_dashboard.php"
                class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'supervisor_dashboard.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
                <i class="fas fa-check-double"></i>
                <span>Approvals</span>
            </a>

            <a href="reports.php"
                class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'reports.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
                <i class="fas fa-chart-pie"></i>
                <span>Reports</span>
            </a>
        <?php endif; ?>

        <hr class="border-gray-200 my-2">

        <a href="/portal-lrn/portal.php"
            class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50">
            <i class="fas fa-door-open"></i>
            <span>Return to Portal</span>
        </a>

        <?php
        $manual_link = '../assets/docs/employee_manual.pdf';
        $r = $_SESSION['role'] ?? '';
        if ($r === 'Executive' || $r === 'Admin' || $r === 'Department Head' || $r === 'Facilities Head') {
            $manual_link = '../assets/docs/admin_manual.pdf';
        }
        ?>

        <a href="<?php echo $manual_link; ?>" target="_blank"
            class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50">
            <i class="fas fa-question-circle"></i>
            <span>Help / Guide</span>
        </a>
    </nav>

    <a href="../auth/logout.php"
        class="flex items-center justify-center gap-2 mt-4 p-3 text-red-500 hover:bg-red-50 rounded-xl transition-all font-semibold">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>

    <div id="sidebar-footer" class="mt-6">
        <img src="../assets/images/footer.png" alt="Footer Logo" class="w-full opacity-90 rounded-lg">
    </div>
</div>