<style>
    /* Sidebar responsive styles - Modern fluid design */
    #app-sidebar {
        /* Disable scrolling for cleaner mobile/tablet experience */
        overflow-y: hidden;
        /* Disable horizontal scrolling */
        overflow-x: hidden;
        /* Ensure it takes full viewport height */
        max-height: 100vh;
        height: 100vh;
        /* FORCE FIXED POSITIONING ON ALL DEVICES */
        position: fixed !important;
        /* Ensure it stays on top */
        z-index: 50;
    }

    /* Footer image display rules - Fluid visibility */
    #sidebar-footer {
        /* Hide footer on mobile to save space */
        display: none;
        margin-top: clamp(0px, 1vw, 1rem);
        height: auto;
    }

    /* Show footer on larger viewports (tablets landscape and desktop) */
    #sidebar-footer {
        display: block;
    }

    /* Fluid image sizing - more compact */
    #sidebar-footer img {
        height: clamp(auto, 8vh, 100px);
        max-height: 100px;
        object-fit: cover;
    }

    /* Navigation buttons - Compact fluid sizing for tablets/phones */
    .nav-item {
        padding: clamp(0.4rem, 0.5rem + 0.25vw, 0.75rem) clamp(0.6rem, 0.75rem + 0.25vw, 1rem) !important;
        font-size: clamp(0.8rem, 0.85rem + 0.05vw, 0.9rem) !important;
        gap: clamp(0.4rem, 0.5vw, 0.6rem) !important;
    }

    .nav-item i {
        font-size: clamp(0.8rem, 0.85rem + 0.05vw, 0.9rem);
    }

    /* =========================================
       1. MOBILE PORTRAIT (Phones) - Bottom Navigation
       ========================================= */
    @media only screen and (max-width: 640px) and (orientation: portrait) {
        #app-sidebar {
            width: 100% !important;
            height: auto !important;
            min-height: 4rem !important;
            bottom: 0 !important;
            top: auto !important;
            left: 0 !important;
            right: 0 !important;
            padding: 0 !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            border-right: none !important;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px 20px 0 0 !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            z-index: 9999 !important;
            overflow-y: hidden !important;
        }

        /* Hide elements */
        .mb-8,
        #sidebar-footer,
        .text-left,
        .nav-item span,
        .glass-panel.p-4 {
            display: none !important;
        }

        /* FORCE HORIZONTAL LAYOUT on the NAV wrapper */
        #app-sidebar nav {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-around !important;
            width: 100% !important;
            flex: 1 !important;
            margin: 0 !important;
        }

        /* Reset vertical spacing */
        #app-sidebar nav>* {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        /* Hide Divider */
        #app-sidebar nav hr {
            display: none !important;
        }

        /* Nav Items */
        .nav-item {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 0.75rem !important;
            width: auto !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .nav-item i {
            margin: 0 !important;
            font-size: 1.5rem !important;
            color: #db2777 !important;
        }

        /* Logout Icon integration */
        #app-sidebar>a[href*="logout"] {
            margin: 0 !important;
            padding: 0.75rem !important;
            display: flex !important;
            align-items: center !important;
        }

        #app-sidebar>a[href*="logout"] span {
            display: none !important;
        }

        #app-sidebar>a[href*="logout"] i {
            font-size: 1.5rem !important;
            color: #ef4444 !important;
        }
    }

    /* =========================================
       2. MOBILE LANDSCAPE (Phones rotated)
       ========================================= */
    /* =========================================
       2. MOBILE LANDSCAPE (Phones rotated) - Bottom Navigation
       ========================================= */
    @media only screen and (max-width: 932px) and (orientation: landscape) {
        #app-sidebar {
            width: 100% !important;
            height: auto !important;
            min-height: 3rem !important;
            /* Slightly more compact */
            bottom: 0 !important;
            top: auto !important;
            left: 0 !important;
            right: 0 !important;
            padding: 0 !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            border-right: none !important;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px 15px 0 0 !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            z-index: 9999 !important;
            overflow-y: hidden !important;
        }

        /* Hide elements */
        .mb-8,
        #sidebar-footer,
        .text-left,
        .nav-item span,
        .glass-panel.p-4 {
            display: none !important;
        }

        /* FORCE HORIZONTAL LAYOUT on the NAV wrapper */
        #app-sidebar nav {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-around !important;
            width: 100% !important;
            flex: 1 !important;
            margin: 0 !important;
        }

        #app-sidebar nav>* {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        #app-sidebar nav hr {
            display: none !important;
        }

        /* Nav Items */
        .nav-item {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 0.25rem !important;
            width: auto !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .nav-item i {
            margin: 0 !important;
            font-size: 1.25rem !important;
            color: #db2777 !important;
        }

        /* Logout Icon integration */
        #app-sidebar>a[href*="logout"] {
            margin: 0 !important;
            padding: 0.5rem !important;
            display: flex !important;
            align-items: center !important;
        }

        #app-sidebar>a[href*="logout"] span {
            display: none !important;
        }

        #app-sidebar>a[href*="logout"] i {
            font-size: 1.25rem !important;
            color: #ef4444 !important;
        }
    }

    /* =========================================
       3. TABLET PORTRAIT (iPad, High-Res 2136px width)
       ========================================= */
    @media only screen and (min-width: 768px) and (max-width: 2136px) and (orientation: portrait) {
        #app-sidebar {
            width: 14rem !important;
            /* Compact but text visible */
            padding: 1.5rem 1rem !important;
        }

        .nav-item {
            padding: 0.75rem 1rem !important;
            font-size: 0.85rem !important;
        }

        .mb-8 {
            padding-left: 0.5rem !important;
        }

        /* Keep text visible but smaller */
        h2.text-lg {
            font-size: 1rem !important;
        }

        .text-xs {
            font-size: 0.7rem !important;
        }
    }

    /* =========================================
       4. TABLET LANDSCAPE (High-Res 3200px width)
       ========================================= */
    @media only screen and (min-width: 768px) and (max-width: 3200px) and (orientation: landscape) {
        #app-sidebar {
            width: 16rem !important;
            /* Full width */
            padding: 1.5rem !important;
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

        $my_primary_role = strtolower($_SESSION['role'] ?? '');
        $my_roles = $_SESSION['roles'] ?? [$my_primary_role]; // Fallback to single if array missing
        
        // Helper function to check if user has any of the given roles
        if (!function_exists('has_role')) {
            function has_role($roles_to_check, $user_roles)
            {
                // normalize user roles to lower case
                $normalized_user_roles = array_map('strtolower', $user_roles);

                if (!is_array($roles_to_check)) {
                    $roles_to_check = [$roles_to_check];
                }

                foreach ($roles_to_check as $check) {
                    $check = strtolower($check);
                    // Handle partial matches for Admin/Supervisor/Manager if needed, 
                    // but usually direct role match is safer unless we want string contains.
                    // The original code used strpos for 'admin' etc.
        
                    foreach ($normalized_user_roles as $ur) {
                        if ($ur === $check)
                            return true;
                        // Keep the substring check for 'admin'/'supervisor' logic from before
                        if (($check === 'admin' || $check === 'supervisor') && strpos($ur, $check) !== false)
                            return true;
                    }
                }
                return false;
            }
        }

        $is_employee = has_role('Employee', $my_roles);
        $is_approver = has_role(['Department Head', 'Facilities Head', 'Executive', 'Admin'], $my_roles);
        ?>

        <?php if ($is_employee): ?>
            <a href="staff_entry.php"
                class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'staff_entry.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Create Form</span>
            </a>
        <?php endif; ?>

        <?php if ($is_employee && !$is_approver): ?>
            <a href="view_forms.php"
                class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50 <?php echo ($current_page == 'view_forms.php') ? 'bg-pink-50 text-pink-600 font-bold shadow-sm' : ''; ?>">
                <i class="fas fa-list"></i>
                <span>View Forms</span>
            </a>
        <?php endif; ?>

        <?php if ($is_approver): ?>
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

        <a href="../portal-lrn-folder/portal.php"
            class="nav-item flex items-center gap-3 px-4 py-3 text-gray-600 hover:text-pink-600 rounded-xl transition-all hover:bg-pink-50">
            <i class="fas fa-door-open"></i>
            <span>Return to Portal</span>
        </a>

        <?php
        $manual_link = '../assets/docs/employee_manual.pdf';

        if ($is_employee && $is_approver) {
            $manual_link = '../assets/docs/disposal_form_guide_multi_role.pdf';
        } elseif ($is_approver) {
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