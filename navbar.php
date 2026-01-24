<?php
// หาชื่อไฟล์ปัจจุบันเพื่อทำเมนู Active
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <a href="index.php" class="logo">🍸 NightBar</a>
    <div class="nav-links">
        <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">หน้าแรก</a>
        <a href="tickets.php" class="<?= $current_page == 'tickets.php' ? 'active' : '' ?>">ซื้อตั๋วคอนเสิร์ต</a>
        <a href="about.php" class="<?= $current_page == 'about.php' ? 'active' : '' ?>">เกี่ยวกับเรา</a>
        <a href="contact.php" class="<?= $current_page == 'contact.php' ? 'active' : '' ?>">ติดต่อเรา</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                
            <?php endif; ?>
            <a href="my_bookings.php">ตั๋วของฉัน</a>
            <a href="logout.php" class="btn-login">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn-login">เข้าสู่ระบบ</a>
        <?php endif; ?>
    </div>
</nav>