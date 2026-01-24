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

        .promo-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
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

        .btn {
            width: 100%;
            padding: 10px;
            background: #9b59b6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .promo-item {
            display: flex;
            gap: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .promo-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require 'sidebar.php'; ?>
        <div class="content">
            <h2 style="color:#2c3e50; margin-top:0;">🔥 จัดการโปรโมชั่น</h2>
            <div class="promo-layout">
                <div class="card">
                    <h3 style="margin-top:0;">เพิ่มโปรฯ ใหม่</h3>
                    <form method="post" enctype="multipart/form-data">
                        <label>หัวข้อ</label><input type="text" name="title" required>
                        <label>รายละเอียด</label><textarea name="details" rows="4"></textarea>
                        <label>รูปภาพ</label><input type="file" name="promo_img" accept="image/*" required>
                        <button type="submit" name="add_promo" class="btn">บันทึก</button>
                    </form>
                </div>
                <div class="card">
                    <h3 style="margin-top:0;">รายการโปรโมชั่น</h3>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM promotions ORDER BY id DESC");
                    while ($row = $stmt->fetch()):
                        $img = $row['image_url'] ? "../" . $row['image_url'] : "https://via.placeholder.com/80";
                    ?>
                        <div class="promo-item">
                            <img src="<?= $img ?>" class="promo-img">
                            <div style="flex:1;">
                                <h4 style="margin:0 0 5px;"><?= $row['title'] ?></h4>
                                <p style="margin:0; color:#666; font-size:0.9rem;"><?= $row['details'] ?></p>
                            </div>
                            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('ลบ?')" style="color:#e74c3c;"><i class="fas fa-trash"></i></a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>