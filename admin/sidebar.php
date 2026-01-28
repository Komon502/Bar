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
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-shield-alt" style="color:#3498db;"></i> Admin Panel
    </div>
    <div class="sidebar-menu">
        <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line" style="width:20px;"></i> Dashboard
        </a>
        <a href="verify.php" class="<?= $current_page == 'verify.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice-dollar" style="width:20px;"></i> ตรวจสลิป
            <?php if ($pendingCount > 0): ?>
                <span class="badge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>
        <a href="manage_events.php" class="<?= $current_page == 'manage_events.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt" style="width:20px;"></i> จัดการตั๋ว/งาน
        </a>
        <a href="manage_promotions.php" class="<?= $current_page == 'manage_promotions.php' ? 'active' : '' ?>">
            <i class="fas fa-bullhorn" style="width:20px;"></i> โปรโมชั่น
        </a>
        <a href="manage_tables.php" class="<?= $current_page == 'manage_tables.php' ? 'active' : '' ?>">
            <i class="fas fa-chair" style="width:20px;"></i> จัดการโต๊ะ
        </a>

        <a href="history.php" class="<?= $current_page == 'history.php' ? 'active' : '' ?>">
            <i class="fas fa-history" style="width:20px;"></i> ประวัติการจอง
        </a>
        <a href="../index.php" target="_blank">
            <i class="fas fa-external-link-alt" style="width:20px;"></i> หน้าเว็บหลัก
        </a>
        <a href="../logout.php" style="color:#e74c3c; margin-top:auto; border-top:1px solid #2c3e50;">
            <i class="fas fa-sign-out-alt" style="width:20px;"></i> Logout
        </a>
    </div>
</div>