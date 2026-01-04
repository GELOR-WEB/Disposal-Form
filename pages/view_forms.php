<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../db/conn.php';

// 1. UPDATED QUERY: Uses the correct table 'disposal_forms'
$sql = "SELECT * FROM disposal_forms ORDER BY created_date DESC";
$stmt = $conn->query($sql);
$forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        /* 3. UPDATED: Adjusted padding to match dashboard */
        body { 
            padding-left: 280px; 
            padding-top: 20px; 
            padding-right: 20px; 
        }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
        .status-Pending { background: #fef3c7; color: #92400e; }
        .status-Approved { background: #dcfce7; color: #166534; }
        .status-Rejected { background: #fee2e2; color: #991b1b; }
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
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 15px; top: 12px; color: #9ca3af;"></i>
                <input type="text" class="form-input" placeholder="Search Control No..." style="padding-left: 40px; width: 300px;">
            </div>
        </div>

        <div class="card p-6">
            <div class="table w-full"> <table class="w-full" style="border-collapse: collapse;">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[10%]">Control No.</th>
                            <th class="text-left py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[20%]">Date Created</th>
                            <th class="text-left py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[20%]">Department</th>
                            <th class="text-left py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[20%]">Created By</th>
                            <th class="text-left py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[15%]">Status</th>
                            <th class="text-left py-4 px-4 font-bold text-gray-500 uppercase text-xs w-[15%]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (count($forms) > 0): ?>
                            <?php foreach ($forms as $form): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-4 font-mono text-pink-600 font-bold text-sm">
                                        #<?php echo $form['id']; ?>
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
                                    <td class="py-4 px-4">
                                        <span class="status-badge status-<?php echo $form['status']; ?> text-xs px-3 py-1">
                                            <?php echo $form['status']; ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <a href="view_details.php?id=<?php echo $form['id']; ?>" class="bg-white border border-gray-200 text-gray-600 hover:text-pink-600 hover:border-pink-300 px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-sm">
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
    </div>
</body>
</html>