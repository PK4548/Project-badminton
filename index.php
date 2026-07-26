<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badminton Booking System | ระบบจองสนามแบดมินตัน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Sarabun', sans-serif; transition: 0.3s; }
        
        /* สไตล์ปุ่ม Sun/Moon สำหรับหน้า Index (ปรับความชัดเจน) */
        #theme-toggle-index {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(13, 110, 253, 0.2); /* เพิ่มสีน้ำเงินจางๆ */
            backdrop-filter: blur(8px);
            border-radius: 50%;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.5);
            font-size: 1.6rem;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* สีไอคอนเมื่ออยู่ในโหมดสว่าง (อยู่บนพื้นน้ำเงิน) */
        [data-bs-theme="light"] #theme-icon-dark { color: #FFD700 !important; } 
        /* สีไอคอนเมื่ออยู่ในโหมดมืด */
        [data-bs-theme="dark"] #theme-icon-light { color: #ffca28 !important; }

        .hero-section {
            background: linear-gradient(rgba(13, 110, 253, 0.85), rgba(11, 94, 215, 0.95)), 
                        url('https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            transition: 0.5s;
        }

        [data-bs-theme="dark"] .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.9), rgba(20, 20, 20, 0.95)), 
                        url('https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=2070&auto=format&fit=crop');
        }

        /* ปรับสีพื้นหลัง Content ด้านล่างในโหมดมืด */
        [data-bs-theme="dark"] body { background-color: #121212 !important; }
        [data-bs-theme="dark"] .feature-card { background-color: #2c2c2c; border: 1px solid #444; }

        .btn-landing {
            padding: 12px 35px;
            font-size: 1.1rem;
            border-radius: 50px;
            font-weight: 700;
            transition: 0.3s;
        }
        .btn-landing:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }

        .feature-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border-radius: 20px;
            background-color: #f0f7ff;
            color: #0d6efd;
            font-size: 2rem;
            transition: 0.3s;
        }
    </style>
</head>
<body>

    <div id="theme-toggle-index" title="สลับโหมดสว่าง/มืด">
        <i class="bi bi-sun-fill d-none" id="theme-icon-light"></i>
        <i class="bi bi-moon-stars-fill" id="theme-icon-dark"></i>
    </div>

    <header class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 text-center text-lg-start">
                    <div class="mb-4 no-print">
                        <span class="badge rounded-pill bg-white text-primary px-3 py-2 fw-bold">
                            <i class="bi bi-house-door-fill me-1"></i> หน้าหลักระบบจอง
                        </span>
                    </div>
                    <h1 class="display-3 fw-bold mb-3">ยกระดับการจองสนามแบดมินตันของคุณ</h1>
                    <p class="lead mb-5 fs-4 opacity-90">ระบบจองออนไลน์ที่รวดเร็ว แม่นยำ และแจ้งเตือนทันใจ ไม่พลาดทุกนัดสำคัญ</p>
                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center justify-content-lg-start">
                        <a href="login.php" class="btn btn-light btn-landing text-primary shadow">เข้าสู่ระบบ / จองสนาม</a>
                        <a href="register.php" class="btn btn-outline-light btn-landing">สมัครสมาชิกใหม่</a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/3252/3252924.png" class="img-fluid" alt="Badminton Icon" style="max-height: 380px; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.3));">
                </div>
            </div>
        </div>
    </header>

    <section class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">ทำไมต้องใช้ระบบของเรา?</h2>
                <div class="bg-primary mx-auto mt-2" style="width: 60px; height: 4px; border-radius: 2px;"></div>
            </div>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card p-4 h-100 shadow-sm border-0">
                        <div class="feature-icon mb-4 shadow-sm"><i class="bi bi-lightning-charge"></i></div>
                        <h4 class="fw-bold">รวดเร็ว & แม่นยำ</h4>
                        <p class="text-muted px-lg-4 small">ตรวจสอบสถานะสนามได้ทันทีแบบ Real-time ป้องกันการจองซ้ำซ้อน 100%</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card p-4 h-100 shadow-sm border-0">
                        <div class="feature-icon mb-4 shadow-sm"><i class="bi bi-bell"></i></div>
                        <h4 class="fw-bold">ระบบแจ้งเตือน</h4>
                        <p class="text-muted px-lg-4 small">รับแจ้งเตือนผ่าน LINE ทันทีหลังจากกดจองสนามสำเร็จ</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card p-4 h-100 shadow-sm border-0">
                        <div class="feature-icon mb-4 shadow-sm"><i class="bi bi-shield-check"></i></div>
                        <h4 class="fw-bold">UI สมัยใหม่</h4>
                        <p class="text-muted px-lg-4 small">รองรับ Dark Mode ถนอมสายตา และมีระบบจัดการที่ง่ายสำหรับทุกคน</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        const themeToggle = document.getElementById('theme-toggle-index');
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

        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateIcons(savedTheme);

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