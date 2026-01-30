<?php
require 'db.php';
require 'rate_limiter.php';

// ถ้าล็อกอินแล้วให้เด้งไปหน้าแรกเลย
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$rateLimiter = new RateLimiter($pdo);
$user_ip = $_SERVER['REMOTE_ADDR'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Check if IP is blocked
    if ($rateLimiter->isBlocked($user_ip)) {
        $remaining = $rateLimiter->getRemainingTime($user_ip);
        $minutes = ceil($remaining / 60);
        $error = "คุณพยายามล็อกอินผิดหลายครั้ง กรุณารอ {$minutes} นาที";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful - clear attempts
            $rateLimiter->clearAttempts($user_ip);
            
            // Regenerate session ID (prevent session fixation)
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Log successful login
            error_log("Successful login: {$username} from {$user_ip}");

            if ($user['role'] == 'admin') header("Location: admin/index.php");
            else header("Location: index.php");
            exit();
        } else {
            // Record failed attempt
            $rateLimiter->recordAttempt($user_ip, $username);
            
            // Log failed login
            error_log("Failed login attempt: {$username} from {$user_ip}");
            
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - NightBar</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php require 'navbar.php'; ?>

    <div class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
        <div class="form-box" style="width: 100%; max-width: 400px; padding: 40px; border-radius: 15px;">
            <h2 style="text-align:center; margin-bottom: 30px; color: var(--primary);">🔐 เข้าสู่ระบบ</h2>

            <?php if (isset($error)): ?>
                <div style="background: #fadbd8; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">ชื่อผู้ใช้</label>
                    <input type="text" name="username" required placeholder="กรอกชื่อผู้ใช้ของคุณ" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">รหัสผ่าน</label>
                    <input type="password" name="password" required placeholder="กรอกรหัสผ่าน" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                </div>

                <button type="submit" class="btn-main" style="width: 100%; font-size: 1.1rem; padding: 12px;">Login</button>
            </form>

            <p style="text-align:center; margin-top:20px; color: #666;">
                ยังไม่มีบัญชี? <a href="register.php" style="color: var(--accent); font-weight: bold;">สมัครสมาชิกที่นี่</a>
            </p>
        </div>
    </div>
</body>

</html>