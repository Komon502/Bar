<?php
require '../db.php';

// เช็คสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Filter by status
$filter = $_GET['filter'] ?? 'all';
$where_clause = "";
if ($filter == 'pending') {
    $where_clause = "WHERE status = 'pending'";
} elseif ($filter == 'confirmed') {
    $where_clause = "WHERE status = 'confirmed'";
} elseif ($filter == 'cancelled') {
    $where_clause = "WHERE status = 'cancelled'";
}

// Get all table reservations
$stmt = $pdo->query("SELECT r.*, u.username 
                     FROM reservations r 
                     LEFT JOIN users u ON r.user_id = u.id 
                     $where_clause
                     ORDER BY r.created_at DESC");
$reservations = $stmt->fetchAll();

// Count by status
$pending_count = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
$confirmed_count = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'confirmed'")->fetchColumn();
$cancelled_count = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'cancelled'")->fetchColumn();
$total_count = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการจองโต๊ะ - Admin</title>
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

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            margin: 0 0 10px;
            color: #2c3e50;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 10px 20px;
            background: #fff;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #555;
            transition: all 0.3s;
            font-weight: 500;
        }

        .filter-tab:hover {
            background: #f8f9fa;
            border-color: #3498db;
        }

        .filter-tab.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .badge {
            background: #e74c3c;
            color: white;
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 5px;
        }

        .table-container {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-action {
            padding: 6px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
            transition: 0.3s;
        }

        .btn-approve {
            background: #2ecc71;
            color: white;
        }

        .btn-approve:hover {
            background: #27ae60;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
        }

        .btn-reject:hover {
            background: #c0392b;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php require 'sidebar.php'; ?>

        <div class="content">
            <div class="page-header">
                <h2><i class="fas fa-calendar-check"></i> จัดการจองโต๊ะ</h2>
                <p style="color: #7f8c8d;">จัดการรายการจองโต๊ะจากลูกค้า</p>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="?filter=all" class="filter-tab <?= $filter == 'all' ? 'active' : '' ?>">
                    ทั้งหมด <span class="badge"><?= $total_count ?></span>
                </a>
                <a href="?filter=pending" class="filter-tab <?= $filter == 'pending' ? 'active' : '' ?>">
                    รอตรวจสอบ <span class="badge"><?= $pending_count ?></span>
                </a>
                <a href="?filter=confirmed" class="filter-tab <?= $filter == 'confirmed' ? 'active' : '' ?>">
                    ยืนยันแล้ว <span class="badge" style="background:#2ecc71;"><?= $confirmed_count ?></span>
                </a>
                <a href="?filter=cancelled" class="filter-tab <?= $filter == 'cancelled' ? 'active' : '' ?>">
                    ยกเลิก <span class="badge" style="background:#95a5a6;"><?= $cancelled_count ?></span>
                </a>
            </div>

            <!-- Table -->
            <div class="table-container">
                <?php if (count($reservations) == 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>ไม่มีรายการจอง</h3>
                        <p>ยังไม่มีรายการจองโต๊ะในหมวดนี้</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ผู้จอง</th>
                                <th>ติดต่อ</th>
                                <th>วันที่</th>
                                <th>เวลา</th>
                                <th>โต๊ะ</th>
                                <th>จำนวน</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            require '../csrf_helper.php';
                            foreach ($reservations as $row): 
                                $csrf_token = CSRF::generateToken();
                            ?>
                                <tr>
                                    <td><strong>#<?= $row['id'] ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['customer_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <?php if ($row['username']): ?>
                                            <br><small style="color:#999;">@<?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['customer_phone'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['booking_date'])) ?></td>
                                    <td><?= date('H:i', strtotime($row['booking_time'])) ?> น.</td>
                                    <td><strong><?= htmlspecialchars($row['table_number'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><?= $row['guest_count'] ?> คน</td>
                                    <td>
                                        <?php
                                        $status_class = 'status-' . $row['status'];
                                        $status_text = [
                                            'pending' => 'รอตรวจสอบ',
                                            'confirmed' => 'ยืนยันแล้ว',
                                            'cancelled' => 'ยกเลิก'
                                        ];
                                        ?>
                                        <span class="status-badge <?= $status_class ?>">
                                            <?= $status_text[$row['status']] ?? $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <a href="table_booking_action.php?id=<?= $row['id'] ?>&action=approve&csrf_token=<?= $csrf_token ?>" 
                                               class="btn-action btn-approve" 
                                               onclick="return confirm('ยืนยันการอนุมัติ?')">
                                                <i class="fas fa-check"></i> อนุมัติ
                                            </a>
                                            <a href="table_booking_action.php?id=<?= $row['id'] ?>&action=reject&csrf_token=<?= $csrf_token ?>" 
                                               class="btn-action btn-reject" 
                                               onclick="return confirm('ยืนยันการปฏิเสธ?')">
                                                <i class="fas fa-times"></i> ปฏิเสธ
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
