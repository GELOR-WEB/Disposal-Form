<div class="glass-sidebar fixed left-0 top-0 h-full w-64 p-6 flex flex-col z-50 overflow-y-auto">
    
    <div class="mb-8 flex items-center gap-3 px-2">
        <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-purple-400 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
            <i class="fas fa-spa text-xl text-white"></i>
        </div>
        
        <div class="text-left">
            <h2 class="text-lg font-bold text-gray-800 leading-tight">La Rose Noire</h2>
            <p class="text-xs text-gray-500 font-medium">Facilities Dept</p>
        </div>
    </div>

    <div class="glass-panel p-4 mb-6 text-center rounded-xl bg-white/50 border border-white/20 shadow-sm">
        <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-purple-400 rounded-full flex items-center justify-center mx-auto mb-2 text-white font-bold">
            <?php $name = $_SESSION['full_name'] ?? $_SESSION['fullname'] ?? 'User'; echo strtoupper(substr(trim($name), 0, 1));?>
        </div>
        <p class="font-bold text-gray-800 text-sm truncate"><?php echo $_SESSION['full_name'] ?? $_SESSION['fullname'] ?? 'User'; ?></p>
        
        <p class="text-xs text-pink-500 font-semibold uppercase">
            <?php echo $_SESSION['role'] ?? 'Staff'; ?>
        </p>
        <p class="text-xs text-gray-500 mt-1"><?php echo $_SESSION['dept'] ?? ''; ?></p>
    </div>

    <nav class="space-y-3 flex-1">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // --- LOGIC START ---
        // Get the role string and make it lowercase for easy checking
        // We combine both session variables to be absolutely sure we catch the role
        $my_role = strtolower($_SESSION['role'] ?? '');

        // Define who counts as a "Supervisor/Admin"
        // If the role contains any of these words, they are an Admin
        $is_admin_level = (
            strpos($my_role, 'admin') !== false || 
            strpos($my_role, 'supervisor') !== false || 
            strpos($my_role, 'manager') !== false || 
            strpos($my_role, 'leader') !== false
        );
        // --- LOGIC END ---
        ?>

        <?php if (!$is_admin_level && $_SESSION['role'] === 'Employee'): ?>
            <a href="staff_entry.php" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'staff_entry.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Create Form</span>
            </a>
        <?php endif; ?>

        <?php if (!$is_admin_level && $_SESSION['role'] === 'Employee'): ?>
        <a href="view_forms.php" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'view_forms.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
            <i class="fas fa-list"></i>
            <span>View Forms</span>
        </a>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'Executive' || $_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Department Head'): ?>
            <a href="supervisor_dashboard.php" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'supervisor_dashboard.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
                <i class="fas fa-check-double"></i>
                <span>Approvals</span>
            </a>

            <a href="reports.php" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'reports.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
                <i class="fas fa-chart-pie"></i>
                <span>Reports</span>
            </a>
        <?php endif; ?>

        <hr class="border-gray-200 my-2">

        <a href="/portal-lrn/portal.php" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50">
            <i class="fas fa-door-open"></i>
            <span>Return to Portal</span>
        </a>

        <a href="../assets/docs/manual.pdf" target="_blank" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50">
            <i class="fas fa-question-circle"></i>
            <span>Help / Guide</span>
        </a>
    </nav>

    <a href="../auth/logout.php" class="flex items-center justify-center gap-2 mt-4 p-3 text-red-500 hover:bg-red-50 rounded-xl transition-all font-semibold">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>

    <div class="mt-6">
        <img src="../assets/images/footer.png" alt="Footer Logo" class="w-full opacity-90 rounded-lg">
    </div>
</div>