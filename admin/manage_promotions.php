<?php
require '../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['add_promo'])) {
    $image_path = "";
    if (isset($_FILES['promo_img']) && $_FILES['promo_img']['error'] == 0) {
        $ext = pathinfo($_FILES['promo_img']['name'], PATHINFO_EXTENSION);
        $new_name = "pro_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['promo_img']['tmp_name'], "../uploads/" . $new_name);
        $image_path = "uploads/" . $new_name;
    }
    $pdo->prepare("INSERT INTO promotions (title, details, image_url) VALUES (?, ?, ?)")->execute([$_POST['title'], $_POST['details'], $image_path]);
    header("Location: manage_promotions.php");
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM promotions WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_promotions.php");
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
                            <img src="<?= $img ?>" class="promo-img">
                            <div style="flex:1;">
                                <h4 style="margin:0 0 5px; font-size:1.1rem; color:#333;"><?= $row['title'] ?></h4>
                                <p style="margin:0; color:#666; font-size:0.9rem; line-height:1.5;"><?= $row['details'] ?></p>
                            </div>
                            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('ยืนยันการลบ?')" style="color:#e74c3c; align-self:center; padding:10px;">
                                <i class="fas fa-trash-alt fa-lg"></i>
                            </a>
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
    </script>
</body>

</html>