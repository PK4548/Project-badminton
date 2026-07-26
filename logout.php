<?php
// เริ่มต้นการใช้งาน Session เพื่อให้ระบบเข้าถึงข้อมูลที่ต้องการทำลายได้
session_start();

// ดึงไฟล์ตั้งค่าฐานข้อมูลมาใช้งาน
require_once 'includes/config.php';

/**
 * ส่วนที่เพิ่มใหม่: การล้าง Remember Me Token
 */
if (isset($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) === 2) {
        $selector = $parts[0];
        // ลบ Token ออกจากฐานข้อมูล
        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE selector = ?");
        $stmt->execute([$selector]);
    }
    // ลบ Cookie ทิ้ง
    setcookie('remember_me', '', time() - 3600, "/");
}

/**
 * ส่วนที่ 1: การล้างข้อมูล Session (ฝั่ง Server-side)
 */

// 1. ล้างข้อมูลในตัวแปร $_SESSION ทั้งหมดให้เป็นอาร์เรย์ว่าง
$_SESSION = array();

/**
 * 2. การลบ Session Cookie (ฝั่ง Client-side)
 */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. คำสั่งสุดท้าย: ทำลายไฟล์ Session
session_destroy();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging out...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        body { 
            font-family: 'Sarabun', sans-serif; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: 0.3s;
        }
        
        [data-bs-theme="dark"] body { background-color: #121212 !important; color: white; }
        
        .logout-box { text-center; }
        .spinner-grow { width: 3rem; height: 3rem; }
    </style>
</head>
<body>

    <div class="text-center logout-box">
        <div class="spinner-grow text-primary mb-4" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="fw-bold">กำลังออกจากระบบ...</h4>
        <p class="text-muted">ขอบคุณที่ใช้บริการ Badminton Booking System</p>
    </div>

    <script>
        /**
         * ส่วนที่ 2: การจัดการหน้าบ้าน (Client-side Logic)
         */
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);

        /**
         * 2. ระบบหน่วงเวลาเปลี่ยนหน้า (Redirect delay)
         */
        setTimeout(function() {
            window.location.href = 'login.php?status=logged_out';
        }, 1500);
    </script>
</body>
</html>