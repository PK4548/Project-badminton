<?php
session_start();
require_once 'includes/config.php';
//require_once './api/save_booking.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id']; 
echo $user_id;

//เรียกใช้ Function เพื่อดึงจำนวนการจองที่สำเร็จ
$sql_total = "SELECT count_user_bookings(?) AS total_confirmed";
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute([$user_id]);
$total_confirmed = $stmt_total->fetchColumn();

// $sql = "SELECT b.*, f.field_name 
//         FROM bookings b 
//         JOIN fields f ON b.field_id = f.field_id 
//         WHERE b.user_id = user_id
//         ORDER BY b.booking_date DESC, b.start_time DESC";
$sql = "SELECT * from view_booking_details1 where user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการจอง - Badminton System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        body { 
            font-family: 'Sarabun', sans-serif; 
            transition: background-color 0.3s ease; 
        }
        
        [data-bs-theme="dark"] body { background-color: #121212 !important; }
        [data-bs-theme="dark"] .card { background-color: #2c2c2c !important; color: #fff; }

        .card { border-radius: 15px; border: none; }

        /* สไตล์ตารางและการตีเส้นขอบสำหรับหน้าจอ */
        .table-print { border-collapse: collapse !important; width: 100%; }
        .table-print th, .table-print td { border: 1px solid #dee2e6 !important; }

        @media print {
            .no-print { display: none !important; }
            .container { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .card { box-shadow: none !important; border: none !important; }
            body { background-color: white !important; color: black !important; }
            
            /* การตีเส้นตารางให้ชัดเจนตอนพิมพ์ */
            .table-print { border: 2px solid #000 !important; }
            .table-print th, .table-print td { 
                border: 1px solid #000 !important; 
                color: black !important;
                padding: 10px !important;
            }
            .table-dark { background-color: #f2f2f2 !important; color: black !important; }
            
            /* ปรับแต่ง Badge ให้เป็นข้อความปกติพร้อมวงเล็บสถานะเพื่อให้ชัดเจน */
            .badge { 
                background-color: transparent !important; 
                color: black !important; 
                border: none !important;
                font-weight: bold !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; 
    ?>

    <div class="container py-4">
        <div class="card shadow-sm p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <a href="dashboard.php" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับหน้าหลัก
                </a>
                
                <button onclick="window.print()" class="btn btn-primary shadow-sm rounded-pill">
                    <i class="bi bi-printer"></i> พิมพ์รายงาน (PDF)
                </button>
            </div>

            <div class="text-center mb-5">
                <h2 class="fw-bold text-primary">ประวัติการจองสนามของฉัน</h2>
                    <div class="mb-2">
                        <span class="badge bg-success p-2 rounded-pill">
                            <i class="bi bi-check-circle-fill"></i> จองสำเร็จทั้งหมด: <?= $total_confirmed ?> ครั้ง
                            <br>
                            <?php //echo $user_id; 
                            //echo $booking_id;
                            ?>
                        </span>
                    </div>
                <p class="text-muted small">ข้อมูลสรุป ณ วันที่ <?= date('d/m/Y H:i') ?> น.</p>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center table-print">
                    <thead class="table-dark">
                        <tr>
                            <th width="8%">ลำดับ</th>
                            <th>สนาม</th>
                            <th>วันที่จอง</th>
                            <th>ช่วงเวลา</th>
                            <th width="15%">สถานะ</th> 
                            <th class="no-print" width="15%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($my_bookings) > 0): ?>
                            <?php foreach ($my_bookings as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['field_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['booking_date'])) ?></td>
                                <td><?= substr($row['start_time'], 0, 5) ?> - <?= substr($row['end_time'], 0, 5) ?> น.</td>
                               
                                <td>
                                    <?php if($row['status'] == 'confirmed'): ?>
                                        <span class="badge text-success">จองสำเร็จ</span>
                                    <?php else: ?>
                                        <span class="badge text-danger">ยกเลิกแล้ว</span>
                                    <?php endif; ?>
                                </td>

                                <td class="no-print">
                                    <?php if($row['status'] == 'confirmed'): ?>
                                        <button class="btn btn-sm btn-danger px-3 shadow-sm rounded-pill" onclick="cancelBooking(<?= $row['booking_id'] ?>)">
                                            <i class="bi bi-x-circle"></i> ยกเลิก
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-4 text-muted text-center">ยังไม่มีประวัติการจองในขณะนี้</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end small text-muted p-3 border-top">
                Badminton Booking System - ระบบรายงานส่วนบุคคล
            </div>
        </div>
    </div>

    <script>
        // ระบบ Dark/Light Mode
        const themeToggle = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
        }

        // ฟังก์ชันยกเลิกการจอง
        function cancelBooking(id) {
            Swal.fire({
                title: 'ยืนยันการยกเลิก?',
                text: "รายการนี้จะถูกยกเลิกและไม่สามารถคืนค่าได้",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'ใช่, ยกเลิกเลย',
                cancelButtonText: 'เปลี่ยนใจ'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('api/cancel_process.php?id=' + id) 
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success') {
                            Swal.fire({ title: 'สำเร็จ!', text: 'ยกเลิกเรียบร้อยแล้ว', icon: 'success' }).then(() => location.reload());
                        } else {
                            Swal.fire('ผิดพลาด!', 'ไม่สามารถทำรายการได้', 'error');
                        }
                    })
                    .catch(err => Swal.fire('ผิดพลาด!', 'การเชื่อมต่อมีปัญหา', 'error'));
                }
            });
        }
    </script>
</body>
</html>