<?php
require '../db.php';
require '../csrf_helper.php';

// เช็คสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Validate CSRF token
if (!isset($_GET['csrf_token']) || !CSRF::validateToken($_GET['csrf_token'])) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header("Location: manage_table_bookings.php");
    exit();
}

// Validate input
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$action = $_GET['action'] ?? '';

if (!$id || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['error'] = 'Invalid parameters';
    header("Location: manage_table_bookings.php");
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get reservation details
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->execute([$id]);
    $reservation = $stmt->fetch();
    
    if (!$reservation) {
        throw new Exception('Reservation not found');
    }
    
    // Check if already processed
    if ($reservation['status'] != 'pending') {
        throw new Exception('Reservation already processed');
    }
    
    // Update status based on action
    $new_status = ($action == 'approve') ? 'confirmed' : 'cancelled';
    $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    
    // Log admin action
    $admin_id = $_SESSION['user_id'] ?? 0;
    $log_action = ($action == 'approve') ? 'Approved table reservation' : 'Rejected table reservation';
    $log_stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, created_at) 
                                VALUES (?, ?, 'table_reservation', ?, NOW())");
    
    // Try to log, but don't fail if logs table doesn't exist
    try {
        $log_stmt->execute([$admin_id, $log_action, $id]);
    } catch (Exception $e) {
        // Ignore logging errors
        error_log("Logging error: " . $e->getMessage());
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Set success message
    $status_text = ($action == 'approve') ? 'อนุมัติ' : 'ปฏิเสธ';
    $_SESSION['success'] = "การจองโต๊ะ #{$id} ถูก{$status_text}เรียบร้อยแล้ว";
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollBack();
    
    error_log("Table booking action error: " . $e->getMessage());
    $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

// Redirect back
header("Location: manage_table_bookings.php");
exit();
