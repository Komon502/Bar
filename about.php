<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เกี่ยวกับเรา - NightBar</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .about-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1514933651103-005eec06c04b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1934&q=80');
            background-position: center;
            background-size: cover;
            height: 350px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-bottom: 50px;
        }

        .about-hero h1 {
            font-size: 3rem;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .about-hero p {
            font-size: 1.2rem;
            opacity: 0.8;
            margin-top: 10px;
        }

        .story-section {
            display: flex;
            gap: 50px;
            align-items: center;
            margin-bottom: 80px;
        }

        .story-img {
            flex: 1;
            height: 400px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .story-text {
            flex: 1;
        }

        .story-text h2 {
            font-size: 2.2rem;
            color: #d35400;
            /* accent color */
            margin-bottom: 20px;
        }

        .story-text p {
            font-size: 1.1rem;
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }

        .feature-item {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .feature-item:hover {
            transform: translateY(-5px);
        }

        .feature-item i {
            font-size: 3rem;
            color: #d35400;
            margin-bottom: 20px;
        }

        .feature-item h3 {
            margin: 0 0 10px;
            font-size: 1.4rem;
        }

        .feature-item p {
            color: #666;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .story-section {
                flex-direction: column;
            }

            .about-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div class="about-hero">
        <div>
            <h1>The NightBar Story</h1>
            <p>มากกว่าร้านเหล้า คือพื้นที่แห่งความสุขและดนตรี</p>
        </div>
    </div>

    <div class="container">
        <div class="story-section">
            <img src="https://images.unsplash.com/photo-1572116469696-9587215f2faa?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                class="story-img" alt="NightBar Atmosphere">
            <div class="story-text">
                <h2>จุดเริ่มต้นของเรา</h2>
                <p>NightBar ก่อตั้งขึ้นในปี 2023 จากความหลงใหลในเสียงดนตรีและบรรยากาศยามค่ำคืน
                    เราตั้งใจสร้างพื้นที่ที่ทุกคนสามารถปลดปล่อยความเหนื่อยล้า และเติมพลังด้วยความสุข</p>
                <p>เราคัดสรรวงดนตรีสดฝีมือดี อาหารรสเลิศ และเครื่องดื่มสูตรพิเศษ
                    เพื่อให้ทุกค่ำคืนของคุณเป็นคืนที่น่าจดจำ ไม่ว่าจะมาสังสรรค์กับเพื่อนฝูง หรือมานั่งชิลคนเดียว
                    คุณจะพบกับความอบอุ่นที่เป็นกันเองเสมอ</p>
            </div>
        </div>

        <div style="text-align:center; margin-bottom:40px;">
            <h2 style="font-size:2rem; color:#333;">สิ่งที่คุณจะได้รับจากเรา</h2>
            <p style="color:#666;">เราใส่ใจในทุกรายละเอียดเพื่อประสบการณ์ที่ดีที่สุด</p>
        </div>

        <div class="features-grid">
            <div class="feature-item">
                <i class="fas fa-music"></i>
                <h3>ดนตรีสดคุณภาพ</h3>
                <p>พบกับวงดนตรีสดหลากหลายแนว ทั้ง Pop, Jazz, และ Rock
                    ที่จะหมุนเวียนมาสร้างความสุขให้คุณทุกค่ำคืนวันศุกร์ถึงอาทิตย์</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-glass-cheers"></i>
                <h3>เครื่องดื่ม SIGNATURE</h3>
                <p>ค็อกเทลสูตรพิเศษที่คิดค้นโดย Bartender มืออาชีพ พร้อมเบียร์นำเข้าและวิสกี้ชั้นดีจากทั่วโลก</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-utensils"></i>
                <h3>อาหารรสเลิศ</h3>
                <p>เมนูอาหาร Fusion ที่ผสมผสานรสชาติไทยและตะวันตกอย่างลงตัว เหมาะสำหรับทานเล่นหรือทานจริงจัง</p>
            </div>
        </div>

        <!-- Google Map Embed (Placeholder for location context) -->
        <div style="margin-bottom: 50px;">
            <h2 style="text-align:center; margin-bottom:20px;">พบกับเราได้ที่</h2>
            <div
                style="width:100%; height:400px; background:#eee; display:flex; align-items:center; justify-content:center; border-radius:15px; color:#888;">
                <i class="fas fa-map-marker-alt" style="font-size:2rem; margin-right:10px;"></i> แผนที่ร้าน NightBar
                (ซอยสุขุมวิท XX)
            </div>
        </div>

    </div>
</body>

</html>