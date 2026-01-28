<?php
// ตรวจสอบว่า session เริ่มทำงานหรือยัง ถ้ายังให้เริ่ม (กัน Error)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// หาชื่อไฟล์ปัจจุบันเพื่อทำเมนู Active (Highlight สี)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <a href="index.php" class="logo"><i class="fas fa-cocktail"></i> NightBar</a>

    <div class="nav-links">
        <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> หน้าแรก
        </a>
        <a href="tickets.php" class="<?= $current_page == 'tickets.php' ? 'active' : '' ?>">
            <i class="fas fa-ticket-alt"></i> ซื้อตั๋วคอนเสิร์ต
        </a>
        <a href="reserve_table.php" class="<?= $current_page == 'reserve_table.php' ? 'active' : '' ?>">
            <i class="fas fa-utensils"></i> จองโต๊ะ (ร้านอาหาร)
        </a>
        <a href="about.php" class="<?= $current_page == 'about.php' ? 'active' : '' ?>">
            <i class="fas fa-info-circle"></i> เกี่ยวกับเรา
        </a>
        <a href="contact.php" class="<?= $current_page == 'contact.php' ? 'active' : '' ?>">
            <i class="fas fa-phone"></i> ติดต่อเรา
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>


            <a href="my_bookings.php" class="<?= $current_page == 'my_bookings.php' ? 'active' : '' ?>">
                <i class="fas fa-history"></i> ตั๋วของฉัน
            </a>

            <a href="logout.php" class="btn-login" onclick="return confirm('ยืนยันการออกจากระบบ?');">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>

        <?php else: ?>
            <a href="login.php" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
            </a>
        <?php endif; ?>
    </div>
</nav>