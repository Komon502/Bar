<?php 
require 'db.php';
require 'upload_helper.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$promptpay_id = "0812345678";
$event_id = $_GET['event_id'] ?? 0;

// 1. ดึงข้อมูล Event
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();
if(!$event) header("Location: tickets.php");

// 2. ดึงผังที่นั่ง (เรียงตาม แถว -> คอลัมน์)
$tables = $pdo->query("SELECT * FROM tables ORDER BY row_idx ASC, col_idx ASC")->fetchAll();

// 3. หาขอบเขตผัง (Max Row, Max Col)
$max_row = 0; $max_col = 0;
foreach($tables as $t){
    if($t['row_idx'] > $max_row) $max_row = $t['row_idx'];
    if($t['col_idx'] > $max_col) $max_col = $t['col_idx'];
}

// 4. เช็คโต๊ะที่ไม่ว่าง (จะเช็คอีกครั้งใน transaction)
$booked_stmt = $pdo->prepare("SELECT table_number FROM bookings WHERE event_id = ? AND status != 'cancelled'");
$booked_stmt->execute([$event_id]);
$booked_tables = $booked_stmt->fetchAll(PDO::FETCH_COLUMN);

// --- บันทึกการจอง (PHP) - แก้ไขเพิ่ม Security ---
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $table_name = $_POST['selected_table'];
    
    try {
        // เริ่ม transaction เพื่อป้องกัน race condition
        $pdo->beginTransaction();
        
        // 1. Lock และเช็คว่าโต๊ะว่างหรือไม่
        $check_stmt = $pdo->prepare(
            "SELECT COUNT(*) as cnt FROM bookings 
             WHERE event_id = ? AND table_number = ? AND status != 'cancelled' 
             FOR UPDATE"
        );
        $check_stmt->execute([$event_id, $table_name]);
        $count = $check_stmt->fetch()['cnt'];
        
        if($count > 0){
            $pdo->rollBack();
            echo "<script>alert('เสียใจด้วย! โต๊ะ $table_name เพิ่งถูกจองไป'); window.location.reload();</script>";
            exit();
        }
        
        // 2. เช็คจำนวนตั๋วคงเหลือ
        $event_check = $pdo->prepare("SELECT current_sold, max_tickets FROM events WHERE id = ? FOR UPDATE");
        $event_check->execute([$event_id]);
        $evt = $event_check->fetch();
        
        $qty = (int)$_POST['quantity'];
        if($evt['current_sold'] + $qty > $evt['max_tickets']){
            $pdo->rollBack();
            $remaining = $evt['max_tickets'] - $evt['current_sold'];
            echo "<script>alert('บัตรเหลือเพียง {$remaining} ใบ'); history.back();</script>";
            exit();
        }
        
        // 3. Upload slip - ใช้ SecureUpload
        $slip_path = "";
        if(isset($_FILES['slip'])){
            $upload_result = SecureUpload::uploadImage($_FILES['slip'], 'uploads/', 'slip');
            if(!$upload_result['success']){
                $pdo->rollBack();
                echo "<script>alert('ข้อผิดพลาด: {$upload_result['error']}'); history.back();</script>";
                exit();
            }
            $slip_path = $upload_result['path'];
        }
        
        // 4. Insert booking
        $name = $_POST['name']; 
        $phone = $_POST['phone']; 
        $email = $_POST['email'];
        $total = $qty * $event['ticket_price'];
        
        $sql = "INSERT INTO bookings (user_id, event_id, customer_name, customer_phone, customer_email, table_number, quantity, total_price, payment_slip, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $pdo->prepare($sql)->execute([$_SESSION['user_id'], $event_id, $name, $phone, $email, $table_name, $qty, $total, $slip_path]);
        
        // 5. Update ticket count
        $pdo->prepare("UPDATE events SET current_sold = current_sold + ? WHERE id = ?")->execute([$qty, $event_id]);
        
        // Commit transaction
        $pdo->commit();
        
        echo "<script>alert('จองสำเร็จ! กรุณารอตรวจสอบ'); window.location='my_bookings.php';</script>";
        
    } catch(Exception $e) {
        $pdo->rollBack();
        error_log("Booking error: " . $e->getMessage());
        echo "<script>alert('เกิดข้อผิดพลาด กรุณาลองใหม่'); history.back();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"><title>เลือกที่นั่ง - <?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container { padding-top: 20px; padding-bottom: 150px; }
        
        /* Seat Map Styling */
        .seat-map-wrapper { 
            background: #282c34; padding: 40px 20px; border-radius: 15px; 
            text-align: center; overflow-x: auto; margin-bottom: 20px;
            box-shadow: inset 0 0 50px rgba(0,0,0,0.5);
        }
        .screen {
            background: #fff; height: 10px; width: 60%; margin: 0 auto 50px; 
            border-radius: 0 0 50px 50px; box-shadow: 0 5px 20px rgba(255,255,255,0.4); 
            opacity: 0.8; font-size: 0.8rem; line-height: 10px; color: #000;
        }
        
        /* Grid Dynamic */
        .seat-grid { 
            display: inline-grid; 
            grid-template-columns: repeat(<?= $max_col ?>, 45px); 
            grid-gap: 10px; justify-content: center;
        }
        
        .seat-item {
            width: 45px; height: 45px; border-radius: 8px; background: #3b404e; color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;
            cursor: pointer; transition: 0.2s; position: relative; border-bottom: 3px solid #21252b;
        }
        
        /* Status Colors */
        .seat-item.available:hover { background: #3498db; transform: translateY(-3px); border-bottom-color: #2980b9; }
        .seat-item.selected { background: #2ecc71; color: #fff; border-bottom-color: #27ae60; box-shadow: 0 0 15px #2ecc71; }
        .seat-item.booked { background: #e74c3c; border-bottom-color: #c0392b; cursor: not-allowed; opacity: 0.3; }
        
        /* Zone Styles (แค่โชว์สี ไม่บวกราคา) */
        .seat-item.vip { border: 2px solid #f1c40f; color: #f1c40f; }
        .seat-item.vip.selected { color: #fff; background: #f1c40f; border-bottom-color: #d4ac0d; }

        .legend { display: flex; justify-content: center; gap: 15px; margin-top: 30px; color: #aaa; font-size: 0.9rem; flex-wrap: wrap; }
        .dot { width: 12px; height: 12px; display: inline-block; border-radius: 50%; margin-right: 5px; }
        
        /* Checkout Bar */
        .checkout-bar {
            position: fixed; bottom: 0; left: 0; width: 100%; background: #fff;
            padding: 20px; box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
            display: none; z-index: 100;
        }
        .bar-content { max-width: 1000px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        
        @media(max-width: 768px){ .bar-content { flex-direction: column; align-items: stretch; } }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>
    
    <div class="container">
        <h1 style="text-align:center;">🎟️ เลือกที่นั่ง (ฟรีเมื่อซื้อบัตร)</h1>
        <p style="text-align:center; color:#666;">งาน: <?= htmlspecialchars($event['title']) ?></p>
        
        <div class="seat-map-wrapper">
            <div class="screen">STAGE / เวที</div>
            
            <div class="seat-grid">
                <?php
                for($r = 1; $r <= $max_row; $r++){
                    for($c = 1; $c <= $max_col; $c++){
                        $found = null;
                        foreach($tables as $t){ if($t['row_idx'] == $r && $t['col_idx'] == $c) { $found = $t; break; } }
                        
                        if($found){
                            $tName = $found['table_name'];
                            $isBooked = in_array($tName, $booked_tables);
                            $status = $isBooked ? 'booked' : 'available';
                            $vipClass = ($found['zone'] == 'VIP') ? 'vip' : '';
                            
                            // ส่งค่าชื่อโต๊ะไป แต่ไม่ส่งราคาเพิ่ม (เพราะฟรี)
                            echo "<div class='seat-item $status $vipClass' 
                                       onclick=\"selectSeat(this, '$tName', '$status')\"
                                       title='Zone: {$found['zone']}'>
                                    $tName
                                  </div>";
                        } else {
                            echo "<div style='width:45px; height:45px;'></div>";
                        }
                    }
                }
                ?>
            </div>

            <div class="legend">
                <span><span class="dot" style="background:#3b404e;"></span> ว่าง</span>
                <span><span class="dot" style="background:#2ecc71;"></span> ที่เลือก</span>
                <span><span class="dot" style="background:#e74c3c;"></span> จองแล้ว</span>
                <span><span class="dot" style="border:1px solid #f1c40f;"></span> VIP Zone</span>
            </div>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="checkout-bar" id="checkoutBar">
        <div class="bar-content">
            <div>
                <h3 style="margin:0; color:#2c3e50;">โต๊ะ: <span id="selectedTableTxt" style="color:#e67e22;">-</span></h3>
                <small style="color:#2ecc71;">(รวมในราคาบัตรแล้ว)</small>
                <input type="hidden" name="selected_table" id="inputTable" required>
            </div>

            <div style="flex:1; display:flex; gap:10px; flex-wrap:wrap;">
                <input type="text" name="name" required placeholder="ชื่อผู้จอง" style="padding:10px; border:1px solid #ddd; border-radius:5px; width:120px;">
                <input type="tel" name="phone" required placeholder="เบอร์โทร" style="padding:10px; border:1px solid #ddd; border-radius:5px; width:120px;">
                <input type="email" name="email" required placeholder="อีเมล" style="padding:10px; border:1px solid #ddd; border-radius:5px; width:150px;">
                
                <div style="display:flex; align-items:center; gap:5px;">
                    <span>จำนวนบัตร:</span>
                    <input type="number" id="qty" name="quantity" min="1" value="1" onchange="calcTotal()" style="padding:10px; border:1px solid #ddd; border-radius:5px; width:60px;">
                </div>
                
                <label style="cursor:pointer; background:#f8f9fa; padding:10px; border-radius:5px; font-size:0.9rem; border:1px solid #ddd;">
                    <i class="fas fa-camera"></i> แนบสลิป
                    <input type="file" name="slip" required accept="image/*" style="display:none;" onchange="alert('แนบสลิปเรียบร้อย')">
                </label>
            </div>

            <div style="text-align:right;">
                <div style="font-size:0.9rem; color:#888;">ราคารวม</div>
                <div style="font-size:1.4rem; font-weight:bold; color:#2c3e50;" id="totalPrice">฿0</div>
                
                <button type="button" onclick="showQR()" style="background:none; border:none; color:#3498db; cursor:pointer; text-decoration:underline; font-size:0.9rem;">ดู QR Code</button>
                <button type="submit" class="btn-main" style="padding:10px 20px; margin-left:10px; background:#e67e22; color:#fff; border:none; border-radius:5px; cursor:pointer;">ยืนยันจอง</button>
            </div>
        </div>
    </form>

    <div id="qrModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:30px; border-radius:15px; text-align:center; position:relative;">
            <span onclick="document.getElementById('qrModal').style.display='none'" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h3>สแกนจ่ายเงิน</h3>
            <img id="qrImg" src="" style="width:200px; margin:10px 0;">
            <p>พร้อมเพย์: <?= $promptpay_id ?></p>
        </div>
    </div>

    <script>
        const ticketPrice = <?= $event['ticket_price'] ?>;
        const ppID = "<?= $promptpay_id ?>";

        function selectSeat(el, name, status) {
            if(status === 'booked') return;

            // ล้างการเลือกเก่า
            document.querySelectorAll('.seat-item').forEach(s => s.classList.remove('selected'));
            
            // เลือกใหม่
            el.classList.add('selected');

            // อัปเดตข้อมูล (ไม่มีราคาบวกเพิ่ม)
            document.getElementById('checkoutBar').style.display = 'block';
            document.getElementById('selectedTableTxt').innerText = name;
            document.getElementById('inputTable').value = name;
            
            calcTotal();
        }

        function calcTotal() {
            let qty = document.getElementById('qty').value;
            if(qty < 1) qty = 1;
            
            // [แก้ไขสูตรคำนวณ JS] : ราคาบัตร * จำนวน (โต๊ะฟรี)
            let total = ticketPrice * qty;
            
            document.getElementById('totalPrice').innerText = '฿' + total.toLocaleString();
            document.getElementById('qrImg').src = `https://promptpay.io/${ppID}/${total}.png`;
        }

        function showQR() {
            document.getElementById('qrModal').style.display = 'flex';
        }
    </script>
</body>
</html>