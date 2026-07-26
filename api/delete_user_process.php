<?php
session_start();
require_once '../includes/config.php'; // นำเข้าการเชื่อมต่อฐานข้อมูล

// ตรวจสอบสิทธิ์ (Security Check) เฉพาะ Admin เท่านั้นที่ลบได้
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึง']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    try {
        // เตรียมคำสั่ง SQL สำหรับลบข้อมูล
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $result = $stmt->execute([$user_id]);

        if ($result) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบข้อมูลได้']);
        }
    } catch (PDOException $e) {
        // กรณีมี Error เช่น ผู้ใช้นี้มีรายการจองติดอยู่ (Foreign Key Constraint)
        echo json_encode(['status' => 'error', 'message' => 'ลบไม่ได้: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
}
?>