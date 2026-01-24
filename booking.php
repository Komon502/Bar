<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$event_id = $_GET['event_id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) header("Location: tickets.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $qty = $_POST['quantity'];
    $total = $qty * $event['ticket_price'];

    // ตรวจสอบจำนวนบัตรว่าง
    $available = $event['max_tickets'] - $event['current_sold'];
    if ($qty > $available) {
        echo "<script>alert('ขออภัย บัตรเหลือเพียง $available ใบ');</script>";
    } else {
        // อัปโหลดสลิป
        $slip_path = "";
        if (isset($_FILES['slip']) && $_FILES['slip']['error'] == 0) {
            $ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
            $new_name = "slip_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['slip']['tmp_name'], "uploads/" . $new_name);
            $slip_path = "uploads/" . $new_name;
        }

        // 1. สร้าง Booking
        $sql = "INSERT INTO bookings (user_id, event_id, customer_name, customer_phone, quantity, total_price, payment_slip, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
        $pdo->prepare($sql)->execute([$_SESSION['user_id'], $event_id, $name, $phone, $qty, $total, $slip_path]);
        $booking_id = $pdo->lastInsertId();

        // 2. เจนรหัสตั๋ว (Running Number)
        // เริ่มนับจาก (start_num + current_sold)
        $start_run = $event['start_num'] + $event['current_sold'];

        for ($i = 0; $i < $qty; $i++) {
            $run_number = $start_run + $i;
            $code = $event['prefix'] . " " . str_pad($run_number, 3, '0', STR_PAD_LEFT); // เช่น MA 847

            $pdo->prepare("INSERT INTO ticket_items (booking_id, ticket_code) VALUES (?, ?)")
                ->execute([$booking_id, $code]);
        }

        // 3. อัปเดตยอดขายใน Events
        $pdo->prepare("UPDATE events SET current_sold = current_sold + ? WHERE id = ?")
            ->execute([$qty, $event_id]);

        echo "<script>alert('จองสำเร็จ! กรุณารอแอดมินตรวจสอบสลิป'); window.location='my_bookings.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-box {
            display: flex;
            gap: 40px;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
        }

        .qr-section {
            text-align: center;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed #ddd;
        }

        .qr-img {
            width: 200px;
            height: 200px;
            object-fit: contain;
        }
    </style>
</head>

<body>
    <?php require 'navbar.php'; ?>
    <div class="container">
        <h1>🛍️ ชำระเงิน</h1>
        <div class="checkout-box">
            <div style="flex:1;">
                <h3><?= htmlspecialchars($event['title']) ?></h3>
                <p>ราคาใบละ: <strong>฿<?= number_format($event['ticket_price']) ?></strong></p>
                <form method="post" enctype="multipart/form-data" id="bookingForm">
                    <label>ชื่อผู้จอง (หน้าบัตร)</label>
                    <input type="text" name="name" required placeholder="ชื่อ-นามสกุล">

                    <label>เบอร์โทรศัพท์</label>
                    <input type="tel" name="phone" required placeholder="08x-xxx-xxxx">

                    <label>จำนวนบัตร</label>
                    <input type="number" id="qty" name="quantity" min="1" max="10" value="1" required onchange="calcTotal()">

                    <h3 style="color:var(--accent); margin-top:20px;">ยอดรวม: <span id="totalDisplay">฿<?= number_format($event['ticket_price']) ?></span></h3>

                    <label>แนบสลิปโอนเงิน</label>
                    <input type="file" name="slip" accept="image/*" required>

                    <button type="submit" class="btn-main" style="width:100%; margin-top:20px;">ยืนยันการแจ้งโอน</button>
                </form>
            </div>

            <div style="flex:1; display:flex; flex-direction:column; justify-content:center;">
                <div class="qr-section">
                    <h4>สแกนเพื่อจ่ายเงิน</h4>
                    <img src="public/QR-Code.png" class="qr-img">
                    <p style="margin-top:10px;">ธนาคาร: กสิกรไทย<br>เลขบัญชี: 206-8-78628-5<br>ชื่อ: บจก. ไนท์บาร์</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        const price = <?= $event['ticket_price'] ?>;

        function calcTotal() {
            let qty = document.getElementById('qty').value;
            let total = qty * price;
            document.getElementById('totalDisplay').innerText = '฿' + total.toLocaleString();
        }
    </script>
</body>

</html>