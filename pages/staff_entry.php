<?php
// pages/staff_entry.php
session_start();

// 1. SECURITY: Check Login
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] === 'Invalid') {
    // If role is invalid, log out the user
    header("Location: ../auth/logout.php");
    exit();
}

// 2. SECURITY: Kick out Admins
$job = strtolower($_SESSION['job_level'] ?? '');
$is_supervisor = (strpos($job, 'supervisor') !== false ||
    strpos($job, 'manager') !== false ||
    strpos($job, 'admin') !== false);

if ($is_supervisor) {
    header("Location: supervisor_dashboard.php");
    exit();
}

require_once '../db/conn.php';
// --- AUTO-GENERATE NEXT CONTROL NO ---
try {
    // 1. Get the highest ID currently in the database
    $sql_last = "SELECT MAX(id) as last_id FROM dsp_forms";
    $stmt_last = $conn->query($sql_last);
    $row_last = $stmt_last->fetch(PDO::FETCH_ASSOC);
    
    // 2. Calculate the next ID (if DB is empty, start at 1)
    $next_id = ($row_last['last_id'] ?? 0) + 1;
    
    // 3. Format it: "CN-" + Year + "-000X" (e.g., CN-2026-0006)
    $display_control_no = "CN-" . date('Y') . "-" . str_pad($next_id, 4, '0', STR_PAD_LEFT);

} catch (Exception $e) {
    // Fallback if DB fails (prevents page crash)
    $display_control_no = "TMP-" . date('YmdHis');
}
// -------------------------------------
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Disposal • La Rose Noire</title>
    <link rel="icon" type="image/jpg" href="../assets/images/favicon.jpg">
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
        body {
            padding-left: 280px;
            padding-top: 20px;
            padding-right: 20px;
        }

        .header-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            align-items: center;
        }

        .header-info-box {
            background: rgba(255, 255, 255, 0.5);
            padding: 15px;
            border-radius: 16px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        .info-label {
            font-size: 0.75rem;
            color: #9ca3af;
            font-weight: 700;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 0.9rem;
            color: #374151;
            font-weight: 600;
        }

        .modal-form-grid {
            display: grid;
            gap: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        #loadingOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.8);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
    </style>
</head>

<body>

    <div id="loadingOverlay">
        <div style="text-align: center;">
            <i class="fas fa-spinner fa-spin" style="font-size: 3rem; color: var(--primary);"></i>
            <p style="margin-top: 1rem; font-weight: 600; color: var(--secondary);">Submitting Form...</p>
        </div>
    </div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="animate-fade-in max-w-7xl mx-auto">
        <div class="glass-panel p-6" style="border-radius: 24px; position: relative; overflow: hidden; margin-bottom: 2rem;">
            <div style="position: absolute; top: 0; left: 0; bottom: 0; width: 6px; background: linear-gradient(to bottom, #f472b6, #a78bfa);"></div>
            <div class="header-grid">
                <div>
                    <h1 style="margin: 0; font-size: 2rem; font-weight: 800; color: #1f2937;">Create Disposal Form</h1>
                    <p style="margin: 5px 0 0 0; color: #6b7280;">Fill in the details below to submit a request.</p>
                </div>
                <div class="header-info-box">
                    <div>
                        <div class="info-label">Created By</div>
                        <div class="info-value"><?php echo $_SESSION['fullname'] ?? 'User'; ?></div>
                    </div>
                    <div>
                        <div class="info-label">Date</div>
                        <div class="info-value"><?php echo date('F j, Y'); ?></div>
                    </div>
                    <div>
                        <div class="info-label">Department</div>
                        <div class="info-value"><?php echo $_SESSION['department'] ?? 'Dept'; ?></div>
                    </div>
                    <div>
                        <div class="info-label">Control No.</div>
                        <div class="info-value" style="color: var(--primary);"><?php echo $display_control_no; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="padding: 2rem; min-height: 500px; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-weight: 700;">Disposal Items</h3>
                <span class="status-badge status-pending">Draft Mode</span>
            </div>

            <div class="table">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Serial No.</th>
                            <th>UOM</th>
                            <th>Qty</th>
                            <th>Reason</th>
                            <th>Attachment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <tr id="emptyRow">
                            <td colspan="8" style="text-align: center; padding: 40px; color: #9ca3af;">
                                <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                No items added yet. Click "Add Item" to start.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: auto; display: flex; justify-content: center; padding-top: 20px;">
                <button type="button" class="btn-primary" onclick="openModal(false)">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; justify-content: flex-end; padding-bottom: 40px;">
            <button type="button" onclick="submitForm()" class="btn-primary" style="background: var(--gradient-secondary); padding: 15px 40px; font-size: 1.1rem;">
                <i class="fas fa-paper-plane"></i> Submit Form
            </button>
        </div>
    </div>

    <div id="itemModal" class="modal-overlay">
        <div class="modal" style="width: 500px;">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Item</h3>
                <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="itemForm" class="modal-form-grid">
                    <div class="form-group">
                        <label>Item Code</label>
                        <input type="text" id="m_code" class="form-input" placeholder="e.g. IT-2026-001" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" id="m_desc" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Serial No.</label>
                        <input type="text" id="m_serial" class="form-input" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Unit of Measure</label>
                            <select id="m_uom" class="form-input" required>
                                <option value="" disabled selected>Select an item</option>
                                <option value="Piece">Piece</option>
                                <option value="Pieces">Pieces</option>
                                <option value="Bag">Bag</option>
                                <option value="Bin">Bin</option>
                                <option value="Bowl">Bowl</option>
                                <option value="Box">Box</option>
                                <option value="Card">Card</option>
                                <option value="Carton">Carton</option>
                                <option value="Case">Case</option>
                                <option value="Centimeters">Centimeters</option>
                                <option value="Dozen">Dozen</option>
                                <option value="Each">Each</option>
                                <option value="Foot">Foot</option>
                                <option value="Gallon">Gallon</option>
                                <option value="Gross">Gross</option>
                                <option value="Inches">Inches</option>
                                <option value="Kilos">Kilos</option>
                                <option value="Millimeter">Millimeter</option>
                                <option value="Pack 50">Pack 50</option>
                                <option value="Pack 100">Pack 100</option>
                                <option value="Pair">Pair</option>
                                <option value="Pallet">Pallet</option>
                                <option value="Rack">Rack</option>
                                <option value="Rim">Rim</option>
                                <option value="Roll">Roll</option>
                                <option value="Set">Set</option>
                                <option value="Set of 3">Set of 3</option>
                                <option value="Set of 5">Set of 5</option>
                                <option value="Sheet">Sheet</option>
                                <option value="Single">Single</option>
                                <option value="Square Ft">Square Ft</option>
                                <option value="Tube">Tube</option>
                                <option value="Yard">Yard</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" id="m_qty" class="form-input" min="1" required 
                            onkeydown="if(event.key==='-' || event.key==='e') event.preventDefault()">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Reason</label>
                        <textarea id="m_reason" class="form-input" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Attachment Picture</label>
                        <input type="file" id="m_file" class="form-input" accept="image/*" required>
                        <p id="existingFileLabel" class="text-xs text-gray-500 mt-1 hidden"></p>
                    </div>
                    <button type="button" id="modalSubmitBtn" class="btn-primary" style="width: 100%; margin-top: 10px;" onclick="saveItem()">Add Item to Form</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let items = JSON.parse(localStorage.getItem('disposal_form_items')) || [];
        let itemFiles = {};
        let editingIndex = -1; // New variable to track editing state

        window.addEventListener('DOMContentLoaded', () => {
            renderTable();
        });

        // Updated openModal to handle Edit State
        function openModal(isEdit = false, index = -1) {
            const modal = document.getElementById('itemModal');
            const title = document.getElementById('modalTitle');
            const btn = document.getElementById('modalSubmitBtn');
            const fileLabel = document.getElementById('existingFileLabel');

            modal.classList.add('active');

            if (isEdit) {
                editingIndex = index;
                title.innerText = "Edit Item";
                btn.innerText = "Update Item";

                // Pre-fill form
                const item = items[index];
                document.getElementById('m_code').value = item.code;
                document.getElementById('m_desc').value = item.desc;
                document.getElementById('m_serial').value = item.serial;
                document.getElementById('m_uom').value = item.uom;
                document.getElementById('m_qty').value = item.qty;
                document.getElementById('m_reason').value = item.reason;

                // Show file info
                if (item.fileName && item.fileName !== "No Image") {
                    fileLabel.innerText = "Current file: " + item.fileName;
                    fileLabel.classList.remove('hidden');
                } else {
                    fileLabel.classList.add('hidden');
                }
            } else {
                editingIndex = -1;
                title.innerText = "Add New Item";
                btn.innerText = "Add Item to Form";
                document.getElementById('itemForm').reset();
                fileLabel.classList.add('hidden');
            }
        }

        function closeModal() {
            document.getElementById('itemModal').classList.remove('active');
            document.getElementById('itemForm').reset();
            editingIndex = -1;
        }

        // Renamed logic to saveItem to handle both Add and Update
        function saveItem() {
            // 1. Get all input values (Trim removes accidental spaces)
            const code = document.getElementById('m_code').value.trim();
            const desc = document.getElementById('m_desc').value.trim();
            const serial = document.getElementById('m_serial').value.trim();
            const uom = document.getElementById('m_uom').value;
            const qty = document.getElementById('m_qty').value.trim();
            const reason = document.getElementById('m_reason').value.trim();
            const fileInput = document.getElementById('m_file');

            // 2. LOGIC: Check if ANY text field is empty
            // We also check if it is a NEW item (editingIndex === -1) and no file is selected.
            const isMissingFile = (editingIndex === -1 && fileInput.files.length === 0);

            if (!code || !desc || !serial || !uom || !qty || !reason || isMissingFile) {
                alert("Please fill out all fields");
                return; // Stop the function here
            }

            // ... (Rest of the saving logic remains the same) ...

            // Check for new file
            let fileName = "No Image";
            let hasNewFile = fileInput.files.length > 0;

            if (editingIndex > -1) {
                // --- UPDATE EXISTING ITEM ---
                const item = items[editingIndex];
                item.code = code;
                item.desc = desc;
                item.serial = serial;
                item.uom = uom;
                item.qty = qty;
                item.reason = reason;

                if (hasNewFile) {
                    itemFiles[item.temp_id] = fileInput.files[0];
                    item.fileName = fileInput.files[0].name;
                }
            } else {
                // --- ADD NEW ITEM ---
                const tempId = Date.now();
                if (hasNewFile) {
                    itemFiles[tempId] = fileInput.files[0];
                    fileName = fileInput.files[0].name;
                }

                items.push({
                    temp_id: tempId,
                    code: code,
                    desc: desc,
                    serial: serial,
                    uom: uom,
                    qty: qty,
                    reason: reason,
                    fileName: fileName
                });
            }

            localStorage.setItem('disposal_form_items', JSON.stringify(items));
            renderTable();
            closeModal();
        }

        function renderTable() {
            const tbody = document.getElementById('itemsTableBody');
            tbody.innerHTML = '';
            if (items.length === 0) {
                tbody.innerHTML = `<tr id="emptyRow"><td colspan="8" style="text-align:center; padding:40px; color:#9ca3af;">No items added yet.</td></tr>`;
                return;
            }
            items.forEach((item, index) => {
                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td>${item.code}</td>
                        <td><strong>${item.desc}</strong></td>
                        <td>${item.serial}</td>
                        <td>${item.uom}</td>
                        <td>${item.qty}</td>
                        <td>${item.reason}</td>
                        <td>${item.fileName}</td>
                        <td class="flex gap-2">
                             <button class="bg-yellow-400 text-white hover:bg-yellow-500 text-xs px-2 py-1 rounded transition-colors shadow-sm" onclick="openModal(true, ${index})">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <button class="btn-danger text-xs px-2 py-1 rounded shadow-sm" onclick="confirmRemoveItem(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });
        }

        function confirmRemoveItem(index) {
            if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                removeItem(index);
            }
        }

        function removeItem(index) {
            delete itemFiles[items[index].temp_id];
            items.splice(index, 1);
            localStorage.setItem('disposal_form_items', JSON.stringify(items));
            renderTable();
        }

        async function submitForm() {
            if (items.length === 0) {
                alert("Please add at least one item.");
                return;
            }
            const loading = document.getElementById('loadingOverlay');
            loading.style.display = 'flex';

            const formData = new FormData();
            formData.append('items_data', JSON.stringify(items));
            items.forEach(item => {
                if (itemFiles[item.temp_id]) {
                    formData.append('item_file_' + item.temp_id, itemFiles[item.temp_id]);
                }
            });

            try {
                const response = await fetch('../api/submit_disposal_form.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    localStorage.removeItem('disposal_form_items');
                    items = [];
                    alert("Success!");
                    window.location.href = 'view_forms.php';
                } else {
                    alert("Error: " + result.message);
                }
            } catch (error) {
                console.error(error);
                alert("An error occurred.");
            } finally {
                loading.style.display = 'none';
            }
        }
    </script>
</body>

</html>