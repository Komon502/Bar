<?php
require '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $date = $_POST['event_date'];
    $price = $_POST['ticket_price'];

    $image_path = "";
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $target_dir = "../uploads/";
        $filename = uniqid() . "_" . basename($_FILES["event_image"]["name"]);
        if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $target_dir . $filename)) {
            $image_path = "uploads/" . $filename;
        }
    }

    $sql = "INSERT INTO events (title, description, event_date, ticket_price, image_url) VALUES (?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$title, $desc, $date, $price, $image_path]);
    echo "<script>alert('เพิ่มงานเรียบร้อย'); window.location='index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มกิจกรรม - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-nav {
            background: #2c3e50;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .admin-nav a {
            color: #ecf0f1;
            text-decoration: none;
        }

        .form-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            max-width: 700px;
            margin: 40px auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--accent);
            outline: none;
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div>🔒 Admin: เพิ่มกิจกรรมใหม่</div>
        <a href="index.php">← กลับ Dashboard</a>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:15px;">📝 รายละเอียดกิจกรรม</h2>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>ชื่องาน / คอนเสิร์ต</label>
                    <input type="text" name="title" required placeholder="เช่น Jazz Night Live">
                </div>

                <div class="form-group">
                    <label>รายละเอียด</label>
                    <textarea name="description" rows="5" placeholder="รายละเอียดเกี่ยวกับงาน..."></textarea>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div class="form-group">
                        <label>วันและเวลาจัดงาน</label>
                        <input type="datetime-local" name="event_date" required>
                    </div>
                    <div class="form-group">
                        <label>ราคาตั๋ว (บาท)</label>
                        <input type="number" name="ticket_price" required placeholder="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label>รูปภาพโปสเตอร์ (ขนาดแนะนำ 800x500px)</label>
                    <input type="file" name="event_image" accept="image/*" style="padding: 10px; background: #f9f9f9;">
                </div>

                <button type="submit" class="btn-main" style="width:100%; font-size:1.1rem; margin-top:10px;">บันทึกกิจกรรม</button>
            </form>
        </div>
    </div>
</body>

</html>