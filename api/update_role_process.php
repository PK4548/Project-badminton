<?php
session_start();
// แก้ Path ให้ถูกต้อง ถ้า config.php อยู่ใน includes ให้ใช้ ../includes/config.php
// แต่ถ้า config.php อยู่โฟลเดอร์เดียวกับ manage_users.php ให้ใช้ ../config.php
require '../includes/config.php'; 
header('Content-Type: application/json');

// เช็คว่าเป็น Admin จริงไหม
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึง']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่าส่งค่ามาครบไหม
    if (!isset($_POST['user_id']) || !isset($_POST['role'])) {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit();
    }

    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    // ป้องกันไม่ให้แก้สิทธิ์ตัวเอง
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเปลี่ยนสิทธิ์ตัวเองได้']);
        exit();
    }

    try {
        // อัปเดตข้อมูล (ตรวจสอบชื่อคอลัมน์ใน DB ของคุณด้วยว่าชื่อ 'role' หรือ 'user_role')
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        if ($stmt->execute([$role, $user_id])) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตข้อมูลได้']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}
?>