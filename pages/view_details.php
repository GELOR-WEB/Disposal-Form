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
    // 2. FETCH FORM DETAILS
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Form #<?php echo $form['id']; ?> Details</title>
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
        body {
            padding-left: 280px;
            padding-top: 20px;
            padding-right: 20px;
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

        .info-group label {
            display: block;
            font-size: 0.75rem;
            color: #9ca3af;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .info-group div {
            font-size: 1rem;
            color: #374151;
            font-weight: 600;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

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
    </style>
</head>

<body class="bg-gray-50">

    <?php include '../includes/sidebar.php'; ?>

    <div class="animate-fade-in max-w-5xl mx-auto">

        <a href="javascript:history.back()"
            class="inline-flex items-center gap-2 text-gray-500 hover:text-pink-600 mb-6 transition-colors font-medium">
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
                    <p class="font-bold text-gray-700 text-lg">
                        <?php echo date('F j, Y', strtotime($form['created_date'])); ?></p>
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
                    <label>Approval Hierarchy</label>
                    <div class="space-y-2">

                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">Dept.Head:</span>
                            <?php if (!empty($form['dept_head_approved_date'])): ?>
                                <span class="text-green-600 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> Approved
                                    <span class="text-xs text-gray-500">
                                        (<?php echo date('M j, Y g:i A', strtotime($form['dept_head_approved_date'])); ?>)
                                    </span>
                                </span>
                            <?php elseif ($form['status'] == 'Rejected' && empty($form['dept_head_approved_date'])): ?>
                                <span class="text-red-600 flex items-center gap-1">
                                    <i class="fas fa-times-circle"></i> Rejected
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 flex items-center gap-1">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">Fac.Head:</span>
                            <?php if (!empty($form['fac_head_approved_date'])): ?>
                                <span class="text-green-600 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> Approved
                                    <span class="text-xs text-gray-500">
                                        (<?php echo date('M j, Y g:i A', strtotime($form['fac_head_approved_date'])); ?>)
                                    </span>
                                </span>
                            <?php elseif ($form['status'] == 'Rejected' && !empty($form['dept_head_approved_date']) && empty($form['fac_head_approved_date'])): ?>
                                <span class="text-red-600 flex items-center gap-1">
                                    <i class="fas fa-times-circle"></i> Rejected
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 flex items-center gap-1">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">Executive:</span>
                            <?php if (!empty($form['executive_approved_date'])): ?>
                                <span class="text-green-600 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> Approved
                                    <span class="text-xs text-gray-500">
                                        (<?php echo date('M j, Y g:i A', strtotime($form['executive_approved_date'])); ?>)
                                    </span>
                                </span>
                            <?php elseif ($form['status'] == 'Rejected' && !empty($form['fac_head_approved_date']) && empty($form['executive_approved_date'])): ?>
                                <span class="text-red-600 flex items-center gap-1">
                                    <i class="fas fa-times-circle"></i> Rejected
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 flex items-center gap-1">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">Final Check:</span>
                            <?php if (!empty($form['final_fac_head_approved_date'])): ?>
                                <span class="text-green-600 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> Approved
                                    <span class="text-xs text-gray-500">
                                        (<?php echo date('M j, Y g:i A', strtotime($form['final_fac_head_approved_date'])); ?>)
                                    </span>
                                </span>
                            <?php elseif ($form['status'] == 'Rejected' && !empty($form['executive_approved_date']) && empty($form['final_fac_head_approved_date'])): ?>
                                <span class="text-red-600 flex items-center gap-1">
                                    <i class="fas fa-times-circle"></i> Rejected
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 flex items-center gap-1">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            <?php endif; ?>
                        </div>

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
                                <td class="py-4 font-mono text-pink-600 font-bold">
                                    <?php echo htmlspecialchars($item['code']); ?></td>
                                <td class="py-4 font-medium text-gray-800">
                                    <?php echo htmlspecialchars($item['description']); ?></td>
                                <td class="py-4"><?php echo htmlspecialchars($item['serial_no']); ?></td>
                                <td class="py-4">
                                    <span class="bg-gray-100 px-2 py-1 rounded font-bold text-gray-700">
                                        <?php echo $item['quantity'] . ' ' . $item['unit_of_measure']; ?>
                                    </span>
                                </td>
                                <td class="py-4 italic text-gray-500">"<?php echo htmlspecialchars($item['reason']); ?>"
                                </td>
                                <td class="py-4">
                                    <?php if (!empty($item['image_path']) && $item['image_path'] !== 'No Image'): ?>
                                        <img src="../uploads/<?php echo $item['image_path']; ?>" alt="Attachment"
                                            class="max-w-20 max-h-20 object-cover rounded border cursor-pointer"
                                            onclick="openImageModal('../uploads/<?php echo $item['image_path']; ?>')">
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

    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="relative max-w-4xl max-h-screen p-4">
            <img id="modalImage" src="" alt="Full Size" class="max-w-full max-h-full object-contain">
            <button onclick="closeImageModal()"
                class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100">
                <i class="fas fa-times text-gray-800"></i>
            </button>
        </div>
    </div>

    <script>
        function openImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    </script>
</body>

</html>