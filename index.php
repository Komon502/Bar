<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>NightBar - หน้าแรก</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div class="hero">
        <div>
            <h1>NightBar & Bistro</h1>
            <p>ดื่มด่ำกับบรรยากาศ ดนตรีสด และเครื่องดื่มสูตรพิเศษ</p>
        </div>
    </div>

    <div class="container">
        
        <h2 class="section-title">🔥 โปรโมชั่นแนะนำ</h2>
        <div class="grid-box">
            <?php
            // ดึงข้อมูลโปรโมชั่นจากตาราง promotions
            $stmt = $pdo->query("SELECT * FROM promotions ORDER BY id DESC");
            
            // ถ้าไม่มีข้อมูล ให้แสดงข้อความแจ้ง
            if ($stmt->rowCount() == 0) {
                echo "<p style='color:#999; text-align:center; width:100%;'>ยังไม่มีโปรโมชั่นขณะนี้</p>";
            }

            while ($row = $stmt->fetch()) {
                // เช็คว่ามีรูปไหม ถ้าไม่มีใช้รูป placeholder
                $img = $row['image_url'] ? $row['image_url'] : 'https://via.placeholder.com/500x300?text=No+Image';
                
                echo '<div class="card">';
                echo '<img src="' . $img . '" style="width:100%; height:200px; object-fit:cover;">';
                echo '<div class="card-content">';
                echo '<span style="background:#e67e22; color:#fff; padding:3px 8px; border-radius:4px; font-size:0.8rem;">Promotion</span>';
                echo '<h3 style="margin:10px 0 5px;">' . htmlspecialchars($row['title']) . '</h3>';
                echo '<p style="color:#666; font-size:0.9rem;">' . htmlspecialchars($row['details']) . '</p>';
                echo '</div></div>';
            }
            ?>
        </div>

        <h2 class="section-title" style="margin-top:50px;">🎸 คอนเสิร์ตเร็วๆ นี้</h2>
        <div class="grid-box">
            <?php
            $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC LIMIT 3");
            while ($row = $stmt->fetch()) {
                $img = $row['image_url'] ? $row['image_url'] : 'https://via.placeholder.com/500x300?text=Event';
                echo '<div class="card">';
                echo '<img src="' . $img . '" style="width:100%; height:200px; object-fit:cover;">';
                echo '<div class="card-content">';
                echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                echo '<p style="color:#888;">' . date("d M Y H:i", strtotime($row['event_date'])) . '</p>';
                echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px;">';
                echo '<span class="price-tag" style="color:#2ecc71; font-weight:bold; font-size:1.1rem;">฿' . number_format($row['ticket_price']) . '</span>';
                echo '<a href="booking.php?event_id=' . $row['id'] . '" class="btn-main" style="background:#333; color:#fff; padding:8px 15px; text-decoration:none; border-radius:5px;">ซื้อตั๋ว</a>';
                echo '</div></div></div>';
            }
            ?>
        </div>

        <div style="text-align:center; margin-top:30px;">
            <a href="tickets.php" class="btn-main" style="background:transparent; border:1px solid #333; color:#333; padding:10px 20px; text-decoration:none; border-radius:5px;">ดูตั๋วคอนเสิร์ตทั้งหมด →</a>
        </div>
    </div>

    <footer style="background:#333; color:#fff; text-align:center; padding:20px; margin-top:50px;">
        <p>© 2024 NightBar Booking System</p>
    </footer>
</body>
</html>