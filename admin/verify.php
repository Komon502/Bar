<?php
require '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    if ($action == 'approve') {
        $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?")->execute([$id]);
    } elseif ($action == 'reject') {
        $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?")->execute([$id]);
    }
    header("Location: verify.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ตรวจสลิป</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            background-color: #f4f6f9;
            font-family: 'Kanit', sans-serif;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .verify-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .verify-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-top: 4px solid #f39c12;
        }

        .card-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            color: #555;
        }

        .card-body {
            padding: 15px;
        }

        .slip-thumb {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            border: 1px solid #ddd;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 0.9rem;
        }

        .btn-yes {
            background: #27ae60;
        }

        .btn-no {
            background: #e74c3c;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
        }

        .modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 5px;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require 'sidebar.php'; ?>
        <div class="content">
            <h2 style="color:#2c3e50; margin-top:0;">📝 รายการรอตรวจสอบ</h2>
            <div class="verify-grid">
                <?php
                $stmt = $pdo->query("SELECT b.*, e.title FROM bookings b JOIN events e ON b.event_id = e.id WHERE b.status = 'pending' ORDER BY b.id ASC");
                if ($stmt->rowCount() == 0) echo "<p style='color:#888;'>ไม่มีรายการค้างตรวจสอบ</p>";
                while ($row = $stmt->fetch()): ?>
                    <div class="verify-card">
                        <div class="card-header">
                            <span>#<?= $row['id'] ?></span>
                            <small><?= date("d/m H:i", strtotime($row['booking_date'])) ?></small>
                        </div>
                        <div class="card-body">
                            <h4 style="margin:0 0 10px;"><?= $row['title'] ?></h4>
                            <p style="margin:5px 0;">👤 <?= $row['customer_name'] ?></p>
                            <p style="margin:5px 0;">📞 <?= $row['customer_phone'] ?></p>
                            <p style="margin:5px 0;">💰 <b style="color:#27ae60;">฿<?= number_format($row['total_price']) ?></b></p>
                            <img src="../<?= $row['payment_slip'] ?>" class="slip-thumb" onclick="openModal(this.src)">
                            <div class="btn-group">
                                <a href="?action=approve&id=<?= $row['id'] ?>" class="btn btn-yes" onclick="return confirm('ยืนยัน?')">อนุมัติ</a>
                                <a href="?action=reject&id=<?= $row['id'] ?>" class="btn btn-no" onclick="return confirm('ปฏิเสธ?')">ปฏิเสธ</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <div id="imgModal" class="modal" onclick="this.style.display='none'"><span class="close-modal">&times;</span><img id="modalImg" src=""></div>
    <script>
        function openModal(src) {
            document.getElementById('modalImg').src = src;
            document.getElementById('imgModal').style.display = "flex";
        }
    </script>
</body>

</html>