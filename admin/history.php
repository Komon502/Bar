<?php 
require '../db.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){ header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"><title>ประวัติการจอง</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin:0; background:#f4f6f9; font-family:'Kanit', sans-serif; }
        .admin-layout { display: flex; min-height: 100vh; }
        .content { flex: 1; padding: 30px; }
        .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; font-size:0.9rem; }
        th { background:#f8f9fa; color:#555; }
        .badge { padding:3px 8px; border-radius:10px; font-size:0.8rem; }
        .bg-green { background:#d4edda; color:#155724; }
        .bg-red { background:#f8d7da; color:#721c24; }
        .bg-yellow { background:#fff3cd; color:#856404; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require 'sidebar.php'; ?>
        <div class="content">
            <h2 style="color:#2c3e50; margin-top:0;">📜 ประวัติการจองทั้งหมด</h2>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>วันที่จอง</th><th>ลูกค้า</th><th>Email</th><th>งาน</th><th>โต๊ะ</th><th>ยอดเงิน</th><th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT b.*, e.title FROM bookings b JOIN events e ON b.event_id = e.id ORDER BY b.id DESC";
                        $stmt = $pdo->query($sql);
                        while($row = $stmt->fetch()){
                            $statusClass = 'bg-yellow';
                            if($row['status']=='confirmed') $statusClass='bg-green';
                            if($row['status']=='cancelled') $statusClass='bg-red';
                            
                            echo "<tr>
                                <td>#{$row['id']}</td>
                                <td>".date("d/m/Y H:i", strtotime($row['booking_date']))."</td>
                                <td>{$row['customer_name']}<br><small>{$row['customer_phone']}</small></td>
                                <td>{$row['customer_email']}</td>
                                <td>{$row['title']}</td>
                                <td>{$row['table_number']}</td>
                                <td>฿".number_format($row['total_price'])."</td>
                                <td><span class='badge $statusClass'>".strtoupper($row['status'])."</span></td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>