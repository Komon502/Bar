<?php
require '../db.php';
require '../csrf_helper.php';

// เช็คสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// ตรวจสอบ CSRF Token
if (!CSRF::validateToken($_GET['csrf_token'] ?? '')) {
    die('Invalid CSRF token. การกระทำนี้ถูกปฏิเสธเพื่อความปลอดภัย');
}

// ตรวจสอบค่า ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die('Invalid booking ID');
}

// ตรวจสอบ action (whitelist)
$action = $_GET['action'] ?? '';
$allowed_actions = ['approve', 'reject'];
if (!in_array($action, $allowed_actions)) {
    die('Invalid action');
}

try {
    $pdo->beginTransaction();
    
    if ($action == 'approve') {
        // ดึงข้อมูล booking
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            throw new Exception('Booking not found');
        }
        
        // Update status
        $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?")->execute([$id]);
        
        // สร้าง ticket codes
        $evt_stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $evt_stmt->execute([$booking['event_id']]);
        $evt = $evt_stmt->fetch();
        
        if($evt) {
            for($i = 0; $i < $booking['quantity']; $i++){
                $ticket_num = str_pad($evt['start_num'] + $evt['current_sold'] + $i, 3, '0', STR_PAD_LEFT);
                $ticket_code = $evt['prefix'] . ' ' . $ticket_num;
                
                $pdo->prepare("INSERT INTO ticket_items (booking_id, ticket_code) VALUES (?, ?)")
                    ->execute([$id, $ticket_code]);
            }
        }
        
        // Log activity
        error_log("Admin {$_SESSION['username']} approved booking #{$id}");
    } elseif ($action == 'reject') {
        // อัปเดตสถานะเป็น cancelled และคืนสต็อกตั๋ว
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        
        if($booking) {
            $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?")->execute([$id]);
            $pdo->prepare("UPDATE events SET current_sold = current_sold - ? WHERE id = ?")
                ->execute([$booking['quantity'], $booking['event_id']]);
            
            error_log("Admin {$_SESSION['username']} rejected booking #{$id}");
        }
    }
    
    $pdo->commit();
    header("Location: index.php?msg=success");
    
} catch(Exception $e) {
    $pdo->rollBack();
    error_log("Verify error: " . $e->getMessage());
    header("Location: index.php?msg=error");
}
