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

if (!$event)
    header("Location: tickets.php");

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
        body {
            overflow: hidden;
            /* Try to force no scroll if content fits */
            height: 100vh;
        }

        .container {
            height: calc(100vh - 80px);
            /* Adjust for navbar */
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 10px 20px;
        }

        h1 {
            margin: 0 0 15px 0;
            font-size: 1.8rem;
            text-align: center;
        }

        .checkout-box {
            display: flex;
            gap: 30px;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .form-group {
            margin-bottom: 12px;
        }

        label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #444;
        }

        input[type="text"],
        input[type="tel"],
        input[type="number"] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            box-sizing: border-box;
            background: #fcfcfc;
        }

        input:focus {
            border-color: #d35400;
            outline: none;
            background: #fff;
        }

        .upload-box {
            width: 100%;
            height: 120px;
            border: 2px dashed #ddd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #fafafa;
            flex-direction: column;
            gap: 5px;
            text-align: center;
        }

        .upload-box:hover {
            border-color: #d35400;
            background: #fff5f0;
        }

        .upload-box i {
            font-size: 2rem;
            color: #ccc;
            transition: color 0.3s;
        }

        .upload-box:hover i {
            color: #d35400;
        }

        .upload-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            display: none;
            border-radius: 12px;
        }

        .upload-text {
            color: #888;
            font-size: 0.8rem;
        }

        .qr-section {
            text-align: center;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            border: 1px solid #eee;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .qr-img {
            width: 180px;
            height: 180px;
            object-fit: contain;
            margin: 15px 0;
            border: 8px solid #f8f9fa;
            border-radius: 12px;
        }

        .btn-main {
            padding: 10px !important;
            font-size: 1rem !important;
            margin-top: 5px !important;
        }

        h3 {
            margin: 0 0 10px 0;
            font-size: 1.3rem;
        }
    </style>
</head>

<body>
    <?php require 'navbar.php'; ?>
    <div class="container">
        <h1>🛍️ ชำระเงิน</h1>
        <div class="checkout-box">
            <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                    <p style="margin:0;">ราคา: <strong>฿<?= number_format($event['ticket_price']) ?></strong></p>
                </div>

                <form method="post" enctype="multipart/form-data" id="bookingForm" style="margin-top:10px;">
                    <div class="form-group">
                        <label>ชื่อผู้จอง</label>
                        <input type="text" name="name" required placeholder="กรอกชื่อ-นามสกุล">
                    </div>

                    <div class="form-group">
                        <label>เบอร์โทรศัพท์</label>
                        <input type="tel" name="phone" required placeholder="08x-xxx-xxxx">
                    </div>

                    <div class="form-group">
                        <label>จำนวนบัตร</label>
                        <input type="number" id="qty" name="quantity" min="1" max="10" value="1" required
                            onchange="calcTotal()">
                    </div>

                    <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <span style="font-weight:500;">ยอดที่ต้องชำระ:</span>
                        <span id="totalDisplay"
                            style="color:var(--accent); font-size:1.4rem; font-weight:bold;">฿<?= number_format($event['ticket_price']) ?></span>
                    </div>

                    <div class="form-group">
                        <label>แนบสลิปโอนเงิน</label>
                        <label class="upload-box" for="slip">
                            <input type="file" name="slip" id="slip" accept="image/*" required
                                onchange="previewSlip(this)" style="display:none;">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span class="upload-text">คลิกอัปโหลดสลิป</span>
                            <img id="preview" src="#" alt="Preview">
                        </label>
                    </div>

                    <button type="submit" class="btn-main"
                        style="width:100%; margin-top:5px; padding: 12px; font-size: 1rem;">ยืนยันการแจ้งโอน</button>
                </form>
            </div>

            <div style="flex:1; display:flex; flex-direction:column; justify-content:center;">
                <div class="qr-section">
                    <h4 style="margin:0;">สแกนเพื่อจ่ายเงิน</h4>
                    <img src="public/QR-Code.png" class="qr-img">
                    <p style="margin:5px 0 0; font-size:0.9rem;">ธ.กสิกรไทย 206-8-78628-5<br>บจก. ไนท์บาร์</p>
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

        function previewSlip(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('preview').style.display = 'block';
                    document.querySelector('.upload-box i').style.display = 'none';
                    document.querySelector('.upload-text').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>