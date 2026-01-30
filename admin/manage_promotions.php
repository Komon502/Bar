<?php
require '../db.php';
require '../upload_helper.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// เพิ่มโปรโมชั่นใหม่
if (isset($_POST['add_promo'])) {
    $image_path = "";
    if (isset($_FILES['promo_img'])) {
        $upload_result = SecureUpload::uploadImage($_FILES['promo_img'], '../uploads/', 'promo');
        
        if ($upload_result['success']) {
            // แก้ไข: ตัด ../ ออกเพื่อให้ path ถูกต้อง
            $image_path = str_replace('../', '', $upload_result['path']);
        } else {
            echo "<script>alert('ข้อผิดพลาดในการอัปโหลดรูป: {$upload_result['error']}'); history.back();</script>";
            exit();
        }
    }
    $pdo->prepare("INSERT INTO promotions (title, details, image_url) VALUES (?, ?, ?)")->execute([$_POST['title'], $_POST['details'], $image_path]);
    header("Location: manage_promotions.php");
    exit();
}

// แก้ไขโปรโมชั่น
if (isset($_POST['edit_promo'])) {
    $id = $_POST['promo_id'];
    $title = $_POST['title'];
    $details = $_POST['details'];
    
    // ดึงรูปเก่า
    $stmt = $pdo->prepare("SELECT image_url FROM promotions WHERE id = ?");
    $stmt->execute([$id]);
    $old_promo = $stmt->fetch();
    $image_path = $old_promo['image_url'];
    
    // ถ้ามีรูปใหม่
    if (isset($_FILES['promo_img']) && $_FILES['promo_img']['error'] == 0) {
        $upload_result = SecureUpload::uploadImage($_FILES['promo_img'], '../uploads/', 'promo');
        
        if ($upload_result['success']) {
            $image_path = str_replace('../', '', $upload_result['path']);
            
            // ลบรูปเก่า (ถ้ามี)
            if ($old_promo['image_url'] && file_exists('../' . $old_promo['image_url'])) {
                unlink('../' . $old_promo['image_url']);
            }
        }
    }
    
    $pdo->prepare("UPDATE promotions SET title = ?, details = ?, image_url = ? WHERE id = ?")
        ->execute([$title, $details, $image_path, $id]);
    header("Location: manage_promotions.php");
    exit();
}

// ลบโปรโมชั่น
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT image_url FROM promotions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $promo = $stmt->fetch();
    
    // ลบรูป
    if ($promo && $promo['image_url'] && file_exists('../' . $promo['image_url'])) {
        unlink('../' . $promo['image_url']);
    }
    
    $pdo->prepare("DELETE FROM promotions WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_promotions.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>โปรโมชั่น</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            background-color: #f4f6f9;
            font-family: 'Kanit', sans-serif;
            overflow: hidden; /* Prevent full page scroll */
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
            height: calc(100vh - 40px); /* Padding compensation */
            box-sizing: border-box;
        }

        /* --- Left Side: Promo List --- */
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

        .list-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .promo-item {
            display: flex;
            gap: 15px;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            background: #fff;
        }

        .promo-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-color: #3498db;
        }

        .promo-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
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

        label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 0.9rem;
        }

        input:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
        }

        .btn {
            background: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #2980b9;
        }

        /* Compact Upload Box */
        .upload-box {
            width: 100%;
            height: 120px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 15px;
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
            font-size: 0.9rem;
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
            max-width: 500px;
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
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
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
            <!-- Left: Promo List -->
            <div class="list-section">
                <div class="list-header">
                    <h2 style="margin:0; font-size:1.4rem; color:#2c3e50;"><i class="fas fa-bullhorn"></i> รายการโปรโมชั่น</h2>
                    <span style="font-size:0.9rem; color:#888;">ทั้งหมด <?= $pdo->query("SELECT count(*) FROM promotions")->fetchColumn() ?> รายการ</span>
                </div>
                <div class="list-container">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM promotions ORDER BY id DESC");
                    while ($row = $stmt->fetch()):
                        $img = $row['image_url'] ? "../" . $row['image_url'] : "https://via.placeholder.com/80";
                    ?>
                        <div class="promo-item">
                            <img src="<?= $img ?>" class="promo-img" onerror="this.src='https://via.placeholder.com/100'">
                            <div style="flex:1;">
                                <h4 style="margin:0 0 5px; font-size:1.1rem; color:#333;"><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <p style="margin:0; color:#666; font-size:0.9rem; line-height:1.5;"><?= htmlspecialchars($row['details'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div style="display:flex; gap:5px; align-self:center;">
                                <button onclick='openEditModal(<?= json_encode([
                                    "id" => $row["id"],
                                    "title" => $row["title"],
                                    "details" => $row["details"],
                                    "img" => $img
                                ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-edit" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('ยืนยันการลบ?')" style="color:#e74c3c; padding:8px 15px; background:#ffe6e6; border-radius:5px;" title="ลบ">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Right: Add Form -->
            <div class="form-section">
                <h3><i class="fas fa-plus-circle"></i> เพิ่มโปรฯ ใหม่</h3>
                <form method="post" enctype="multipart/form-data">
                    <label>หัวข้อโปรโมชั่น</label>
                    <input type="text" name="title" required placeholder="เช่น Happy Hour 1 แถม 1">
                    
                    <label>รายละเอียด</label>
                    <textarea name="details" rows="5" placeholder="รายละเอียดโปรโมชั่น..."></textarea>
                    
                    <label>รูปภาพปก</label>
                    <label class="upload-box" for="promo_img">
                        <input type="file" name="promo_img" id="promo_img" accept="image/*" required onchange="previewImage(this)">
                        <i class="fas fa-image"></i>
                        <span>คลิกเลือกรูปภาพ..</span>
                        <img id="preview" src="#" alt="Preview">
                    </label>

                    <button type="submit" name="add_promo" class="btn">บันทึกข้อมูล</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> แก้ไขโปรโมชั่น</h3>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="promo_id" id="edit_promo_id">
                
                <label>หัวข้อโปรโมชั่น</label>
                <input type="text" name="title" id="edit_title" required>
                
                <label>รายละเอียด</label>
                <textarea name="details" id="edit_details" rows="5"></textarea>
                
                <label>รูปภาพปก (เว้นว่างถ้าไม่ต้องการเปลี่ยน)</label>
                <label class="upload-box" for="edit_promo_img">
                    <input type="file" name="promo_img" id="edit_promo_img" accept="image/*" onchange="previewEditImage(this)">
                    <i class="fas fa-image"></i>
                    <span>คลิกเลือกรูปใหม่ (ถ้าต้องการเปลี่ยน)</span>
                    <img id="edit_preview" src="#" alt="Preview">
                </label>

                <button type="submit" name="edit_promo" class="btn">บันทึกการแก้ไข</button>
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

        function openEditModal(data) {
            document.getElementById('edit_promo_id').value = data.id;
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_details').value = data.details;
            
            // แสดงรูปเดิม
            document.getElementById('edit_preview').src = data.img;
            document.getElementById('edit_preview').style.display = 'block';
            document.querySelector('#editModal .upload-box i').style.display = 'none';
            document.querySelector('#editModal .upload-box span').style.display = 'none';
            
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            
            // Reset form
            document.getElementById('edit_preview').style.display = 'none';
            document.querySelector('#editModal .upload-box i').style.display = 'block';
            document.querySelector('#editModal .upload-box span').style.display = 'block';
            document.getElementById('edit_promo_img').value = '';
        }

        // ปิด modal เมื่อคลิกข้างนอก
        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>

</html>