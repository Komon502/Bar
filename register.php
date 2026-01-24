<?php 
require 'db.php';
// ถ้าล็อกอินแล้วให้เด้งไปหน้าแรก
if(isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    
    if($pass != $confirm_pass) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user]);
        
        if ($stmt->rowCount() > 0) {
            $error = "ชื่อนี้มีคนใช้แล้ว";
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')";
            if ($pdo->prepare($sql)->execute([$user, $hashed])) {
                echo "<script>alert('สมัครสำเร็จ! กรุณาล็อคอิน'); window.location='login.php';</script>";
            } else {
                $error = "เกิดข้อผิดพลาด โปรดลองใหม่";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก - NightBar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require 'navbar.php'; ?>

    <div class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
        <div class="form-box" style="width: 100%; max-width: 450px; padding: 40px; border-radius: 15px;">
            <h2 style="text-align:center; margin-bottom: 30px; color: var(--primary);">📝 สมัครสมาชิกใหม่</h2>
            
            <?php if(isset($error)): ?>
                <div style="background: #fadbd8; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">ตั้งชื่อผู้ใช้</label>
                    <input type="text" name="username" required placeholder="ภาษาอังกฤษหรือตัวเลข" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">ตั้งรหัสผ่าน</label>
                    <input type="password" name="password" required placeholder="อย่างน้อย 4 ตัวอักษร" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">ยืนยันรหัสผ่าน</label>
                    <input type="password" name="confirm_password" required placeholder="ใส่รหัสผ่านอีกครั้ง" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                </div>
                
                <button type="submit" class="btn-main" style="width: 100%; font-size: 1.1rem; padding: 12px;">สมัครสมาชิก</button>
            </form>
            
            <p style="text-align:center; margin-top:20px; color: #666;">
                มีบัญชีอยู่แล้ว? <a href="login.php" style="color: var(--accent); font-weight: bold;">เข้าสู่ระบบ</a>
            </p>
        </div>
    </div>
</body>
</html>