<?php
require '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['add_event'])) {
    $prefix = strtoupper($_POST['prefix']);
    $image_path = "";
    if (isset($_FILES['event_img']) && $_FILES['event_img']['error'] == 0) {
        $ext = pathinfo($_FILES['event_img']['name'], PATHINFO_EXTENSION);
        $new_name = "evt_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['event_img']['tmp_name'], "../uploads/" . $new_name);
        $image_path = "uploads/" . $new_name;
    }
    $sql = "INSERT INTO events (title, description, event_date, ticket_price, image_url, prefix, start_num, max_tickets) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$_POST['title'], $_POST['desc'], $_POST['date'], $_POST['price'], $image_path, $prefix, $_POST['start_num'], $_POST['max']]);
    header("Location: manage_events.php");
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_events.php");
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการกิจกรรม</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            background-color: #f4f6f9;
            font-family: 'Kanit', sans-serif;
            overflow: hidden;
            /* Prevent full page scroll */
            height: 100vh;
        }

        .admin-layout {
            display: flex;
            height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            display: flex;
            gap: 20px;
            height: calc(100vh - 40px);
            /* Padding compensation */
            box-sizing: border-box;
        }

        /* --- Left Side: Table List --- */
        .list-section {
            flex: 6;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .list-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .table-container {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            color: #555;
            z-index: 10;
            font-size: 0.9rem;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background-color: #fcfcfc;
        }

        /* --- Right Side: Add Form --- */
        .form-section {
            flex: 4;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .form-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            display: inline-block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 0.9rem;
        }

        input:focus,
        textarea:focus {
            border-color: #3498db;
            outline: none;
        }

        .btn-add {
            background: #3498db;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .btn-add:hover {
            background: #2980b9;
        }

        /* Compact Upload Box */
        .upload-box {
            width: 100%;
            height: 100px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #fafafa;
            flex-direction: row;
            gap: 10px;
        }

        .upload-box:hover {
            border-color: #3498db;
            background: #f0f7fb;
        }

        .upload-box i {
            font-size: 1.5rem;
            color: #ccc;
        }

        .upload-box span {
            font-size: 0.85rem;
            color: #888;
        }

        .upload-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            display: none;
        }

        /* Progress Bar */
        .bar-bg {
            background: #eee;
            height: 6px;
            border-radius: 3px;
            width: 80px;
            margin-top: 5px;
        }

        .bar-fill {
            height: 100%;
            background: #2ecc71;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Left: Event List -->
            <div class="list-section">
                <div class="list-header">
                    <h2 style="margin:0; font-size:1.4rem; color:#2c3e50;"><i class="fas fa-calendar-alt"></i>
                        รายการกิจกรรม</h2>
                    <span style="font-size:0.9rem; color:#888;">ทั้งหมด
                        <?= $pdo->query("SELECT count(*) FROM events")->fetchColumn() ?> รายการ</span>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ชื่องาน</th>
                                <th>วันที่</th>
                                <th>Config</th>
                                <th>ยอดขาย</th>
                                <th>ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
                            while ($row = $stmt->fetch()):
                                $pct = ($row['max_tickets'] > 0) ? ($row['current_sold'] / $row['max_tickets']) * 100 : 0;
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600; color:#333;"><?= $row['title'] ?></div>
                                    </td>
                                    <td style="font-size:0.9rem; color:#666;">
                                        <?= date("d/m/y H:i", strtotime($row['event_date'])) ?>
                                    </td>
                                    <td>
                                        <span
                                            style="background:#f0f0f0; padding:2px 6px; border-radius:4px; font-size:0.8rem; font-weight:500;">
                                            <?= $row['prefix'] ?>-<?= $row['start_num'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span
                                                style="font-size:0.85rem;"><?= $row['current_sold'] ?>/<?= $row['max_tickets'] ?></span>
                                        </div>
                                        <div class="bar-bg">
                                            <div class="bar-fill" style="width:<?= $pct ?>%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('ยืนยันการลบ?')"
                                            style="color:#e74c3c; text-decoration:none;">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Add Form -->
            <div class="form-section">
                <h3><i class="fas fa-plus-circle"></i> เพิ่มงานใหม่</h3>
                <form method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div>
                            <label>ชื่องาน</label>
                            <input type="text" name="title" required placeholder="Ex. Jazz Night">
                        </div>
                        <div>
                            <label>วันเวลา</label>
                            <input type="datetime-local" name="date" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label>ราคาบัตร</label>
                            <input type="number" name="price" required placeholder="0.00">
                        </div>
                        <div>
                            <label>จำนวนสูงสุด</label>
                            <input type="number" name="max" value="100">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label>Prefix (เช่น VIP)</label>
                            <input type="text" name="prefix" required placeholder="VIP">
                        </div>
                        <div>
                            <label>Start Number</label>
                            <input type="number" name="start_num" value="1">
                        </div>
                    </div>

                    <label>รายละเอียด</label>
                    <textarea name="desc" rows="3" placeholder="รายละเอียดของงาน..."></textarea>

                    <label>รูปภาพปก</label>
                    <label class="upload-box" for="promo_img">
                        <input type="file" name="event_img" id="promo_img" accept="image/*" required
                            onchange="previewImage(this)">
                        <i class="fas fa-image"></i>
                        <span>คลิกเลือกรูป..</span>
                        <img id="preview" src="#">
                    </label>

                    <button type="submit" name="add_event" class="btn-add">บันทึกข้อมูล</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('preview').style.display = 'block';
                    // Hide icons/text
                    document.querySelector('.upload-box i').style.display = 'none';
                    document.querySelector('.upload-box span').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>