<?php 
require '../db.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){ header("Location: ../login.php"); exit(); }

// --- Logic PHP (เหมือนเดิม ไม่ต้องแก้) ---
if(isset($_POST['edit_save'])){
    $id = $_POST['table_id'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $zone = $_POST['zone'];
    $pdo->prepare("UPDATE tables SET price_modifier = ?, status = ?, zone = ? WHERE id = ?")->execute([$price, $status, $zone, $id]);
    header("Location: manage_tables.php"); exit();
}
if(isset($_GET['delete'])){
    $pdo->prepare("DELETE FROM tables WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_tables.php"); exit();
}
if(isset($_POST['clear_all'])){
    $pdo->query("TRUNCATE TABLE tables");
    header("Location: manage_tables.php"); exit();
}
if(isset($_POST['auto_gen'])){
    $rows = (int)$_POST['rows']; 
    $cols = (int)$_POST['cols'];
    $price = $_POST['price'];
    $zone = $_POST['zone'];
    for($r = 1; $r <= $rows; $r++){
        $rowChar = chr(64 + $r); 
        for($c = 1; $c <= $cols; $c++){
            $tableName = $rowChar . $c;
            $check = $pdo->prepare("SELECT id FROM tables WHERE table_name = ?");
            $check->execute([$tableName]);
            if($check->rowCount() == 0){
                $pdo->prepare("INSERT INTO tables (table_name, zone, row_idx, col_idx, price_modifier) VALUES (?, ?, ?, ?, ?)")->execute([$tableName, $zone, $r, $c, $price]);
            }
        }
    }
    header("Location: manage_tables.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"><title>จัดการผังที่นั่ง</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    
    <style>
        /* --- Clean UI Variables --- */
        :root {
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-main: #2d3748;
            --text-muted: #a0aec0;
            --primary: #4f46e5; /* Indigo */
            --danger: #ef4444;
            --success: #10b981;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --radius: 12px;
        }

        body { background: var(--bg-color); font-family: 'Kanit', sans-serif; color: var(--text-main); margin: 0; }
        .admin-layout { display: flex; min-height: 100vh; }
        .content { flex: 1; padding: 40px; }

        /* Header Section */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title h2 { font-size: 1.8rem; font-weight: 600; margin: 0; color: #1a202c; }
        .page-title p { color: var(--text-muted); font-size: 0.9rem; margin-top: 5px; }

        .btn-action {
            background: var(--primary); color: white; border: none; padding: 10px 20px;
            border-radius: 8px; font-weight: 500; cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; gap: 8px; font-size: 0.95rem;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3); }

        /* --- Seat Map Canvas --- */
        .seat-map-wrapper {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: var(--shadow);
            text-align: center;
            overflow-x: auto;
            border: 1px solid #edf2f7;
            position: relative;
        }

        .stage-area {
            width: 60%; margin: 0 auto 60px; height: 40px;
            background: #e2e8f0; border-radius: 0 0 100px 100px;
            display: flex; align-items: center; justify-content: center;
            color: #64748b; font-weight: 600; letter-spacing: 2px; font-size: 0.8rem;
            box-shadow: inset 0 -2px 5px rgba(0,0,0,0.05);
        }

        .seat-grid { display: inline-flex; flex-direction: column; gap: 14px; }
        .seat-row { display: flex; gap: 14px; justify-content: center; }

        /* ตัวที่นั่ง (Seat) - ดีไซน์ใหม่ */
        .seat {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; font-weight: 500; color: #4a5568;
            cursor: pointer; position: relative;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .seat:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 10px 15px rgba(0,0,0,0.05);
            border-color: var(--primary); color: var(--primary);
            z-index: 10;
        }

        /* สีแบ่งตามโซน (Pastel Tones) */
        .seat[data-zone="Standard"] { background: #fff; } /* ขาวคลีน */
        .seat[data-zone="VIP"] { background: #fff1f2; border-color: #fda4af; color: #e11d48; } /* ชมพูอ่อน */
        .seat[data-zone="Box"] { background: #ecfdf5; border-color: #6ee7b7; color: #059669; } /* เขียวอ่อน */
        
        /* โต๊ะที่ถูกจอง - สีเขียว */
        .seat.booked {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-color: #10b981;
            color: #047857;
            font-weight: 600;
        }
        .seat.booked:hover {
            background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
            border-color: #059669;
        }
        
        /* ปิดใช้งาน */
        .seat.disabled { background: #f1f5f9; color: #cbd5e1; border-color: #e2e8f0; cursor: not-allowed; }
        .seat.disabled:hover { transform: none; box-shadow: none; border-color: #e2e8f0; color: #cbd5e1; }

        /* Legend */
        .map-legend {
            margin-top: 40px; display: flex; justify-content: center; gap: 25px;
            font-size: 0.9rem; color: #64748b;
        }
        .legend-item { display: flex; align-items: center; gap: 8px; }
        .dot { width: 12px; height: 12px; border-radius: 4px; }

        /* --- Modal Clean Style --- */
        .modal {
            display: none; position: fixed; z-index: 2000; left: 0; top: 0;
            width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.6); /* ขาวจางๆ */
            backdrop-filter: blur(8px); justify-content: center; align-items: center;
        }
        
        .modal-content {
            background: #fff; padding: 30px; border-radius: 24px;
            width: 420px; max-width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            border: 1px solid #f1f5f9;
            animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes popIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .modal-title h3 { margin: 0; font-size: 1.25rem; color: #1e293b; }
        .close-btn { background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .close-btn:hover { background: #e2e8f0; color: #0f172a; }

        /* Form Elements */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 0.9rem; font-weight: 500; color: #475569; margin-bottom: 8px; }
        
        /* Modern Radio Cards */
        .radio-group { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .radio-card {
            cursor: pointer; border: 2px solid #f1f5f9; padding: 12px; border-radius: 12px;
            display: flex; align-items: center; gap: 10px; transition: 0.2s;
        }
        .radio-card input { display: none; }
        .radio-card:hover { border-color: #cbd5e1; }
        
        .radio-card:has(input:checked) { border-color: var(--primary); background: #eef2ff; color: var(--primary); }
        .radio-card:has(input[value="maintenance"]:checked) { border-color: var(--danger); background: #fef2f2; color: var(--danger); }

        .form-input {
            width: 100%; padding: 12px 16px; border: 2px solid #f1f5f9; border-radius: 12px;
            font-size: 1rem; color: #334155; transition: 0.2s; box-sizing: border-box;
        }
        .form-input:focus { border-color: var(--primary); outline: none; }

        /* Zone Dots */
        .zone-options { display: flex; gap: 15px; }
        .zone-opt { cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .z-circle { width: 36px; height: 36px; border-radius: 50%; border: 2px solid transparent; transition: 0.2s; }
        .zone-opt input { display: none; }
        .zone-opt input:checked + .z-circle { transform: scale(1.1); box-shadow: 0 0 0 2px #fff, 0 0 0 4px var(--primary); }

        .zc-std { background: #f1f5f9; border: 1px solid #cbd5e1; }
        .zc-vip { background: #ffe4e6; border: 1px solid #fda4af; }
        .zc-box { background: #d1fae5; border: 1px solid #6ee7b7; }

        .modal-footer { margin-top: 30px; display: flex; gap: 12px; }
        .btn-save { flex: 1; background: #1e293b; color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 500; cursor: pointer; }
        .btn-save:hover { background: #0f172a; }
        .btn-del { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.9rem; text-decoration: underline; margin-top: 15px; width: 100%; }

    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require 'sidebar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h2>จัดการผังที่นั่ง</h2>
                    <p>คลิกที่โต๊ะเพื่อแก้ไขสถานะ หรือปรับเปลี่ยนราคา</p>
                </div>
                <button onclick="document.getElementById('toolModal').style.display='flex'" class="btn-action">
                    <i class="fas fa-plus"></i> สร้างผังใหม่
                </button>
            </div>
            
            <div class="seat-map-wrapper">
                <div class="stage-area">STAGE</div>
                
                <div class="seat-grid">
                    <?php
                    // ดึงโต๊ะทั้งหมด
                    $tables = $pdo->query("SELECT * FROM tables ORDER BY row_idx, col_idx")->fetchAll();
                    
                    // ดึงข้อมูลการจองที่ยังไม่หมดอายุ (วันนี้และในอนาคต + status confirmed)
                    $today = date('Y-m-d');
                    $reservationStmt = $pdo->prepare("
                        SELECT r.*, u.username 
                        FROM reservations r 
                        LEFT JOIN users u ON r.user_id = u.id 
                        WHERE r.booking_date >= ? AND r.status = 'confirmed'
                    ");
                    $reservationStmt->execute([$today]);
                    $reservations = $reservationStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // สร้าง array แมพโต๊ะกับการจอง
                    $tableBookings = [];
                    foreach ($reservations as $res) {
                        $tableBookings[$res['table_number']] = $res;
                    }
                    
                    if(count($tables) > 0){
                        $maxRow = 0; foreach($tables as $t) { if($t['row_idx'] > $maxRow) $maxRow = $t['row_idx']; }

                        for($r=1; $r<=$maxRow; $r++){
                            echo "<div class='seat-row'>";
                            $rowTables = array_filter($tables, function($v) use ($r) { return $v['row_idx'] == $r; });
                            
                            if(!empty($rowTables)){
                                $maxCol = 0; foreach($rowTables as $rt) { if($rt['col_idx'] > $maxCol) $maxCol = $rt['col_idx']; }
                                for($c=1; $c<=$maxCol; $c++){
                                    $found = null; foreach($rowTables as $rt) { if($rt['col_idx'] == $c) $found = $rt; }

                                    if($found){
                                        $statusClass = ($found['status'] == 'maintenance') ? 'disabled' : '';
                                        $isBooked = isset($tableBookings[$found['table_name']]);
                                        
                                        if ($isBooked) {
                                            $statusClass .= ' booked';
                                            $booking = $tableBookings[$found['table_name']];
                                            $bookingData = json_encode($booking, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                                            echo "<div class='seat {$statusClass}' 
                                                       data-zone='{$found['zone']}'
                                                       onclick='openBookingModal({$bookingData})'>
                                                  {$found['table_name']}
                                                  </div>";
                                        } else {
                                            echo "<div class='seat {$statusClass}' 
                                                       data-zone='{$found['zone']}'
                                                       onclick=\"openEditModal({$found['id']}, '{$found['table_name']}', '{$found['price_modifier']}', '{$found['status']}', '{$found['zone']}')\">
                                                  {$found['table_name']}
                                                  </div>";
                                        }
                                    } else {
                                        echo "<div style='width:48px; height:48px;'></div>";
                                    }
                                }
                            }
                            echo "</div>";
                        }
                    } else { echo "<p style='color:#94a3b8;'>ยังไม่มีข้อมูลโต๊ะ กรุณาสร้างผังใหม่</p>"; }
                    ?>
                </div>

                <div class="map-legend">
                    <div class="legend-item"><div class="dot" style="background:#fff; border:1px solid #cbd5e1;"></div> ปกติ</div>
                    <div class="legend-item"><div class="dot" style="background:#ffe4e6; border:1px solid #fda4af;"></div> VIP</div>
                    <div class="legend-item"><div class="dot" style="background:#d1fae5; border:1px solid #6ee7b7;"></div> Box</div>
                    <div class="legend-item"><div class="dot" style="background:linear-gradient(135deg, #d1fae5, #a7f3d0); border:1px solid #10b981;"></div> ถูกจอง</div>
                    <div class="legend-item"><div class="dot" style="background:#f1f5f9;"></div> ปิดปรับปรุง</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Information Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h3>📋 ข้อมูลการจอง <span id="bookingTableName" style="color:var(--success);"></span></h3>
                </div>
                <button class="close-btn" onclick="closeModal('bookingModal')"><i class="fas fa-times"></i></button>
            </div>
            
            <div style="padding: 10px 0;">
                <div class="form-group">
                    <label class="form-label">ชื่อผู้จอง</label>
                    <div style="padding: 12px 16px; background: #f8fafc; border-radius: 12px; font-weight: 500; color: #1e293b;">
                        <i class="fas fa-user" style="color: #64748b; margin-right: 8px;"></i>
                        <span id="bookingCustomerName"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">เบอร์โทรศัพท์</label>
                    <div style="padding: 12px 16px; background: #f8fafc; border-radius: 12px; font-weight: 500; color: #1e293b;">
                        <i class="fas fa-phone" style="color: #64748b; margin-right: 8px;"></i>
                        <span id="bookingPhone"></span>
                    </div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="form-label">วันที่จอง</label>
                        <div style="padding: 12px 16px; background: #f8fafc; border-radius: 12px; font-weight: 500; color: #1e293b;">
                            <i class="fas fa-calendar" style="color: #64748b; margin-right: 8px;"></i>
                            <span id="bookingDate"></span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">เวลา</label>
                        <div style="padding: 12px 16px; background: #f8fafc; border-radius: 12px; font-weight: 500; color: #1e293b;">
                            <i class="fas fa-clock" style="color: #64748b; margin-right: 8px;"></i>
                            <span id="bookingTime"></span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">จำนวนผู้เข้าร่วม</label>
                    <div style="padding: 12px 16px; background: #f8fafc; border-radius: 12px; font-weight: 500; color: #1e293b;">
                        <i class="fas fa-users" style="color: #64748b; margin-right: 8px;"></i>
                        <span id="bookingGuests"></span> คน
                    </div>
                </div>

                <div class="form-group" id="bookingUsernameGroup" style="display: none;">
                    <label class="form-label">Username ผู้จอง</label>
                    <div style="padding: 12px 16px; background: #eef2ff; border-radius: 12px; font-weight: 500; color: #4f46e5;">
                        <i class="fas fa-at" style="margin-right: 8px;"></i>
                        <span id="bookingUsername"></span>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #d1fae5; border-radius: 12px; border-left: 4px solid #10b981;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #047857; font-weight: 500;">
                        <i class="fas fa-check-circle"></i>
                        <span>สถานะ: ยืนยันแล้ว</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button onclick="closeModal('bookingModal')" class="btn-save" style="background: #64748b;">ปิด</button>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h3>แก้ไขที่นั่ง <span id="seatNameDisplay" style="color:var(--primary);"></span></h3>
                </div>
                <button class="close-btn" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
            </div>
            
            <form method="post">
                <input type="hidden" name="table_id" id="edit_id">

                <div class="form-group">
                    <label class="form-label">สถานะ</label>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="status" value="available" id="status_active">
                            <i class="fas fa-check-circle"></i>
                            <span>เปิดใช้งาน</span>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="status" value="maintenance" id="status_inactive">
                            <i class="fas fa-minus-circle"></i>
                            <span>ปิดปรับปรุง</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ประเภทโซน</label>
                    <div class="zone-options">
                        <label class="zone-opt">
                            <input type="radio" name="zone" value="Standard" id="zone_std">
                            <div class="z-circle zc-std"></div>
                            <span style="font-size:0.8rem;">ปกติ</span>
                        </label>
                        <label class="zone-opt">
                            <input type="radio" name="zone" value="VIP" id="zone_vip">
                            <div class="z-circle zc-vip"></div>
                            <span style="font-size:0.8rem;">VIP</span>
                        </label>
                        <label class="zone-opt">
                            <input type="radio" name="zone" value="Box" id="zone_box">
                            <div class="z-circle zc-box"></div>
                            <span style="font-size:0.8rem;">Box</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ราคาบวกเพิ่ม (บาท)</label>
                    <input type="number" name="price" id="edit_price" class="form-input" placeholder="0.00">
                </div>

                <div class="modal-footer">
                    <button type="submit" name="edit_save" class="btn-save">บันทึกการแก้ไข</button>
                </div>
                <a href="#" id="deleteLink" onclick="return confirm('ยืนยันลบโต๊ะนี้?')" class="btn-del">ลบที่นั่งนี้ถาวร</a>
            </form>
        </div>
    </div>

    <div id="toolModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title"><h3>สร้างผังอัตโนมัติ</h3></div>
                <button class="close-btn" onclick="closeModal('toolModal')"><i class="fas fa-times"></i></button>
            </div>
            <form method="post">
                <div class="form-group">
                    <label class="form-label">จำนวนแถว (A-Z)</label>
                    <select name="rows" class="form-input">
                        <?php for($i=1; $i<=26; $i++): $char = chr(64+$i); ?>
                            <option value="<?= $i ?>"><?= $i ?> แถว (ถึง <?= $char ?>)</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">จำนวนคอลัมน์</label>
                    <input type="number" name="cols" value="10" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">โซนเริ่มต้น</label>
                    <select name="zone" class="form-input">
                        <option value="Standard">ปกติ (Standard)</option>
                        <option value="VIP">VIP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ราคาเริ่มต้น</label>
                    <input type="number" name="price" value="0" class="form-input">
                </div>
                <div class="modal-footer">
                     <button type="submit" name="clear_all" class="btn-save" style="background:#fff; color:#ef4444; border:1px solid #fee2e2;" onclick="return confirm('ล้างข้อมูลเก่าทั้งหมด?')">ล้างผังเก่า</button>
                     <button type="submit" name="auto_gen" class="btn-save">สร้างใหม่</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, price, status, zone) {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('edit_id').value = id;
            document.getElementById('seatNameDisplay').innerText = name;
            document.getElementById('edit_price').value = price;
            document.getElementById('deleteLink').href = '?delete=' + id;
            
            if(status === 'available') document.getElementById('status_active').checked = true;
            else document.getElementById('status_inactive').checked = true;

            if(zone === 'VIP') document.getElementById('zone_vip').checked = true;
            else if(zone === 'Box') document.getElementById('zone_box').checked = true;
            else document.getElementById('zone_std').checked = true;
        }

        function openBookingModal(booking) {
            document.getElementById('bookingModal').style.display = 'flex';
            document.getElementById('bookingTableName').innerText = booking.table_number;
            document.getElementById('bookingCustomerName').innerText = booking.customer_name;
            document.getElementById('bookingPhone').innerText = booking.customer_phone;
            
            // Format date
            const date = new Date(booking.booking_date);
            const formattedDate = date.toLocaleDateString('th-TH', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            document.getElementById('bookingDate').innerText = formattedDate;
            
            // Format time
            document.getElementById('bookingTime').innerText = booking.booking_time.substring(0, 5) + ' น.';
            
            document.getElementById('bookingGuests').innerText = booking.guest_count;
            
            // Show username if available
            if (booking.username) {
                document.getElementById('bookingUsername').innerText = booking.username;
                document.getElementById('bookingUsernameGroup').style.display = 'block';
            } else {
                document.getElementById('bookingUsernameGroup').style.display = 'none';
            }
        }

        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        window.onclick = function(e) { if (e.target.classList.contains('modal')) e.target.style.display = "none"; }
    </script>
</body>
</html>