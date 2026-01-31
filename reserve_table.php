<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// รับค่าวันที่ที่ลูกค้าเลือก (Default เป็นวันนี้)
$date = $_GET['date'] ?? date('Y-m-d');

// --- Logic 1: เช็คว่าวันนี้มีคอนเสิร์ตไหม? ---
$stmt = $pdo->prepare("SELECT * FROM events WHERE DATE(event_date) = ?");
$stmt->execute([$date]);
$concert = $stmt->fetch();

if ($concert) {
    // ถ้ามีคอนเสิร์ต -> Redirect หรือโชว์ปุ่มไปซื้อบัตร
    $hasConcert = true;
} else {
    $hasConcert = false;

    // --- Logic 2: ถ้าไม่มีคอนเสิร์ต -> ดึงข้อมูลการจองโต๊ะวันนั้นมาเช็คว่าโต๊ะไหนไม่ว่าง ---
    $res_stmt = $pdo->prepare("SELECT table_number FROM reservations WHERE booking_date = ? AND status != 'cancelled'");
    $res_stmt->execute([$date]);
    $reserved_tables = $res_stmt->fetchAll(PDO::FETCH_COLUMN); // Array โต๊ะที่ไม่ว่าง

    // ดึงผังโต๊ะทั้งหมด
    $tables = $pdo->query("SELECT * FROM tables ORDER BY row_idx ASC, col_idx ASC")->fetchAll();

    // หาขนาดผัง
    $max_row = 0;
    $max_col = 0;
    foreach ($tables as $t) {
        if ($t['row_idx'] > $max_row)
            $max_row = $t['row_idx'];
        if ($t['col_idx'] > $max_col)
            $max_col = $t['col_idx'];
    }
}

