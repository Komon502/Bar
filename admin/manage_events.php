<?php
require '../db.php';
require '../upload_helper.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// เพิ่มกิจกรรมใหม่
if (isset($_POST['add_event'])) {
    $prefix = strtoupper($_POST['prefix']);
    $image_path = "";
    
    if (isset($_FILES['event_img'])) {
        $upload_result = SecureUpload::uploadImage($_FILES['event_img'], '../uploads/', 'evt');
        
        if ($upload_result['success']) {
            $image_path = str_replace('../', '', $upload_result['path']);
        } else {
            echo "<script>alert('ข้อผิดพลาดในการอัปโหลดรูป: {$upload_result['error']}'); history.back();</script>";
            exit();
        }
    }
    
    $sql = "INSERT INTO events (title, description, event_date, ticket_price, image_url, prefix, start_num, max_tickets) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$_POST['title'], $_POST['desc'], $_POST['date'], $_POST['price'], $image_path, $prefix, $_POST['start_num'], $_POST['max']]);
    header("Location: manage_events.php");
    exit();
}

// แก้ไขกิจกรรม
if (isset($_POST['edit_event'])) {
    $id = $_POST['event_id'];
    
    // ดึงข้อมูลเก่า
    $stmt = $pdo->prepare("SELECT image_url FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $old_event = $stmt->fetch();
    $image_path = $old_event['image_url'];
    
    // ถ้ามีรูปใหม่
    if (isset($_FILES['event_img']) && $_FILES['event_img']['error'] == 0) {
        $upload_result = SecureUpload::uploadImage($_FILES['event_img'], '../uploads/', 'evt');
        
        if ($upload_result['success']) {
            $image_path = str_replace('../', '', $upload_result['path']);
            
            // ลบรูปเก่า
            if ($old_event['image_url'] && file_exists('../' . $old_event['image_url'])) {
                unlink('../' . $old_event['image_url']);
            }
        }
    }
    
    $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, ticket_price = ?, image_url = ?, prefix = ?, start_num = ?, max_tickets = ? WHERE id = ?";
    $pdo->prepare($sql)->execute([
        $_POST['title'], 
        $_POST['desc'], 
        $_POST['date'], 
        $_POST['price'], 
        $image_path, 
        strtoupper($_POST['prefix']), 
        $_POST['start_num'], 
        $_POST['max'],
        $id
    ]);
    header("Location: manage_events.php");
    exit();
}

// ลบกิจกรรม
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT image_url FROM events WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $event = $stmt->fetch();
    
    // ลบรูป
    if ($event && $event['image_url'] && file_exists('../' . $event['image_url'])) {
        unlink('../' . $event['image_url']);
    }
    
    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_events.php");
    exit();
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

        .upload-box input[type="file"] {
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

        /* Event Image */
        .event-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 10px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }

        .close-btn {
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
        }

        .close-btn:hover {
            color: #e74c3c;
        }

        .btn-edit {
            background: #f39c12;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 0.85rem;
        }

        .btn-edit:hover {
            background: #e67e22;
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
                                <th>รูป</th>
                                <th>ชื่องาน</th>
                                <th>วันที่</th>
                                <th>Config</th>
                                <th>ยอดขาย</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
                            while ($row = $stmt->fetch()):
                                // แก้ไข: นับเฉพาะบัตรที่ไม่ได้ถูก cancel
                                $sold_stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as actual_sold FROM bookings WHERE event_id = ? AND status != 'cancelled'");
                                $sold_stmt->execute([$row['id']]);
                                $actual_sold = $sold_stmt->fetch()['actual_sold'];
                                
                                $pct = ($row['max_tickets'] > 0) ? ($actual_sold / $row['max_tickets']) * 100 : 0;
                                $img = $row['image_url'] ? "../" . $row['image_url'] : "https://via.placeholder.com/60";
                                ?>
                                <tr>
                                    <td>
                                        <img src="<?= $img ?>" class="event-img" onerror="this.src='https://via.placeholder.com/60'">
                                    </td>
                                    <td>
                                        <div style="font-weight:600; color:#333;"><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <small style="color:#999;">฿<?= number_format($row['ticket_price']) ?></small>
                                    </td>
                                    <td style="font-size:0.9rem; color:#666;">
                                        <?= date("d/m/y H:i", strtotime($row['event_date'])) ?>
                                    </td>
                                    <td>
                                        <span
                                            style="background:#f0f0f0; padding:2px 6px; border-radius:4px; font-size:0.8rem; font-weight:500;">
                                            <?= htmlspecialchars($row['prefix'], ENT_QUOTES, 'UTF-8') ?>-<?= $row['start_num'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-size:0.85rem;"><?= $actual_sold ?>/<?= $row['max_tickets'] ?></span>
                                        </div>
                                        <div class="bar-bg">
                                            <div class="bar-fill" style="width:<?= $pct ?>%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <button onclick='openEditModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-edit" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
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
                    <label class="upload-box" for="event_img">
                        <input type="file" name="event_img" id="event_img" accept="image/*" required onchange="previewImage(this)">
                        <i class="fas fa-image"></i>
                        <span>คลิกเลือกรูปภาพ..</span>
                        <img id="preview" src="#" alt="Preview">
                    </label>

                    <button type="submit" name="add_event" class="btn-add">บันทึกข้อมูล</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> แก้ไขกิจกรรม</h3>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="event_id" id="edit_event_id">
                
                <div class="form-grid">
                    <div>
                        <label>ชื่องาน</label>
                        <input type="text" name="title" id="edit_title" required>
                    </div>
                    <div>
                        <label>วันเวลา</label>
                        <input type="datetime-local" name="date" id="edit_date" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label>ราคาบัตร</label>
                        <input type="number" name="price" id="edit_price" required>
                    </div>
                    <div>
                        <label>จำนวนสูงสุด</label>
                        <input type="number" name="max" id="edit_max" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label>Prefix</label>
                        <input type="text" name="prefix" id="edit_prefix" required>
                    </div>
                    <div>
                        <label>Start Number</label>
                        <input type="number" name="start_num" id="edit_start_num" required>
                    </div>
                </div>

                <label>รายละเอียด</label>
                <textarea name="desc" id="edit_desc" rows="3"></textarea>

                <label>รูปภาพปก (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
                <label class="upload-box" for="edit_event_img">
                    <input type="file" name="event_img" id="edit_event_img" accept="image/*" onchange="previewEditImage(this)">
                    <i class="fas fa-image"></i>
                    <span>คลิกเลือกรูปใหม่ (ถ้าต้องการเปลี่ยน)</span>
                    <img id="edit_preview" src="#" alt="Preview">
                </label>

                <button type="submit" name="edit_event" class="btn-add">บันทึกการแก้ไข</button>
            </form>
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

        function previewEditImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('edit_preview').src = e.target.result;
                    document.getElementById('edit_preview').style.display = 'block';
                    document.querySelector('#editModal .upload-box i').style.display = 'none';
                    document.querySelector('#editModal .upload-box span').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openEditModal(event) {
            document.getElementById('edit_event_id').value = event.id;
            document.getElementById('edit_title').value = event.title;
            document.getElementById('edit_desc').value = event.description;
            document.getElementById('edit_price').value = event.ticket_price;
            document.getElementById('edit_max').value = event.max_tickets;
            document.getElementById('edit_prefix').value = event.prefix;
            document.getElementById('edit_start_num').value = event.start_num;
            
            // Convert datetime
            let eventDate = new Date(event.event_date);
            let dateStr = eventDate.toISOString().slice(0, 16);
            document.getElementById('edit_date').value = dateStr;
            
            // Show existing image
            if (event.image_url) {
                document.getElementById('edit_preview').src = '../' + event.image_url;
                document.getElementById('edit_preview').style.display = 'block';
                document.querySelector('#editModal .upload-box i').style.display = 'none';
                document.querySelector('#editModal .upload-box span').style.display = 'none';
            }
            
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            
            // Reset form
            document.getElementById('edit_preview').style.display = 'none';
            document.querySelector('#editModal .upload-box i').style.display = 'block';
            document.querySelector('#editModal .upload-box span').style.display = 'block';
            document.getElementById('edit_event_img').value = '';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>

</html>