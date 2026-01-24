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
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-add {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-box {
            display: none;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #ecf0f1;
            color: #555;
        }

        .bar-bg {
            background: #eee;
            height: 6px;
            border-radius: 3px;
            width: 100px;
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
        <div class="content">
            <div class="header-flex">
                <h2 style="color:#2c3e50; margin:0;">🎸 จัดการกิจกรรม</h2>
                <button onclick="document.getElementById('addForm').style.display='block'" class="btn-add">+ เพิ่มงานใหม่</button>
            </div>

            <div id="addForm" class="form-box">
                <h3 style="margin-top:0;">เพิ่มกิจกรรมใหม่</h3>
                <form method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div>
                            <label>ชื่องาน</label><input type="text" name="title" required>
                            <label>วันเวลา</label><input type="datetime-local" name="date" required>
                            <label>ราคาบัตร</label><input type="number" name="price" required>
                        </div>
                        <div>
                            <label>Prefix ตั๋ว (เช่น VIP)</label><input type="text" name="prefix" required>
                            <label>เลขเริ่ม (เช่น 100)</label><input type="number" name="start_num" value="1">
                            <label>จำนวนตั๋วสูงสุด</label><input type="number" name="max" value="100">
                        </div>
                    </div>
                    <label>รายละเอียด</label><textarea name="desc"></textarea>
                    <label>รูปภาพ</label><input type="file" name="event_img" accept="image/*" required>
                    <button type="submit" name="add_event" class="btn-add" style="width:100%;">บันทึก</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ชื่องาน</th>
                        <th>วันที่</th>
                        <th>Config ตั๋ว</th>
                        <th>ยอดขาย</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
                    while ($row = $stmt->fetch()):
                        $pct = ($row['max_tickets'] > 0) ? ($row['current_sold'] / $row['max_tickets']) * 100 : 0;
                    ?>
                        <tr>
                            <td><strong><?= $row['title'] ?></strong></td>
                            <td><?= date("d/m/y H:i", strtotime($row['event_date'])) ?></td>
                            <td><span style="background:#eee; padding:2px 5px; border-radius:4px; font-size:0.9rem;"><?= $row['prefix'] ?>-<?= $row['start_num'] ?></span></td>
                            <td>
                                <?= $row['current_sold'] ?> / <?= $row['max_tickets'] ?>
                                <div class="bar-bg">
                                    <div class="bar-fill" style="width:<?= $pct ?>%"></div>
                                </div>
                            </td>
                            <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('ลบ?')" style="color:#e74c3c;">ลบ</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>