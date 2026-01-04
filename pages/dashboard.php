<?php
// 1. Session Fix (Kept from your previous step)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../db/conn.php'; 

$display_control_no = "NEW-" . date('Ymd');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Disposal • Facilities</title>
    
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
        /* 3. UPDATED: Increased padding-left so content isn't hidden behind the sidebar */
        body { 
            padding-left: 280px; /* Changed from 100px to fit the 256px sidebar */
            padding-top: 20px; 
            padding-right: 20px; 
        }
        
        .header-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: center; }
        .header-info-box { background: rgba(255,255,255,0.5); padding: 15px; border-radius: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; border: 1px solid rgba(255,255,255,0.6); }
        .info-label { font-size: 0.75rem; color: #9ca3af; font-weight: 700; text-transform: uppercase; }
        .info-value { font-size: 0.9rem; color: #374151; font-weight: 600; }
        .modal-form-grid { display: grid; gap: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; }
        
        #loadingOverlay { display: none; position: fixed; inset: 0; background: rgba(255,255,255,0.8); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
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
                    <div><div class="info-label">Created By</div><div class="info-value"><?php echo $_SESSION['full_name']; ?></div></div>
                    <div><div class="info-label">Date</div><div class="info-value"><?php echo date('F j, Y'); ?></div></div>
                    <div><div class="info-label">Department</div><div class="info-value"><?php echo $_SESSION['dept']; ?></div></div>
                    <div><div class="info-label">Control No.</div><div class="info-value" style="color: var(--primary);"><?php echo $display_control_no; ?></div></div>
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
                <button type="button" class="btn-primary" onclick="openModal()">
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
                <h3>Add New Item</h3>
                <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="itemForm" class="modal-form-grid">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Item Code</label>
                        <input type="text" id="m_code" class="form-input" placeholder="e.g. IT-2026-001">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" id="m_desc" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label>Serial No.</label>
                        <input type="text" id="m_serial" class="form-input">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Unit of Measure</label>
                            <select id="m_uom" class="form-input">
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
                            <input type="number" id="m_qty" class="form-input" min="1" oninput="this.value = !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Reason</label>
                        <textarea id="m_reason" class="form-input" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Attachment Picture</label>
                        <input type="file" id="m_file" class="form-input" accept="image/*">
                    </div>

                    <button type="button" class="btn-primary" style="width: 100%; margin-top: 10px;" onclick="addItemToTable()">Add Item to Form</button>
                </form>
            </div>
        </div>
    </div>

    <script>
            // 1. Initialize items from LocalStorage if they exist (Data Persistence)
            let items = JSON.parse(localStorage.getItem('disposal_form_items')) || [];
            let itemFiles = {}; // Files cannot be stored in LocalStorage, they live in memory only
            let editingIndex = -1; // Tracks if we are adding (-1) or editing (0, 1, 2...)

            // Load items immediately when page opens
            window.addEventListener('DOMContentLoaded', () => {
                renderTable();
            });

            function openModal(isEdit = false) { 
                document.getElementById('itemModal').classList.add('active'); 
                
                // Change button text based on mode
                const btn = document.querySelector('#itemModal .btn-primary');
                if(isEdit) {
                    document.querySelector('.modal-header h3').innerText = "Edit Item";
                    btn.innerText = "Update Item";
                    btn.onclick = updateItem; // Switch function to Update
                } else {
                    document.querySelector('.modal-header h3').innerText = "Add New Item";
                    btn.innerText = "Add Item to Form";
                    btn.onclick = addItemToTable; // Switch function to Add
                }
            }

            function closeModal() { 
                document.getElementById('itemModal').classList.remove('active'); 
                document.getElementById('itemForm').reset();
                editingIndex = -1; // Reset edit mode
            }

            function saveToStorage() {
                // Save the text data to browser memory
                localStorage.setItem('disposal_form_items', JSON.stringify(items));
            }

            function addItemToTable() {
                // Collect Data
                const desc = document.getElementById('m_desc').value;
                const qty = document.getElementById('m_qty').value;
                const fileInput = document.getElementById('m_file');

                if(!desc || !qty) { alert("Description and Quantity are required."); return; }

                const tempId = Date.now(); // Unique ID for this item
                let fileName = "No Image";
                
                // Handle File
                if(fileInput.files.length > 0) {
                    itemFiles[tempId] = fileInput.files[0];
                    fileName = fileInput.files[0].name;
                }

                const item = {
                    temp_id: tempId,
                    code: document.getElementById('m_code').value,
                    desc: desc,
                    serial: document.getElementById('m_serial').value,
                    uom: document.getElementById('m_uom').value,
                    qty: qty,
                    reason: document.getElementById('m_reason').value,
                    fileName: fileName
                };

                items.push(item);
                saveToStorage(); // <--- SAVES DATA
                renderTable();
                closeModal();
            }

            // 2. NEW: Function to prepare the modal for editing
            function editItem(index) {
                editingIndex = index;
                const item = items[index];

                // Fill the form with existing data
                document.getElementById('m_code').value = item.code;
                document.getElementById('m_desc').value = item.desc;
                document.getElementById('m_serial').value = item.serial;
                document.getElementById('m_uom').value = item.uom;
                document.getElementById('m_qty').value = item.qty;
                document.getElementById('m_reason').value = item.reason;
                
                // Note: We cannot set the file input value programmatically for security reasons
                
                openModal(true); // Open in "Edit Mode"
            }

            // 3. NEW: Function to save the changes
            function updateItem() {
                if(editingIndex === -1) return;

                const item = items[editingIndex];
                const fileInput = document.getElementById('m_file');

                // Update properties
                item.code = document.getElementById('m_code').value;
                item.desc = document.getElementById('m_desc').value;
                item.serial = document.getElementById('m_serial').value;
                item.uom = document.getElementById('m_uom').value;
                item.qty = document.getElementById('m_qty').value;
                item.reason = document.getElementById('m_reason').value;

                // Only update file if they chose a NEW one
                if(fileInput.files.length > 0) {
                    itemFiles[item.temp_id] = fileInput.files[0];
                    item.fileName = fileInput.files[0].name;
                }

                saveToStorage(); // <--- SAVES DATA
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
                    // Check if we lost the file image (because of page reload)
                    let fileDisplay = item.fileName;
                    if(fileDisplay !== "No Image" && !itemFiles[item.temp_id]) {
                        fileDisplay += " <span class='text-red-500 text-xs'>(Lost on reload)</span>";
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>${item.code}</td>
                            <td><strong>${item.desc}</strong></td>
                            <td>${item.serial}</td>
                            <td>${item.uom}</td>
                            <td>${item.qty}</td>
                            <td>${item.reason}</td>
                            <td>${fileDisplay}</td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn-warning text-xs px-2 py-1 rounded" onclick="editItem(${index})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-danger text-xs px-2 py-1 rounded" onclick="removeItem(${index})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });
            }

            function removeItem(index) {
                const item = items[index];
                delete itemFiles[item.temp_id]; 
                items.splice(index, 1);
                saveToStorage(); // Update storage after delete
                renderTable();
            }

            async function submitForm() {
                if(items.length === 0) { alert("Please add at least one item."); return; }

                const loading = document.getElementById('loadingOverlay');
                loading.style.display = 'flex';

                const formData = new FormData();
                formData.append('items_data', JSON.stringify(items));

                // Append files that are currently in memory
                items.forEach(item => {
                    if(itemFiles[item.temp_id]) {
                        formData.append('item_file_' + item.temp_id, itemFiles[item.temp_id]);
                    }
                });

                try {
                    const response = await fetch('../api/submit_disposal_form.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const text = await response.text();
                    try {
                        const result = JSON.parse(text);
                        if(result.success) {
                            // CLEAR STORAGE ON SUCCESS
                            localStorage.removeItem('disposal_form_items'); 
                            items = [];
                            
                            alert("Success! Form ID: " + result.form_id);
                            window.location.href = 'view_forms.php'; 
                        } else {
                            alert("Error: " + result.message);
                        }
                    } catch(e) {
                        console.error("Server Error:", text);
                        alert("Server error. Check console for details.");
                    }
                } catch (error) {
                    console.error(error);
                    alert("An error occurred while submitting.");
                } finally {
                    loading.style.display = 'none';
                }
            }
    </script>
</body>
</html>