<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php require 'navbar.php'; ?>
    <div class="container">
        <h1>🎫 ตั๋วของฉัน (History)</h1>
        <?php
        $sql = "SELECT b.*, e.title, e.event_date 
                FROM bookings b 
                JOIN events e ON b.event_id = e.id 
                WHERE b.user_id = ? ORDER BY b.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);

        while ($booking = $stmt->fetch()) {
            $status_color = ($booking['status'] == 'confirmed') ? '#d4edda' : '#fff3cd';

            echo '<div class="card" style="margin-bottom:20px; padding:20px; border-left: 5px solid ' . $status_color . ';">';
            echo '<div style="display:flex; justify-content:space-between;">';
            echo '<h3>' . $booking['title'] . ' <span style="font-size:0.8rem; background:' . $status_color . '; padding:3px 8px; border-radius:10px;">' . strtoupper($booking['status']) . '</span></h3>';
            echo '<span>ยอดเงิน: ฿' . number_format($booking['total_price']) . '</span>';
            echo '</div>';
            echo '<p>📅 วันที่: ' . date("d M Y H:i", strtotime($booking['event_date'])) . '</p>';

            if ($booking['status'] == 'confirmed') {
                echo '<hr style="border-top:1px dashed #ddd; margin:10px 0;">';
                echo '<h4>รหัสเข้างาน (Ticket Codes):</h4>';
                echo '<div style="display:flex; gap:10px; flex-wrap:wrap;">';

                // ดึงเลขตั๋วของ booking นี้
                $sub_stmt = $pdo->prepare("SELECT ticket_code FROM ticket_items WHERE booking_id = ?");
                $sub_stmt->execute([$booking['id']]);
                while ($ticket = $sub_stmt->fetch()) {
                    echo '<span style="background:#333; color:#fff; padding:5px 15px; border-radius:5px; font-family:monospace; font-size:1.1rem;">' . $ticket['ticket_code'] . '</span>';
                }
                echo '</div>';
            } else {
                echo '<p style="color:#e67e22;">⏳ กำลังตรวจสอบสลิปโอนเงิน...</p>';
            }
            echo '</div>';
        }
        ?>
    </div>
</body>

</html>