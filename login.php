<?php
// เริ่มต้นการใช้งาน Session เพื่อเก็บสถานะการเข้าสู่ระบบ
session_start();

// ดึงไฟล์ตั้งค่าการเชื่อมต่อฐานข้อมูลมาใช้งาน
require_once 'includes/config.php';

/**
 * ส่วนที่ 1: ตรวจสอบสถานะการเข้าสู่ระบบ
 * หากผู้ใช้งานมี Session 'user_id' อยู่แล้ว (แปลว่า Login ค้างไว้) 
 * ให้ดีดตัวไปยังหน้า dashboard.php ทันที เพื่อไม่ต้องกรอกข้อมูลซ้ำ
 */
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

/**
 * เพิ่มเติม: ระบบตรวจสอบ Remember Me Token (Auto Login)
 */
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    list($selector, $authenticator) = explode(':', $_COOKIE['remember_me']);
    
    // ค้นหา Token ในฐานข้อมูลที่ยังไม่หมดอายุ
    $stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expiry > NOW()");
    $stmt->execute([$selector]);
    $tokenRow = $stmt->fetch();
    
    if ($tokenRow && hash_equals($tokenRow['hashed_validator'], hash('sha256', hex2bin($authenticator)))) {
        // Token ถูกต้อง: ดึงข้อมูลผู้ใช้มาสร้าง Session
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$tokenRow['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = $user['role']; 
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        }
    }
}

