<?php
require '../db.php';

// เช็คสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Logic คำนวณต่างๆ
$currentMonth = date('m');
$revenue = $pdo->prepare("SELECT SUM(total_price) FROM bookings WHERE status = 'confirmed' AND MONTH(booking_date) = ?")->execute([$currentMonth]) ? $pdo->query("SELECT @revenue")->fetchColumn() : 0;
// *หมายเหตุ: เพื่อความชัวร์ใช้ query แบบเดิมด้านล่างนี้ครับ*
$revStmt = $pdo->prepare("SELECT SUM(total_price) as total FROM bookings WHERE status = 'confirmed' AND MONTH(booking_date) = ?");
$revStmt->execute([$currentMonth]);
$revenue = $revStmt->fetch()['total'] ?? 0;

$pendingCount = $pdo->query("SELECT count(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$eventCount = $pdo->query("SELECT count(*) FROM events")->fetchColumn();
$pendingTableCount = $pdo->query("SELECT count(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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

        /* สไตล์ของการ์ดและตารางในหน้านี้ */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .card-green {
            border-left: 5px solid #2ecc71;
        }

        .card-blue {
            border-left: 5px solid #3498db;
        }

        .card-orange {
            border-left: 5px solid #f39c12;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 0.95rem;
            color: #7f8c8d;
            font-weight: normal;
        }

        .stat-info .number {
            font-size: 2.2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 5px;
        }

        .stat-icon {
            font-size: 4rem;
            opacity: 0.1;
            position: absolute;
            right: 20px;
            bottom: 10px;
        }

        .table-container {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            background: #f8f9fa;
            color: #7f8c8d;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #2c3e50;
        }

        .btn-check {
            background: #e8f8f5;
            color: #27ae60;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .btn-check:hover {
            background: #27ae60;
            color: white;
        }
    </style>
</head>

<body>

    <div class="admin-layout">

        <?php require 'sidebar.php'; ?>

        <div class="content">
            <h2 style="color:#2c3e50; margin-top:0;">📊 ภาพรวมระบบ</h2>
            <p style="color:#7f8c8d; margin-bottom:30px;">ยินดีต้อนรับ, <?= $_SESSION['username'] ?? 'Admin' ?></p>

            <div class="stat-grid">
                <div class="stat-card card-green">
                    <div class="stat-info">
                        <h3>ยอดขายเดือนนี้ (<?= date('M') ?>)</h3>
                        <div class="number">฿<?= number_format($revenue) ?></div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-wallet" style="color:#2ecc71;"></i></div>
                </div>
                <div class="stat-card card-blue">
                    <div class="stat-info">
                        <h3>กิจกรรมทั้งหมด</h3>
                        <div class="number"><?= $eventCount ?> งาน</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-music" style="color:#3498db;"></i></div>
                </div>
                <div class="stat-card card-orange">
                    <div class="stat-info">
                        <h3>รอตรวจสอบสลิป</h3>
                        <div class="number" style="color:#e67e22;"><?= $pendingCount ?> รายการ</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-clock" style="color:#f39c12;"></i></div>
                </div>
                <div class="stat-card" style="border-left: 5px solid #9b59b6;">
                    <div class="stat-info">
                        <h3>จองโต๊ะค้างตรวจสอบ</h3>
                        <div class="number" style="color:#9b59b6;"><?= $pendingTableCount ?> โต๊ะ</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-calendar-check" style="color:#9b59b6;"></i></div>
                </div>
            </div>

            <div class="table-container">
                <h3 style="margin-top:0;">🕒 รายการจองล่าสุด</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ลูกค้า</th>
                            <th>ยอดเงิน</th>
                            <th>หลักฐาน</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require '../csrf_helper.php';
                        $stmt = $pdo->query("SELECT * FROM bookings WHERE status = 'pending' ORDER BY id DESC LIMIT 5");
                        if ($stmt->rowCount() == 0) {
                            echo "<tr><td colspan='5' style='text-align:center; padding:30px; color:#aaa;'>ไม่มีรายการค้างตรวจสอบ</td></tr>";
                        } else {
                            while ($row = $stmt->fetch()) {
                                $csrf_token = CSRF::generateToken();
                                echo "<tr>";
                                echo "<td>#{$row['id']}</td>";
                                echo "<td><strong>" . htmlspecialchars($row['customer_name'], ENT_QUOTES, 'UTF-8') . "</strong><br><small>" . htmlspecialchars($row['customer_phone'], ENT_QUOTES, 'UTF-8') . "</small></td>";
                                echo "<td>฿" . number_format($row['total_price']) . "</td>";
                                echo "<td><a href='../" . htmlspecialchars($row['payment_slip'], ENT_QUOTES, 'UTF-8') . "' target='_blank' style='color:#3498db;'>ดูสลิป</a></td>";
                                echo "<td><a href='verify_action.php?id={$row['id']}&action=approve&csrf_token={$csrf_token}' class='btn-check' onclick='return confirm(\"ยืนยันการอนุมัติ?\")'>อนุมัติ</a></td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>