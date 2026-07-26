<?php
// เริ่มต้นใช้งาน Session (แม้หน้านี้จะยังไม่ได้ใช้เก็บค่า แต่ใส่ไว้เป็นมาตรฐาน)
session_start();
// ดึงไฟล์ตั้งค่าการเชื่อมต่อฐานข้อมูล
require_once 'includes/config.php';

/**
 * ส่วนที่ 1: ประมวลผลเมื่อมีการส่งฟอร์ม (Method POST)
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจาก input และทำความสะอาดข้อมูลเพื่อป้องกัน XSS
    $title      = htmlspecialchars($_POST['title']);
    $name       = htmlspecialchars($_POST['name']);
    $surname    = htmlspecialchars($_POST['surname']);
    $student_id = htmlspecialchars($_POST['student_id']);
    
    // กรองอีเมลให้ถูกต้องตามรูปแบบ
    $email      = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password   = $_POST['password'];
    $confirm_pw = $_POST['confirm_password'];

    /**
     * ส่วนที่ 2: ตรวจสอบความถูกต้องของข้อมูล (Validation)
     */
    if ($password !== $confirm_pw) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } elseif (strlen($password) < 6) {
        $error = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    } else {
        // ตรวจสอบว่ามี Email หรือรหัสนักศึกษานี้ในระบบแล้วหรือยัง
        $check = $pdo->prepare("SELECT email FROM users WHERE email = ? OR student_id = ?");
        $check->execute([$email, $student_id]);
        
        if ($check->rowCount() > 0) {
            $error = "อีเมลหรือรหัสนักศึกษานี้ถูกใช้งานแล้ว";
        } else {
            /**
             * ส่วนที่ 3: การบันทึกข้อมูล (Database Operations)
             */
            // เข้ารหัสผ่านก่อนบันทึก (Hash Password) เพื่อความปลอดภัยขั้นสูงสุด
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // เตรียมคำสั่ง SQL สำหรับเพิ่มข้อมูล
            $sql = "INSERT INTO users (title, name, surname, student_id, email, password, role) 
                    VALUES (?, ?, ?, ?, ?, ?, 'student')";
            $stmt = $pdo->prepare($sql);
            
            // บันทึกข้อมูลลงฐานข้อมูล
            if ($stmt->execute([$title, $name, $surname, $student_id, $email, $hashed_password])) {
                // หากสำเร็จ: พ่น JavaScript เพื่อแจ้งเตือนด้วย SweetAlert2 และย้ายหน้าไป Login
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'ลงทะเบียนสำเร็จ!',
                            text: 'คุณสามารถเข้าสู่ระบบได้ทันที',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = 'login.php';
                        });
                    });
                </script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน - Badminton System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        body { 
            font-family: 'Sarabun', sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            padding: 40px 0;
            transition: 0.3s;
        }

        /* ตกแต่ง UI ตามโหมดธีม */
        [data-bs-theme="dark"] body { background-color: #121212 !important; }
        [data-bs-theme="dark"] .reg-card { background-color: #2c2c2c !important; color: #fff; }

        .reg-card { 
            max-width: 650px; 
            width: 100%; 
            margin: auto; 
            border-radius: 20px; 
            border: none; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        }

        /* ปรับแต่งตำแหน่งปุ่มเมนูคงที่ */
        #theme-toggle { position: fixed; top: 20px; right: 20px; cursor: pointer; font-size: 1.5rem; z-index: 1000; }
        .back-home { position: fixed; top: 20px; left: 20px; z-index: 1000; }
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

    <div class="card reg-card p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">สร้างบัญชีใหม่</h2>
            <p class="text-muted">เข้าร่วมเป็นส่วนหนึ่งของ Badminton Booking System</p>
        </div>

        <form method="POST">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold small">คำนำหน้า</label>
                    <select name="title" class="form-select rounded-pill" required>
                        <option value="นาย">นาย</option>
                        <option value="นาง">นาง</option>
                        <option value="นางสาว">นางสาว</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold small">ชื่อ</label>
                    <input type="text" name="name" class="form-control rounded-pill" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold small">นามสกุล</label>
                    <input type="text" name="surname" class="form-control rounded-pill" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">รหัสนักศึกษา / รหัสประจำตัว</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 rounded-start-pill"><i class="bi bi-card-text"></i></span>
                    <input type="text" name="student_id" class="form-control border-start-0 ps-0 rounded-end-pill" maxlength="13" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">อีเมล</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 rounded-start-pill"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0 rounded-end-pill" placeholder="example@mail.com" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small">รหัสผ่าน (6 ตัวขึ้นไป)</label>
                    <input type="password" name="password" class="form-control rounded-pill" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small">ยืนยันรหัสผ่าน</label>
                    <input type="password" name="confirm_password" class="form-control rounded-pill" required>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger py-2 border-0 shadow-sm small text-center rounded-pill">
                    <i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-success w-100 py-2 fw-bold mt-3 rounded-pill shadow">
                ลงทะเบียนสมาชิก <i class="bi bi-check-lg"></i>
            </button>
            
            <div class="text-center mt-4">
                <p class="small text-muted">มีบัญชีอยู่แล้ว? <a href="login.php" class="text-primary fw-bold text-decoration-none">เข้าสู่ระบบที่นี่</a></p>
            </div>
        </form>
    </div>

    <script>
        /**
         * ส่วนที่ 4: ระบบ Dark/Light Mode
         * จัดการเรื่องการสลับไอคอนและบันทึกธีมลงใน LocalStorage
         */
        const themeToggle = document.getElementById('theme-toggle');
        const iconLight = document.getElementById('theme-icon-light');
        const iconDark = document.getElementById('theme-icon-dark');
        const htmlElement = document.documentElement;

        function updateIcons(theme) {
            if (theme === 'dark') {
                iconLight.classList.remove('d-none');
                iconDark.classList.add('d-none');
            } else {
                iconLight.classList.add('d-none');
                iconDark.classList.remove('d-none');
            }
        }

        // โหลดธีมที่เคยใช้ล่าสุด
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateIcons(savedTheme);

        // ดักจับการคลิกปุ่มสลับธีม
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