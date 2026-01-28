<?php 
require 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"><title>ตั๋วของฉัน</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <style>
        .ticket-box { background:#fff; padding:20px; border-radius:15px; margin-bottom:20px; box-shadow:0 4px 15px rgba(0,0,0,0.05); position: relative; overflow:hidden; border: 1px solid #eee; }
        .ticket-header { border-bottom:2px dashed #eee; padding-bottom:15px; margin-bottom:15px; }
        .status-badge { float:right; padding:5px 15px; border-radius:20px; font-size:0.8rem; color:#fff; }
        .bg-conf { background:#2ecc71; } .bg-pend { background:#f39c12; }
        .ticket-codes { background:#2c3e50; color:#fff; padding:15px; border-radius:10px; text-align:center; margin-top:15px; letter-spacing:1px; }
        .dl-btn { background:#3498db; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; margin-top:10px; font-size:0.9rem; transition:0.3s; }
        .dl-btn:hover { background:#2980b9; }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>
    <div class="container">
        <h2 style="text-align:center; margin-bottom:30px;">🎫 ตั๋วของฉัน</h2>
        
        <?php
        $sql = "SELECT b.*, e.title, e.event_date, e.image_url FROM bookings b JOIN events e ON b.event_id = e.id WHERE b.user_id = ? ORDER BY b.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        
        while($b = $stmt->fetch()){
            $status = $b['status'];
            $statusText = ($status=='confirmed') ? 'ชำระเงินแล้ว' : 'รอตรวจสอบ';
            $bg = ($status=='confirmed') ? 'bg-conf' : 'bg-pend';
            
            // ID สำหรับอ้างอิงตอนดาวน์โหลด
            $ticketDivID = "ticket_".$b['id'];
            
            echo "<div class='ticket-box' id='$ticketDivID'>";
            echo "<div class='ticket-header'>";
            echo "<span class='status-badge $bg'>$statusText</span>";
            echo "<h3 style='margin:0;'>{$b['title']}</h3>";
            echo "<p style='color:#777; margin:5px 0;'><i class='fas fa-calendar'></i> ".date("d M Y H:i", strtotime($b['event_date']))."</p>";
            echo "</div>";
            
            echo "<div style='display:flex; justify-content:space-between; font-size:0.95rem;'>";
            echo "<div><strong>ชื่อ:</strong> {$b['customer_name']}</div>";
            echo "<div><strong>โต๊ะ:</strong> {$b['table_number']}</div>"; // แสดงเลขโต๊ะ
            echo "<div><strong>จำนวน:</strong> {$b['quantity']} ใบ</div>";
            echo "</div>";

            if($status == 'confirmed'){
                echo "<div class='ticket-codes'>";
                echo "<small>Ticket ID:</small><br>";
                $codes = $pdo->prepare("SELECT ticket_code FROM ticket_items WHERE booking_id = ?");
                $codes->execute([$b['id']]);
                $codeList = [];
                while($c = $codes->fetch()) $codeList[] = $c['ticket_code'];
                echo implode(" , ", $codeList);
                echo "</div>";
                
                // ปุ่มดาวน์โหลด (เพิ่ม data-html2canvas-ignore เพื่อไม่ให้ปุ่มติดไปในรูป)
                echo "<div style='text-align:center; margin-top:15px;' data-html2canvas-ignore='true'>";
                echo "<button onclick=\"downloadTicket('$ticketDivID', 'Ticket-{$b['id']}')\" class='dl-btn'><i class='fas fa-download'></i> ดาวน์โหลดรูปภาพ</button>";
                echo "</div>";
            }
            echo "</div>";
        }
        ?>
    </div>

    <script>
        function downloadTicket(divId, fileName) {
            const element = document.getElementById(divId);
            // ใช้ html2canvas แปลง div เป็นรูป
            html2canvas(element, { scale: 2 }).then(canvas => {
                const link = document.createElement('a');
                link.download = fileName + '.jpg';
                link.href = canvas.toDataURL('image/jpeg');
                link.click();
            });
        }
    </script>
</body>
</html>