<?php
$current_page = basename($_SERVER['PHP_SELF']);
// เช็คจำนวน Pending เพื่อแสดง Badge สีแดง
if (isset($pdo)) {
    $pendingCount = $pdo->query("SELECT count(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
} else {
    $pendingCount = 0;
}
?>

<style>
    /* CSS Sidebar (ใช้ร่วมกันทุกหน้า) */
    .sidebar {
        width: 250px;
        background-color: #212f3d;
        /* สีกรมท่าเข้ม */
        color: #ecf0f1;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        min-height: 100vh;
    }

    .sidebar-header {
        padding: 20px;
        font-size: 1.2rem;
        font-weight: 600;
        background-color: #17202a;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid #2c3e50;
        color: #fff;
    }

    .sidebar-menu {
        display: flex;
        flex-direction: column;
        padding-top: 10px;
    }

    .sidebar a {
        color: #bdc3c7;
        padding: 15px 20px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 15px;
        border-bottom: 1px solid #2c3e50;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }

    .sidebar a:hover {
        background-color: #34495e;
        color: #fff;
    }

    .sidebar a.active {
        background-color: #2c3e50;
        color: #fff;
        border-left: 4px solid #3498db;
        padding-left: 16px;
    }

    .badge {
        background: #e74c3c;
        color: white;
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 10px;
        margin-left: auto;
    }

    .menu-category {
        padding: 15px 20px 8px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #7f8c8d;
        letter-spacing: 1px;
        border-top: 1px solid #2c3e50;
        margin-top: 5px;
    }

    .menu-category:first-of-type {
        border-top: none;
        margin-top: 0;
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-shield-alt" style="color:#3498db;"></i> Admin Panel
    </div>
    <div class="sidebar-menu">
        <!-- Overview Section -->
        <div class="menu-category">📊 ภาพรวม</div>
        <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line" style="width:20px;"></i> Dashboard
        </a>

        <!-- Bookings Management Section -->
        <div class="menu-category">📋 จัดการการจอง</div>
        <a href="verify.php" class="<?= $current_page == 'verify.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice-dollar" style="width:20px;"></i> ตรวจสลิปตั๋วงาน
            <?php if ($pendingCount > 0): ?>
                <span class="badge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>
        <?php 
        // Count pending table reservations
        $pendingTableCount = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
        ?>
        <a href="manage_table_bookings.php" class="<?= $current_page == 'manage_table_bookings.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check" style="width:20px;"></i> จองโต๊ะ
            <?php if ($pendingTableCount > 0): ?>
                <span class="badge"><?= $pendingTableCount ?></span>
            <?php endif; ?>
        </a>
        <a href="history.php" class="<?= $current_page == 'history.php' ? 'active' : '' ?>">
            <i class="fas fa-history" style="width:20px;"></i> ประวัติการจอง
        </a>

        <!-- Content Management Section -->
        <div class="menu-category">🎨 จัดการเนื้อหา</div>
        <a href="manage_events.php" class="<?= $current_page == 'manage_events.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt" style="width:20px;"></i> ตั๋ว/งานอีเว้นท์
        </a>
        <a href="manage_promotions.php" class="<?= $current_page == 'manage_promotions.php' ? 'active' : '' ?>">
            <i class="fas fa-bullhorn" style="width:20px;"></i> โปรโมชั่น
        </a>
        <a href="manage_tables.php" class="<?= $current_page == 'manage_tables.php' ? 'active' : '' ?>">
            <i class="fas fa-chair" style="width:20px;"></i> ผังโต๊ะ
        </a>

        <!-- System Section -->
        <div class="menu-category">⚙️ ระบบ</div>
        <a href="../index.php" target="_blank">
            <i class="fas fa-external-link-alt" style="width:20px;"></i> หน้าเว็บหลัก
        </a>
        <a href="../logout.php" style="color:#e74c3c; border-top:1px solid #2c3e50; margin-top:auto;">
            <i class="fas fa-sign-out-alt" style="width:20px;"></i> Logout
        </a>
    </div>
</div>