<div class="glass-sidebar fixed left-0 top-0 h-full w-64 p-6 flex flex-col z-50">
    <div class="mb-8 text-center">
        <div class="w-16 h-16 bg-gradient-to-br from-pink-400 to-purple-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <i class="fas fa-spa text-3xl text-white"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800">La Rose Noire</h2>
        <p class="text-sm text-gray-500">Facilities Dept</p>
    </div>

    <div class="glass-panel p-4 mb-6 text-center rounded-xl bg-white/50">
        <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-purple-400 rounded-full flex items-center justify-center mx-auto mb-2 text-white font-bold">
            <?php echo substr($_SESSION['full_name'] ?? 'U', 0, 1); ?>
        </div>
        <p class="font-bold text-gray-800 text-sm"><?php echo $_SESSION['full_name'] ?? 'User'; ?></p>
        <p class="text-xs text-pink-500 font-semibold"><?php echo $_SESSION['job_level'] ?? 'Staff'; ?></p>
    </div>

    <nav class="space-y-3 flex-1 overflow-y-auto">
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']);
        $is_supervisor = (isset($_SESSION['job_level']) && ($_SESSION['job_level'] == 'Supervisor' || $_SESSION['job_level'] == 'Team Leader'));
        ?>

        <?php if(!$is_supervisor): ?>
        <a href="../pages/staff_entry.php" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'staff_entry.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
            <i class="fas fa-plus-circle"></i>
            <span>Create Form</span>
        </a>
        <?php endif; ?>

        <a href="../pages/view_forms.php" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'view_forms.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
            <i class="fas fa-list"></i>
            <span>View Forms</span>
        </a>

        <?php if($is_supervisor): ?>
        <a href="../pages/supervisor_dashboard.php" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'supervisor_dashboard.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
            <i class="fas fa-check-double"></i>
            <span>Approvals</span>
        </a>

        <a href="../pages/reports.php" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'reports.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Reports</span>
        </a>
        <?php endif; ?>

        <hr class="border-gray-200 my-2">

        <a href="http://localhost/portal_placeholder" class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50">
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
</div>