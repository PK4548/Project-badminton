<?php
session_start();
require_once '../includes/config.php';
//require_once './save_booking.php';
header('Content-Type: application/json');

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // ตรวจสอบว่าเป็นเจ้าของรายการจองจริงไหม เพื่อความปลอดภัย
    // $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ? AND user_id = ?");
    $stmt = $pdo->prepare("CALL sp_cancel_booking1(?,?)");
    if ($stmt->execute([$id, $user_id])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

?>