/**
 * ส่วนที่ 2: ประมวลผลเมื่อมีการส่งฟอร์ม (Method POST)
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจาก input ที่ผู้ใช้กรอกมา
    $email = $_POST['email'];
    $password = $_POST['password'];

    // เตรียมคำสั่ง SQL เพื่อหาข้อมูลผู้ใช้จาก Email (ใช้ prepare เพื่อป้องกัน SQL Injection)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    /**
     * ตรวจสอบเงื่อนไขการเข้าสู่ระบบ:
     * 1. ต้องมีข้อมูลผู้ใช้ในระบบ ($user)
     * 2. รหัสผ่านที่กรอกมา ต้องตรงกับรหัสผ่านที่เข้ารหัสไว้ในฐานข้อมูล (password_verify)
     */
    if ($user && password_verify($password, $user['password'])) {
        // เมื่อข้อมูลถูกต้อง: บันทึกข้อมูลสำคัญลงใน Session เพื่อนำไปใช้งานในหน้าอื่นๆ
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_role'] = $user['role']; 
        $_SESSION['user_name'] = $user['name'];

        /**
         * ส่วนที่ปรับปรุง: การจัดการระบบ Remember Me แบบปลอดภัย
         */
        if (isset($_POST['rememberMe'])) {
            $selector = bin2hex(random_bytes(6)); // 12 ตัวอักษร
            $authenticator = random_bytes(33);
            
            // ตั้ง Cookie นาน 30 วัน (HttpOnly เพื่อความปลอดภัยจาก XSS)
            setcookie('remember_me', $selector.':'.bin2hex($authenticator), time() + (86400 * 30), "/", "", false, true);
            
            // บันทึก Hash ของ Token ลงฐานข้อมูล
            $stmt = $pdo->prepare("INSERT INTO user_tokens (selector, hashed_validator, user_id, expiry) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))");
            $stmt->execute([$selector, hash('sha256', $authenticator), $user['user_id']]);
        }

        // พ่นคำสั่ง JavaScript เพื่อแสดงผลการแจ้งเตือนสวยๆ ด้วย SweetAlert2
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'สำเร็จ!',
                    text: 'ยินดีต้อนรับคุณ {$user['name']}',
                    icon: 'success',
                    timer: 2000, // แสดงผล 2 วินาทีแล้วปิดเอง
                    showConfirmButton: false
                }).then(() => {
                    // หลังจากแจ้งเตือนเสร็จ ให้ย้ายหน้าไปยัง Dashboard
                    window.location.href = 'dashboard.php';
                });
            });
        </script>";
    } else {
        // หากข้อมูลไม่ถูกต้อง: กำหนดข้อความแจ้งเตือนความผิดพลาดไว้แสดงใน HTML
        $error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Badminton System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        body { 
            font-family: 'Sarabun', sans-serif; 
            display: flex; 
            align-items: center; 
            min-height: 100vh; 
            transition: 0.3s;
        }

        /* ตกแต่ง Card ในโหมด Dark Theme */
        [data-bs-theme="dark"] body { background-color: #121212 !important; }
        [data-bs-theme="dark"] .login-card { background-color: #2c2c2c !important; color: #fff; }

        .login-card { 
            max-width: 420px; 
            width: 100%; 
            margin: auto; 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            z-index: 2;
        }

        /* ตำแหน่งปุ่มสลับธีม */
        #theme-toggle { position: fixed; top: 20px; right: 20px; cursor: pointer; font-size: 1.5rem; z-index: 1000; }
        [data-bs-theme="light"] #theme-icon-dark { color: #FFD700; }
        [data-bs-theme="dark"] #theme-icon-light { color: #ffca28; }

        .back-home { position: fixed; top: 20px; left: 20px; text-decoration: none; font-weight: bold; z-index: 1000; }
    </style>
</head>
<body class="bg-light">

    <a href="index.php" class="back-home btn btn-outline-primary rounded-pill shadow-sm">
        <i class="bi bi-house-door-fill me-1"></i> กลับหน้าแรก
    </a>

    <div id="theme-toggle">
        <i class="bi bi-sun-fill d-none" id="theme-icon-light"></i>
        <i class="bi bi-moon-stars-fill" id="theme-icon-dark"></i>
    </div>

    <div class="card login-card p-5 shadow-lg">
        <div class="text-center mb-4">
            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-person-fill fs-2"></i>
            </div>
            <h3 class="fw-bold">เข้าสู่ระบบ</h3>
            <p class="text-muted small">กรอกข้อมูลเพื่อใช้งานระบบ</p>
        </div>
        
        <form method="POST" id="loginForm">
            <div class="mb-3">
                <label class="form-label fw-bold small">อีเมลผู้ใช้งาน</label>
                <div class="input-group border rounded-pill overflow-hidden">
                    <span class="input-group-text bg-transparent border-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control border-0 ps-0 shadow-none" placeholder="example@mail.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">รหัสผ่าน</label>
                <div class="input-group border rounded-pill overflow-hidden">
                    <span class="input-group-text bg-transparent border-0"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control border-0 ps-0 shadow-none" placeholder="••••••••" required>
                </div>
            </div>

            <div class="mb-4 form-check ps-4 ms-1">
                <input class="form-check-input" type="checkbox" name="rememberMe" id="rememberMe">
                <label class="form-check-label small text-muted" for="rememberMe">
                    จดจำรหัสผ่านสำหรับบัญชีนี้
                </label>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger py-2 small mb-3 border-0 shadow-sm text-center rounded-pill">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow">
                เข้าสู่ระบบ <i class="bi bi-arrow-right-short"></i>
            </button>
        </form>
        
        <div class="text-center mt-4">
            <p class="small text-muted mb-0">ยังไม่มีบัญชีใช่ไหม? <a href="register.php" class="text-primary fw-bold text-decoration-none">ลงทะเบียนที่นี่</a></p>
        </div>
    </div>

    <script>
        /**
         * ส่วนที่ 3: ระบบจดจำข้อมูล (ปรับปรุงเพื่อความปลอดภัย)
         * จะจดจำเฉพาะ Email เท่านั้น รหัสผ่านจะถูกจัดการผ่าน Token ในฝั่ง Server
         */
        const emailInput = document.getElementById('email');
        const rememberCheckbox = document.getElementById('rememberMe');
        const loginForm = document.getElementById('loginForm');

        // เมื่อโหลดหน้าเว็บ: ตรวจสอบว่าเคยจำอีเมลไว้ไหม
        const savedEmail = localStorage.getItem('badminton_remembered_email');
        if (savedEmail) {
            emailInput.value = savedEmail;
            rememberCheckbox.checked = true;
        }

        // เมื่อกดยืนยันฟอร์ม (Submit): จะทำหน้าที่บันทึกหรือลบอีเมล (ไม่ใช่รหัสผ่าน)
        loginForm.addEventListener('submit', function() {
            const email = emailInput.value;
            if (rememberCheckbox.checked) {
                localStorage.setItem('badminton_remembered_email', email);
            } else {
                localStorage.removeItem('badminton_remembered_email');
            }
        });

        /**
         * ส่วนที่ 4: ระบบสลับโหมด Dark/Light Theme 
         */
        const themeToggle = document.getElementById('theme-toggle');
        const iconLight = document.getElementById('theme-icon-light');
        const iconDark = document.getElementById('theme-icon-dark');
        const htmlElement = document.documentElement;

        // ฟังก์ชันช่วยสลับไอคอนพระอาทิตย์/พระจันทร์
        function updateIcons(theme) {
            if (theme === 'dark') {
                iconLight.classList.remove('d-none');
                iconDark.classList.add('d-none');
            } else {
                iconLight.classList.add('d-none');
                iconDark.classList.remove('d-none');
            }
        }

        // ดึงค่าธีมที่เคยตั้งไว้จาก LocalStorage (ถ้าไม่มีให้ใช้ Light เป็นหลัก)
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateIcons(savedTheme);

        // เมื่อคลิกที่ปุ่มสลับธีม: เปลี่ยน attribute ของ HTML และบันทึกค่าลง Storage
        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcons(newTheme);
        });
    </script>
</body>
</html>