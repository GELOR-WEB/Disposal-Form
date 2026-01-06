<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../db/conn.php';

// 1. Get the ID from the URL
if (!isset($_GET['id'])) {
    header("Location: view_forms.php");
    exit();
}
$form_id = $_GET['id'];

try {
    // 2. FETCH FORM DETAILS (SAFE VERSION)
    // I removed the subquery to 'LRNPH_E' to fix the permission error.
    // Now it just pulls the data from your local table.
    $sqlHeader = "SELECT * FROM dsp_forms WHERE id = ?";
                  
    $stmt = $conn->prepare($sqlHeader);
    $stmt->execute([$form_id]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$form) {
        die("Form not found.");
    }

    // 3. Fetch Items
    $sqlItems = "SELECT * FROM dsp_items WHERE form_id = ?";
    $stmtItems = $conn->prepare($sqlItems);
    $stmtItems->execute([$form_id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Form #<?php echo $form_id; ?> Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#f472b6',
                        'primary-dark': '#ec4899',
                        secondary: '#a78bfa',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/jpeg" href="../assets/favicon.jpg">
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        body { padding-left: 280px; padding-top: 20px; padding-right: 20px; }
        .info-group label { display: block; font-size: 0.75rem; color: #9ca3af; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
        .info-group div { font-size: 1rem; color: #374151; font-weight: 600; }
        .status-badge { padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; }
        .status-Pending { background: #fef3c7; color: #92400e; }
        .status-Approved { background: #dcfce7; color: #166534; }
        .status-Rejected { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body class="bg-gray-50">

    <?php include '../includes/sidebar.php'; ?>

    <div class="animate-fade-in max-w-5xl mx-auto">
        
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-500 hover:text-pink-600 mb-6 transition-colors font-medium">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="glass-panel p-8 mb-8 rounded-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-2 bg-gradient-to-b from-pink-400 to-purple-400"></div>
            
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Disposal Request #<?php echo $form['id']; ?></h1>
                    <span class="status-badge status-<?php echo $form['status']; ?>">
                        <?php echo $form['status']; ?>
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Date Created</p>
                    <p class="font-bold text-gray-700 text-lg"><?php echo date('F j, Y', strtotime($form['created_date'])); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-8 border-t border-gray-100 pt-6">
                <div class="info-group">
                    <label>Requested By</label>
                    <div><?php echo htmlspecialchars($form['full_name']); ?></div>
                </div>
                <div class="info-group">
                    <label>Department</label>
                    <div><?php echo htmlspecialchars($form['department']); ?></div>
                </div>
                
                <div class="info-group">
                    <label>Status Check</label>
                    <div>
                       <?php if ($form['status'] === 'Pending'): ?>
                            <span class="text-yellow-600 italic">To be checked</span>
                        
                        <?php elseif ($form['status'] === 'Rejected'): ?>
                            <div class="flex flex-col">
                                <span class="text-red-600 font-semibold">Rejected by: <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Unknown'); ?></span>
                                <span class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?php echo date('M j, Y • g:i A', strtotime($form['approved_date'])); ?>
                                </span>
                            </div>
                        
                        <?php else: ?>
                            <div class="flex flex-col">
                                <span class="text-green-600 font-semibold">Approved by: <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Unknown'); ?></span>
                                <span class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?php echo date('M j, Y • g:i A', strtotime($form['approved_date'])); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fas fa-boxes text-pink-500"></i> Items to Dispose
            </h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-2 border-gray-100 text-xs text-gray-400 uppercase tracking-wider">
                            <th class="pb-4 font-bold">Code</th>
                            <th class="pb-4 font-bold">Description</th>
                            <th class="pb-4 font-bold">Serial No.</th>
                            <th class="pb-4 font-bold">Qty / UOM</th>
                            <th class="pb-4 font-bold">Reason</th>
                            <th class="pb-4 font-bold">Attachment</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600">
                        <?php foreach ($items as $item): ?>
                        <tr class="border-b border-gray-50 hover:bg-pink-50/20 transition-colors">
                            <td class="py-4 font-mono text-pink-600 font-bold"><?php echo htmlspecialchars($item['code']); ?></td>
                            <td class="py-4 font-medium text-gray-800"><?php echo htmlspecialchars($item['description']); ?></td>
                            <td class="py-4"><?php echo htmlspecialchars($item['serial_no']); ?></td>
                            <td class="py-4">
                                <span class="bg-gray-100 px-2 py-1 rounded font-bold text-gray-700">
                                    <?php echo $item['quantity'] . ' ' . $item['unit_of_measure']; ?>
                                </span>
                            </td>
                            <td class="py-4 italic text-gray-500">"<?php echo htmlspecialchars($item['reason']); ?>"</td>
                            <td class="py-4">
                                <?php if (!empty($item['attachment_pictures']) && $item['attachment_pictures'] !== 'No Image'): ?>
                                    <a href="../uploads/<?php echo $item['attachment_pictures']; ?>" target="_blank" class="text-blue-500 hover:underline flex items-center gap-1">
                                        <i class="fas fa-paperclip"></i> View Image
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-300">No Image</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>