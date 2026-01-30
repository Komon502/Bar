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

        /* Carousel Styles */
        .carousel-wrapper-outer {
            position: relative;
            width: 100%;
            margin-bottom: 30px;
        }

        .carousel-container {
            overflow: hidden;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .carousel-wrapper {
            display: flex;
            transition: transform 0.6s ease-in-out;
        }

        .carousel-item {
            min-width: 100%;
            box-sizing: border-box;
            padding: 0 10px;
        }

        /* แก้ไข: ให้การ์ดมีขนาดเท่ากัน */
        .carousel-item .card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .carousel-item .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .carousel-item .card-content p {
            flex: 1;
        }

        /* Arrow Navigation - ไม่เด่น โปร่งแสง */
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.3);
            color: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(0, 0, 0, 0.1);
            width: 45px;
            height: 70px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 100;
            backdrop-filter: blur(5px);
        }

        .carousel-arrow:hover {
            background: rgba(230, 126, 34, 0.9);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
            transform: translateY(-50%) scale(1.05);
        }

        .carousel-arrow:active {
            transform: translateY(-50%) scale(0.95);
        }

        .carousel-arrow.prev {
            left: 0;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .carousel-arrow.next {
            right: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .carousel-dots {
            text-align: center;
            margin-top: 15px;
            display: none;
        }

        .dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ddd;
            margin: 0 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .dot.active {
            background: #e67e22;
            transform: scale(1.2);
        }

        @media (min-width: 768px) {
            .carousel-item {
                min-width: 50%;
            }
        }

        @media (min-width: 1024px) {
            .carousel-item {
                min-width: 33.333%;
            }
        }

        @media (max-width: 768px) {
            .carousel-arrow {
                width: 40px;
                height: 60px;
                font-size: 1.2rem;
            }
        }
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
    </div>

    <?php
    // ดึงข้อมูลจากตาราง promotions
    $stmt = $pdo->query("SELECT * FROM promotions ORDER BY id DESC");
    $promotions = $stmt->fetchAll();
    ?>

    <!-- Carousel อยู่นอก container เพื่อให้ปุ่มชิดขอบจอ -->
    <div class="carousel-wrapper-outer">
        <button class="carousel-arrow prev" onclick="prevSlide()">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <div class="carousel-container">
            <div class="carousel-wrapper" id="promoCarousel">
                <?php
                if (count($promotions) > 0) {
                    foreach ($promotions as $row) {
                        // เช็ค path รูปภาพ
                        $img = $row['image_url'] ? $row['image_url'] : 'https://via.placeholder.com/500x300?text=No+Image';
                        
                        // เตรียมข้อมูลสำหรับ JavaScript - ใช้ json_encode เพื่อป้องกัน XSS
                        $jsData = json_encode([
                            'img' => $img,
                            'title' => $row['title'],
                            'desc' => $row['details']
                        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

                        echo '<div class="carousel-item">';
                        echo '<div class="card promo-card" onclick=\'openModalSafe(' . $jsData . ')\'>';
                        echo '<img src="' . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . '" style="width:100%; height:200px; object-fit:cover;">';
                        echo '<div class="card-content">';
                        echo '<span style="background:#e67e22; color:#fff; padding:3px 8px; border-radius:4px; font-size:0.8rem;">Promotion</span>';
                        echo '<h3 style="margin:10px 0 5px;">' . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . '</h3>';
                        echo '<p style="color:#666; font-size:0.9rem;">' . mb_strimwidth(htmlspecialchars($row['details'], ENT_QUOTES, 'UTF-8'), 0, 80, '...') . '</p>';
                        echo '</div></div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p style="text-align:center; color:#999; padding:40px 0; width:100%;">ยังไม่มีโปรโมชั่นขณะนี้</p>';
                }
                ?>
            </div>
        </div>
        
        <button class="carousel-arrow next" onclick="nextSlideManual()">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <div class="carousel-dots" id="carouselDots"></div>
    </div>

    <div class="container">
        

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
        // ฟังก์ชันใหม่ที่รับ JSON object แทนการรับ string แยก
        function openModalSafe(data) {
            document.getElementById('modalImg').src = data.img;
            document.getElementById('modalTitle').textContent = data.title;
            document.getElementById('modalDesc').textContent = data.desc;
            
            document.getElementById('promoModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('promoModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function closeModalOutside(e) {
            if (e.target == document.getElementById('promoModal')) {
                closeModal();
            }
        }

        // Carousel Auto-Scroll
        const carousel = document.getElementById('promoCarousel');
        const dotsContainer = document.getElementById('carouselDots');
        
        if (carousel) {
            const items = carousel.querySelectorAll('.carousel-item');
            const totalItems = items.length;
            let currentIndex = 0;
            let autoScrollInterval;

            // สร้าง dots
            items.forEach((_, index) => {
                const dot = document.createElement('span');
                dot.className = 'dot' + (index === 0 ? ' active' : '');
                dot.onclick = () => goToSlide(index);
                dotsContainer.appendChild(dot);
            });

            const dots = dotsContainer.querySelectorAll('.dot');

            function updateCarousel() {
                // คำนวณ offset สำหรับหน้าจอขนาดต่างๆ
                let itemsPerView = 1;
                if (window.innerWidth >= 1024) itemsPerView = 3;
                else if (window.innerWidth >= 768) itemsPerView = 2;

                const offset = -(currentIndex * (100 / itemsPerView));
                carousel.style.transform = `translateX(${offset}%)`;

                // Update dots
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentIndex);
                });
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % totalItems;
                updateCarousel();
            }

            function prevSlide() {
                currentIndex = (currentIndex - 1 + totalItems) % totalItems;
                updateCarousel();
                resetAutoScroll();
            }

            function nextSlideManual() {
                currentIndex = (currentIndex + 1) % totalItems;
                updateCarousel();
                resetAutoScroll();
            }

            function goToSlide(index) {
                currentIndex = index;
                updateCarousel();
                resetAutoScroll();
            }

            function resetAutoScroll() {
                clearInterval(autoScrollInterval);
                autoScrollInterval = setInterval(nextSlide, 5000); // เลื่อนทุก 5 วินาที
            }

            // เริ่ม auto-scroll
            resetAutoScroll();

            // Update on window resize
            window.addEventListener('resize', updateCarousel);

            // หยุดเมื่อ hover (optional)
            carousel.addEventListener('mouseenter', () => clearInterval(autoScrollInterval));
            carousel.addEventListener('mouseleave', resetAutoScroll);
        }
    </script>
</body>
</html>