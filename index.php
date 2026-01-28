<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>NightBar - หน้าแรก</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS สำหรับ Modal Popup --- */
        .modal {
            display: none; 
            position: fixed; z-index: 9999; 
            left: 0; top: 0; width: 100%; height: 100%; 
            background-color: rgba(0,0,0,0.85); 
            justify-content: center; align-items: center;
            backdrop-filter: blur(5px);
            padding: 20px; box-sizing: border-box;
        }

        .modal-content {
            background-color: #fff;
            border-radius: 12px;
            max-width: 500px; width: 100%;
            position: relative;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
            overflow: hidden;
            animation: popUp 0.3s ease-out;
        }

        @keyframes popUp { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .modal-img-container { width: 100%; height: 300px; background: #000; position: relative; }
        .modal-img { width: 100%; height: 100%; object-fit: contain; }
        
        .modal-close {
            position: absolute; top: 10px; right: 10px;
            background: rgba(0,0,0,0.6); color: #fff;
            width: 35px; height: 35px; border-radius: 50%;
            border: none; cursor: pointer; font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center;
            transition: 0.2s; z-index: 10;
        }
        .modal-close:hover { background: #e74c3c; transform: rotate(90deg); }

        .modal-body { padding: 25px; text-align: left; }
        .modal-title { margin: 0 0 10px; color: #e67e22; font-size: 1.5rem; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .modal-desc { color: #555; line-height: 1.6; font-size: 1rem; white-space: pre-wrap; }

        /* การ์ดโปรโมชั่น */
        .promo-card { cursor: pointer; transition: transform 0.3s; }
        .promo-card:hover { transform: translateY(-7px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    </style>
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
            // ดึงข้อมูลจากตาราง promotions
            $stmt = $pdo->query("SELECT * FROM promotions ORDER BY id DESC");
            
            // ถ้าไม่มีข้อมูลให้แสดงข้อความ
            if ($stmt->rowCount() == 0) {
                echo "<p style='color:#999; text-align:center; width:100%; grid-column:1/-1;'>ยังไม่มีโปรโมชั่นขณะนี้</p>";
            }

            while ($row = $stmt->fetch()) {
                // เช็ค path รูปภาพ
                $img = $row['image_url'] ? $row['image_url'] : 'https://via.placeholder.com/500x300?text=No+Image';
                
                // เตรียมข้อมูลสำหรับส่งเข้า JS Popup (ป้องกันอักขระพิเศษ)
                $jsTitle = htmlspecialchars($row['title'], ENT_QUOTES);
                $jsDesc = htmlspecialchars($row['details'], ENT_QUOTES);
                $jsImg = $img;

                echo '<div class="card promo-card" onclick="openModal(\''.$jsImg.'\', \''.$jsTitle.'\', \''.$jsDesc.'\')">';
                echo '<img src="' . $img . '" style="width:100%; height:200px; object-fit:cover;">';
                echo '<div class="card-content">';
                echo '<span style="background:#e67e22; color:#fff; padding:3px 8px; border-radius:4px; font-size:0.8rem;">Promotion</span>';
                echo '<h3 style="margin:10px 0 5px;">' . htmlspecialchars($row['title']) . '</h3>';
                echo '<p style="color:#666; font-size:0.9rem;">' . mb_strimwidth(htmlspecialchars($row['details']), 0, 80, '...') . '</p>';
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

    <div id="promoModal" class="modal" onclick="closeModalOutside(event)">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            <div class="modal-img-container">
                <img id="modalImg" src="" class="modal-img">
            </div>
            <div class="modal-body">
                <h3 id="modalTitle" class="modal-title"></h3>
                <div id="modalDesc" class="modal-desc"></div>
            </div>
        </div>
    </div>

    <script>
        function openModal(img, title, desc) {
            document.getElementById('modalImg').src = img;
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalDesc').innerText = desc;
            
            document.getElementById('promoModal').style.display = 'flex';
            document.body.style.overflow = 'hidden'; // ล็อก Scroll
        }

        function closeModal() {
            document.getElementById('promoModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // ปลดล็อก Scroll
        }

        function closeModalOutside(e) {
            if (e.target == document.getElementById('promoModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>