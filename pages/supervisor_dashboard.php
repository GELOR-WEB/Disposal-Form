<?php
session_start();
// Enable error reporting to see issues instead of white screen
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db/conn.php';

// 1. CHECK LOGIN
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. ROBUST SECURITY CHECK (The Fix)
// Allows Admins, Managers, Supervisors, Leaders
$my_role = strtolower($_SESSION['role'] ?? '');
$my_job  = strtolower($_SESSION['job_level'] ?? '');
$full_role_string = $my_role . ' ' . $my_job;

$is_admin_level = (
    strpos($full_role_string, 'admin') !== false || 
    strpos($full_role_string, 'supervisor') !== false || 
    strpos($full_role_string, 'manager') !== false || 
    strpos($full_role_string, 'leader') !== false ||
    $my_role === 'executive' ||
    $my_role === 'department head'
);

if (!$is_admin_level) {
    // If they don't have permission, send them to View Forms instead of Staff Entry
    header("Location: view_forms.php");
    exit();
}

$forms = [];
$error_message = "";

try {
    // Fetch forms using PDO
    // Updated table names to match your system (dsp_forms / dsp_items)
    $sql = "SELECT df.id, df.full_name, df.created_date, df.department, df.status,
            COUNT(di.id) as item_count
            FROM dsp_forms df
            LEFT JOIN dsp_items di ON df.id = di.form_id
            GROUP BY df.id, df.full_name, df.created_date, df.department, df.status
            ORDER BY df.created_date DESC";

    $stmt = $conn->query($sql);
    
    if ($stmt === false) {
        // Capture SQL Error if query fails
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
    <title>Supervisor Dashboard</title>
    <link rel="stylesheet" href="../styles/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/jpg" href="../assets/favicon.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800">

    <?php 
    // Safely include sidebar
    if (file_exists('../includes/sidebar.php')) {
        include '../includes/sidebar.php'; 
    } else {
        echo '<div class="p-4 bg-red-100 text-red-700">Error: includes/sidebar.php not found.</div>';
    }
    ?>
    
    <div class="ml-64 p-10 animate-fade-in">
        <h1 class="text-3xl font-bold mb-2 text-gray-800">Approvals Dashboard</h1>
        <p class="text-gray-500 mb-8">Manage and review disposal requests.</p>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <strong class="font-bold">Database Error:</strong>
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <div class="glass-panel bg-white/80 rounded-2xl shadow-xl overflow-hidden border border-white/50">
            <table class="w-full">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Created By</th>
                        <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="p-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($forms)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-gray-400">No disposal forms found.</td></tr>
                    <?php else: ?>
                        <?php foreach($forms as $form): ?>
                        <tr class="hover:bg-pink-50/30 transition-colors">
                            <td class="p-5 font-mono text-sm text-pink-500 font-bold">#<?php echo $form['id']; ?></td>
                            <td class="p-5 font-medium"><?php echo $_SESSION['fullname']; ?></td>
                            <td class="p-5 text-gray-500"><?php echo $_SESSION['department']; ?></td>
                            <td class="p-5"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold"><?php echo $form['item_count']; ?> Items</span></td>
                            <td class="p-5">
                                <?php 
                                    $statusColor = match($form['status']) {
                                        'Approved' => 'bg-green-100 text-green-700',
                                        'Rejected' => 'bg-red-100 text-red-700',
                                        default => 'bg-yellow-100 text-yellow-700'
                                    };
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?php echo $statusColor; ?>">
                                    <?php echo $form['status']; ?>
                                </span>
                            </td>
                            <td class="p-5 flex gap-2">
                                <?php if($form['status'] == 'Pending'): ?>
                                    <button onclick="approveForm(<?php echo $form['id']; ?>)" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    
                                    <button onclick="rejectForm(<?php echo $form['id']; ?>)" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs italic">Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    async function approveForm(id) {
        if(!confirm('Are you sure you want to approve this form?')) return;
        
        try {
            const res = await fetch('../api/approve_forms.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({form_ids: [id]})
            });
            const data = await res.json();
            
            if(data.success) {
                alert('Success!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch(e) {
            alert('System Error: ' + e.message);
        }
    }
    
    async function rejectForm(id) {
        if(!confirm('Are you sure you want to REJECT this request?')) return;
        
        try {
            const res = await fetch('../api/reject_form.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({form_ids: [id]})
            });
            const data = await res.json();
            
            if(data.success) {
                alert('Request Rejected.');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch(e) {
            alert('System Error: ' + e.message);
        }
    }
    </script>
</body>
</html>