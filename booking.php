<?php
// บรรทัดนี้สำคัญมาก เพื่อให้วันที่ตรงกับเมืองไทย
date_default_timezone_set('Asia/Bangkok'); 

session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

// ดึงรายชื่อสนามที่ว่างมาแสดงใน Select Option
$fields = $pdo->query("SELECT * FROM fields WHERE status = 'available'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จองสนาม - Badminton System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        
        body { 
            font-family: 'Sarabun', sans-serif; 
            transition: background-color 0.3s ease, color 0.3s ease; 
        }

        [data-bs-theme="light"] body { background-color: #f8f9fa !important; color: #212529 !important; }
        [data-bs-theme="dark"] body { background-color: #121212 !important; color: #ffffff !important; }

        .card { border-radius: 20px; border: none; transition: 0.3s; }
        [data-bs-theme="dark"] .card { background-color: #2c2c2c !important; }

        .form-control[readonly] {
            background-color: rgba(0, 0, 0, 0.05) !important;
            cursor: not-allowed;
        }
        [data-bs-theme="dark"] .form-control[readonly] {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg pt-4 no-print">
        <div class="container d-flex justify-content-between">
            <a href="dashboard.php" class="btn btn-outline-primary rounded-pill px-4">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
            <button class="btn btn-link nav-link p-0" id="theme-toggle" type="button">
                <i class="bi bi-sun-fill d-none" id="theme-icon-light"></i>
                <i class="bi bi-moon-stars-fill" id="theme-icon-dark"></i>
            </button>
        </div>
    </nav>

    <div class="container py-4">
        <div class="card shadow-lg mx-auto" style="max-width: 550px;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-calendar-check text-primary display-4"></i>
                    <h3 class="fw-bold mt-2">จองสนามแบดมินตัน</h3>
                    <p id="booking-desc" class="text-muted small">กรุณาตรวจสอบข้อมูลการจองของคุณ</p>
                </div>

                <form id="bookingForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกสนาม</label>
                        <select name="field_id" id="fieldSelect" class="form-select form-select-lg" required>
                            <option value="" disabled selected>-- เลือกสนาม --</option>
                            <?php foreach($fields as $field): ?>
                                <option value="<?= $field['field_id'] ?>"><?= $field['field_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">วันที่ต้องการจอง</label>
                        <input type="date" name="booking_date" id="booking_date" class="form-control form-control-lg" min="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">เวลาเริ่ม</label>
                            <input type="time" name="start_time" id="start_time" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">เวลาสิ้นสุด</label>
                            <input type="time" name="end_time" id="end_time" class="form-control form-control-lg" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold mt-3 shadow-sm rounded-pill">
                        ยืนยันการทำรายการจอง
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ประกาศตัวแปรไว้ด้านบนเพื่อให้เข้าถึงได้ทุกฟังก์ชัน
        const startInput = document.getElementById('start_time');
        const endInput = document.getElementById('end_time');
        const dateInput = document.getElementById('booking_date');
        const descText = document.getElementById('booking-desc');
        const selectElement = document.getElementById('fieldSelect');

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const fieldId = urlParams.get('field_id');
            
            // 1. ตั้งวันที่เป็นวันนี้
            dateInput.value = "<?= date('Y-m-d') ?>";

            // 2. ตรวจสอบพารามิเตอร์สนาม
            if (fieldId) {
                selectElement.value = fieldId;
                
                const now = new Date();
                let h = now.getHours();
                if (now.getMinutes() > 0) h += 1; 
                if (h >= 24) h = 0;

                const start = `${h.toString().padStart(2, '0')}:00`;
                let eh = h + 2;
                if (eh >= 24) eh -= 24;
                const end = `${eh.toString().padStart(2, '0')}:00`;

                startInput.value = start;
                endInput.value = end;
                
                startInput.readOnly = true;
                endInput.readOnly = true;
                descText.innerText = "ระบบคำนวณเวลาจอง 2 ชม. ให้ท่านอัตโนมัติ";
            } else {
                startInput.readOnly = false;
                endInput.readOnly = false;
                descText.innerText = "กรุณาเลือกสนามและเวลาที่ท่านต้องการ";
            }
        });

        // ระบบ Theme
        const themeToggle = document.getElementById('theme-toggle');
        function applyTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            const lightIcon = document.getElementById('theme-icon-light');
            const darkIcon = document.getElementById('theme-icon-dark');
            if (savedTheme === 'dark') {
                lightIcon.classList.remove('d-none');
                darkIcon.classList.add('d-none');
            } else {
                lightIcon.classList.add('d-none');
                darkIcon.classList.remove('d-none');
            }
        }
        applyTheme();
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            applyTheme();
        });

        // ฟังก์ชันยิงพลุ
        function fireConfetti() {
            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 }
            });
        }

        // ระบบส่งฟอร์ม
        document.getElementById('bookingForm').onsubmit = function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'ยืนยันการจอง?',
                text: `ช่วงเวลา ${startInput.value} - ${endInput.value} น.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                confirmButtonColor: '#0d6efd',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังประมวลผล...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    const formData = new FormData(this);
                    fetch('api/save_booking.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success') {
                            fireConfetti(); 
                            Swal.fire({
                                title: 'สำเร็จ!',
                                text: 'จองสนามเรียบร้อยแล้ว ',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => window.location.href = 'dashboard.php');
                        } else {
                            Swal.fire('ผิดพลาด', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                    });
                }
            });
        };
    </script>
</body>
</html>