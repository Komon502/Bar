<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Tickets - จองบัตร</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .ticket-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
            border: 1px solid #f0f0f0;
        }

        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .ticket-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .ticket-body {
            padding: 25px;
        }

        .ticket-meta {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .badge-available {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-full {
            background: #ffebee;
            color: #c62828;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div class="container">
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="font-weight: 600; color: #333;">Upcoming Events</h1>
            <p style="color: #666;">เลือกงานที่คุณอยากไปสนุก แล้วจองเลย!</p>
        </div>

        <div class="ticket-grid">
            <?php
            $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
            while ($row = $stmt->fetch()) {
                $available = $row['max_tickets'] - $row['current_sold'];
                $isSoldOut = $available <= 0;
                $img = $row['image_url'] ? $row['image_url'] : 'https://via.placeholder.com/600x400';

                echo '<div class="ticket-card">';
                echo '<img src="' . $img . '" class="ticket-img">';
                echo '<div class="ticket-body">';
                echo '<div class="ticket-meta">';
                echo '<span>📅 ' . date("d M Y", strtotime($row['event_date'])) . '</span>';
                echo '<span>⏰ ' . date("H:i", strtotime($row['event_date'])) . ' น.</span>';
                echo '</div>';

                echo '<h3 style="margin: 0 0 10px; font-size: 1.3rem;">' . htmlspecialchars($row['title']) . '</h3>';
                echo '<p style="color:#666; font-size:0.95rem; margin-bottom:20px;">' . mb_strimwidth($row['description'], 0, 80, "...") . '</p>';

                echo '<div style="display:flex; justify-content:space-between; align-items:center;">';
                echo '<div>';
                echo '<span style="font-size:1.4rem; font-weight:bold; color:var(--primary);">฿' . number_format($row['ticket_price']) . '</span>';
                echo '</div>';

                if ($isSoldOut) {
                    echo '<span class="badge-full">Sold Out</span>';
                } else {
                    echo '<a href="booking.php?event_id=' . $row['id'] . '" class="btn-main">ซื้อตั๋ว</a>';
                }
                echo '</div>';

                echo '<div style="margin-top:10px; font-size:0.8rem; color:#aaa;">เหลือ ' . $available . ' ใบ</div>';
                echo '</div></div>';
            }
            ?>
        </div>
    </div>
</body>

</html>