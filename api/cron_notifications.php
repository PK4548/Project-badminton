<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$current_date = date('Y-m-d');
$current_time = date('H:i:00');
$time_plus_15 = date('H:i:00', strtotime('+15 minutes'));

// 1. แจ้งเตือนล่วงหน้า 15 นาที
$sql_reminder = "SELECT b.*, u.line_user_id FROM bookings b 
                 JOIN users u ON b.user_id = u.user_id 
                 WHERE b.booking_date = ? AND b.start_time <= ? 
                 AND b.reminder_sent = 0 AND b.status = 'confirmed'";
$stmt_remind = $pdo->prepare($sql_reminder);
$stmt_remind->execute([$current_date, $time_plus_15]);
$to_remind = $stmt_remind->fetchAll();

foreach ($to_remind as $row) {
    if (!empty($row['line_user_id'])) {
        $msg = "📢 อีก 15 นาที จะถึงเวลาจองสนามของคุณ ($row[start_time]) กรุณาเตรียมตัวให้พร้อมครับ";
        sendLinePushMessage($row['line_user_id'], $msg);
        
        // บันทึกลงตาราง notifications
        $pdo->prepare("INSERT INTO notifications (user_id, booking_id, message, status) VALUES (?, ?, ?, 'unread')")
            ->execute([$row['user_id'], $row['booking_id'], $msg]);

        $pdo->prepare("UPDATE bookings SET reminder_sent = 1 WHERE booking_id = ?")
            ->execute([$row['booking_id']]);
    }
}

// 2. แจ้งเตือนเมื่อหมดเวลาจอง
$sql_end = "SELECT b.*, u.line_user_id FROM bookings b 
            JOIN users u ON b.user_id = u.user_id 
            WHERE b.booking_date = ? AND b.end_time <= ? 
            AND b.end_notified = 0 AND b.status = 'confirmed'";
$stmt_end = $pdo->prepare($sql_end);
$stmt_end->execute([$current_date, $current_time]);
$to_end = $stmt_end->fetchAll();

foreach ($to_end as $row) {
    if (!empty($row['line_user_id'])) {
        $msg = "⌛ หมดเวลาการจองสนามของคุณแล้ว ($row[end_time]) ขอบคุณที่ใช้บริการครับ";
        sendLinePushMessage($row['line_user_id'], $msg);

        // บันทึกลงตาราง notifications
        $pdo->prepare("INSERT INTO notifications (user_id, booking_id, message, status) VALUES (?, ?, ?, 'unread')")
            ->execute([$row['user_id'], $row['booking_id'], $msg]);
        
        $pdo->prepare("UPDATE bookings SET end_notified = 1 WHERE booking_id = ?")
            ->execute([$row['booking_id']]);
    }
}
?>