<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบว่า Login หรือยัง
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบก่อนทำรายการ']);
        exit();
    }

    $user_id      = $_SESSION['user_id'];
    $field_id     = $_POST['field_id'];
    $booking_date = $_POST['booking_date'];
    $start_time   = $_POST['start_time'];
    $end_time     = $_POST['end_time'];

    // 1. ตรวจสอบว่าเวลาเริ่มต้องน้อยกว่าเวลาสิ้นสุด
    if ($start_time >= $end_time) {
        echo json_encode(['status' => 'error', 'message' => 'เวลาเริ่มต้องก่อนเวลาสิ้นสุดครับ']);
        exit();
    }

    // 2. ตรวจสอบเวลาที่จองซ้ำซ้อน (Overlapping Check)
    $sql_check = "SELECT COUNT(*) FROM bookings 
                  WHERE field_id = ? 
                  AND booking_date = ? 
                  AND status != 'cancelled'
                  AND (? < end_time AND ? > start_time)";
    
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$field_id, $booking_date, $start_time, $end_time]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
    echo json_encode(['status' => 'error', 'message' => 'ขออภัย สนามนี้ถูกจองในช่วงเวลาดังกล่าวแล้ว']);
} else {
    // เตรียม Query สำหรับ Insert การจอง
    $sql_insert = "INSERT INTO bookings (user_id, field_id, booking_date, start_time, end_time, status) 
                    VALUES (?, ?, ?, ?, ?, 'confirmed')";
    $stmt_insert = $pdo->prepare($sql_insert);

    // ตรวจสอบการ Execute การจอง
    if ($stmt_insert->execute([$user_id, $field_id, $booking_date, $start_time, $end_time])) {
        
        // ดึง ID ของการจองที่เพิ่ง Insert สำเร็จ
        $booking_id = $pdo->lastInsertId();

        // ดึง line_user_id ของผู้ใช้
        $stmt_user = $pdo->prepare("SELECT line_user_id FROM users WHERE user_id = ?");
        $stmt_user->execute([$user_id]);
        $user_data = $stmt_user->fetch();

        $msg = "🔔 จองสนามสำเร็จ!\n📅 วันที่: $booking_date\n สนามคอร์ดที่: $field_id\n⏰เวลา: $start_time - $end_time";

        // 1. ส่งแจ้งเตือนทาง LINE (ถ้ามี ID)
        if ($user_data && !empty($user_data['line_user_id'])) {
            sendLinePushMessage($user_data['line_user_id'], $msg); 
        }

        // 2. บันทึกลงตาราง notifications เสมอ
        $sql_notif = "INSERT INTO notifications (user_id, booking_id, message, status) VALUES (?, ?, ?, 'unread')";
        $pdo->prepare($sql_notif)->execute([$user_id, $booking_id, $msg]);

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลการจอง']);
    }
}
}
?>