// --- บันทึกการจอง (เฉพาะวันไม่มีคอนเสิร์ต) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$hasConcert) {
    $table_name = $_POST['selected_table'];

    // เช็คซ้ำอีกรอบกันพลาด
    if (in_array($table_name, $reserved_tables)) {
        echo "<script>alert('ขออภัย โต๊ะนี้เพิ่งถูกจองไป'); window.location.reload();</script>";
        exit();
    }

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $time = $_POST['time'];
    $guests = $_POST['guests'];

    $sql = "INSERT INTO reservations (user_id, customer_name, customer_phone, booking_date, booking_time, table_number, guest_count, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $pdo->prepare($sql)->execute([$_SESSION['user_id'], $name, $phone, $date, $time, $table_name, $guests]);

    echo "<script>alert('จองโต๊ะสำเร็จ! ทางร้านจะรีบยืนยันกลับครับ'); window.location='my_bookings.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จองโต๊ะร้านอาหาร</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            padding-top: 30px;
            padding-bottom: 100px;
        }

        .date-selector {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            margin-bottom: 30px;
        }

        .date-input {
            padding: 10px 20px;
            font-size: 1.2rem;
            border: 2px solid #ddd;
            border-radius: 50px;
            outline: none;
            color: #333;
        }

        /* Concert Alert Card */
        .concert-alert {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            color: #c0392b;
            box-shadow: 0 10px 30px rgba(255, 154, 158, 0.3);
        }

        .btn-buy-ticket {
            background: #c0392b;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(192, 57, 43, 0.3);
            transition: 0.3s;
        }

        .btn-buy-ticket:hover {
            transform: translateY(-3px);
            background: #a93226;
        }

        /* Seat Map (Reuse Style) */
        .seat-map-wrapper {
            background: #2c3e50;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            overflow-x: auto;
        }

        .screen {
            background: #34495e;
            color: #fff;
            padding: 5px 20px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 30px;
            font-size: 0.8rem;
        }

        .seat-grid {
            display: inline-grid;
            grid-gap: 10px;
            justify-content: center;
        }

        .seat-item {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
            border-bottom: 4px solid #ddd;
        }

        /* Status */
        .seat-item.available:hover {
            transform: translateY(-3px);
            background: #3498db;
            color: white;
            border-bottom-color: #2980b9;
        }

        .seat-item.selected {
            background: #2ecc71;
            color: white;
            border-bottom-color: #27ae60;
            box-shadow: 0 0 15px #2ecc71;
        }

        .seat-item.booked {
            background: #555;
            color: #888;
            border-bottom-color: #333;
            cursor: not-allowed;
        }

        .seat-item.vip {
            border: 2px solid #f1c40f;
        }

        /* Checkout Form */
        .booking-form {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            display: none;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div class="container">
        <h1 style="text-align:center;">🍽️ จองโต๊ะ (Dine-in)</h1>
        <p style="text-align:center; color:#666;">เลือกวันที่ต้องการจอง เพื่อดูผังร้าน</p>

        <div class="date-selector">
            <form method="get">
                <label style="font-size:1.1rem; font-weight:bold; margin-right:10px;">วันที่:</label>
                <input type="date" name="date" class="date-input" value="<?= $date ?>" onchange="this.form.submit()"
                    min="<?= date('Y-m-d') ?>">
            </form>
        </div>

        <?php if ($hasConcert): ?>
            <div class="concert-alert">
                <i class="fas fa-music" style="font-size:3rem; margin-bottom:20px;"></i>
                <h2>ขออภัย! วันที่ <?= date("d/m/Y", strtotime($date)) ?> มีคอนเสิร์ต</h2>
                <p style="font-size:1.1rem;">"<?= $concert['title'] ?>"</p>
                <p>กรุณาจองผ่านระบบซื้อบัตรคอนเสิร์ตแทนการจองโต๊ะปกติ</p>
                <a href="booking.php?event_id=<?= $concert['id'] ?>" class="btn-buy-ticket">
                    ไปที่หน้าซื้อบัตรคอนเสิร์ต <i class="fas fa-arrow-right"></i>
                </a>
            </div>

        <?php else: ?>
            <div class="seat-map-wrapper">
                <div class="screen">Bar Counter / เวที</div>
                <div class="seat-grid" style="grid-template-columns: repeat(<?= $max_col ?>, 45px);">
                    <?php
                    for ($r = 1; $r <= $max_row; $r++) {
                        for ($c = 1; $c <= $max_col; $c++) {
                            $found = null;
                            foreach ($tables as $t) {
                                if ($t['row_idx'] == $r && $t['col_idx'] == $c)
                                    $found = $t;
                            }

                            if ($found) {
                                $tName = $found['table_name'];
                                $isBooked = in_array($tName, $reserved_tables);
                                $status = $isBooked ? 'booked' : 'available';
                                $vipClass = ($found['zone'] == 'VIP') ? 'vip' : '';

                                $price = number_format($found['price_modifier'], 0);

                                echo "<div class='seat-item $status $vipClass' 
                                           data-price='{$found['price_modifier']}'
                                           onclick=\"selectSeat(this, '$tName', '$status', {$found['price_modifier']})\">
                                        $tName
                                      </div>";
                            } else {
                                echo "<div></div>";
                            }
                        }
                    }
                    ?>
                </div>
                <div style="margin-top:20px; color:#aaa;">
                    <span style="margin-right:15px;"><i class="fas fa-square" style="color:#fff;"></i> ว่าง</span>
                    <span style="margin-right:15px;"><i class="fas fa-square" style="color:#555;"></i> ไม่ว่าง</span>
                    <span><i class="fas fa-square" style="color:#2ecc71;"></i> ที่เลือก</span>
                </div>
            </div>

            <form method="post" class="booking-form" id="bookingForm">
                <h3 style="border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:20px;">
                    จองโต๊ะ: <span id="selectedTableTxt" style="color:#e67e22;">-</span>
                </h3>

                <div style="background:#f8f9fa; padding:15px; border-radius:10px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                        <span style="color:#666;">ราคาโต๊ะ:</span>
                        <span style="font-weight:bold;" id="tablePriceDisplay">0</span>
                    </div>
                    <div
                        style="display:flex; justify-content:space-between; font-size:1.2rem; color:#2c3e50; font-weight:bold; border-top:1px solid #ddd; padding-top:10px; margin-top:5px;">
                        <span>ยอดชำระสุทธิ:</span>
                        <span style="color:#27ae60;" id="netTotalDisplay">0 บาท</span>
                    </div>
                </div>

                <input type="hidden" name="selected_table" id="inputTable" required>

                <div class="input-group">
                    <label>ชื่อผู้จอง</label>
                    <input type="text" name="name" required placeholder="ชื่อ-นามสกุล">
                </div>
                <div class="input-group">
                    <label>เบอร์โทร</label>
                    <input type="tel" name="phone" required placeholder="08x-xxx-xxxx">
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="input-group">
                        <label>จำนวนท่าน</label>
                        <select name="guests">
                            <?php for ($i = 1; $i <= 10; $i++)
                                echo "<option value='$i'>$i ท่าน</option>"; ?>
                            <option value="11">10+ ท่าน</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>เวลาที่ถึงร้าน</label>
                        <input type="time" name="time" required>
                    </div>
                </div>

                <button type="submit" class="btn-main"
                    style="width:100%; background:#2c3e50; color:#fff; padding:12px; border:none; border-radius:8px; font-size:1.1rem; cursor:pointer;">
                    ยืนยันการจอง
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function selectSeat(el, name, status, price) {
            if (status === 'booked') return;

            // ล้างเลือกเก่า
            document.querySelectorAll('.seat-item').forEach(s => s.classList.remove('selected'));
            el.classList.add('selected');

            // แสดงฟอร์ม
            document.getElementById('bookingForm').style.display = 'block';
            document.getElementById('selectedTableTxt').innerText = name;
            document.getElementById('inputTable').value = name;

            // Update Price
            document.getElementById('tablePriceDisplay').innerText = new Intl.NumberFormat('th-TH').format(price) + ' บาท';
            document.getElementById('netTotalDisplay').innerText = new Intl.NumberFormat('th-TH').format(price) + ' บาท';

            // Scroll ไปที่ฟอร์ม
            document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>

</html>