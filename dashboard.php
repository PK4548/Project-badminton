<?php
date_default_timezone_set('Asia/Bangkok');

session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once 'includes/config.php';
$user_id = $_SESSION['user_id'];
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ระบบจองสนามแบดมินตัน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css"> 
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        
        /* 1. แก้ไขส่วนพื้นหลัง: บังคับสีแยกตาม Theme */
        [data-bs-theme="light"] body { 
            background-color: #f8f9fa !important; 
            color: #212529 !important;
        }
        [data-bs-theme="dark"] body { 
            background-color: #121212 !important; 
            color: #ffffff !important;
        }

        body { font-family: 'Sarabun', sans-serif; transition: background-color 0.3s ease; }
        
        /* 2. สไตล์ Card สนาม (ล็อคสีเขียว/แดงให้ชัดเจน) */
        .card-status { 
            transition: transform 0.3s ease; 
            border: none !important; 
            cursor: pointer; 
        }
        .card-status:hover { transform: translateY(-8px); filter: brightness(1.1); }
        .card-status i, .card-status h5, .card-status p { color: white !important; }

        /* 3. ปรับแต่ง Modal ให้สวยในโหมดมืด */
        [data-bs-theme="dark"] .modal-content { background-color: #2c2c2c !important; color: #eee; }
        .modal-content { border-radius: 20px; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <?php if (empty($user['line_user_id'])): ?>
    <div class="alert alert-warning">
        คุณยังไม่ได้เชื่อมต่อ LINE เพื่อรับการแจ้งเตือน 
        <a href="line_login.php" class="btn btn-success btn-sm rounded-pill">
            <i class="bi bi-line"></i> เชื่อมต่อ LINE
        </a>
    </div>
    <?php else: ?>
    <div class="text-success small">
        <i class="bi bi-check-circle-fill"></i> เชื่อมต่อ LINE แล้ว (รับการแจ้งเตือนได้ปกติ)
    </div>
    <?php endif; ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="list-group shadow-sm border-0">
                    <a href="dashboard.php" class="list-group-item list-group-item-action active"><i class="bi bi-house"></i> หน้าหลัก</a>
                    <a href="booking.php" class="list-group-item list-group-item-action"><i class="bi bi-calendar-check"></i> จองสนาม</a>
                    <a href="history.php" class="list-group-item list-group-item-action"><i class="bi bi-clock-history"></i> ประวัติการจอง</a>
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <div class="mt-3 small text-muted px-3 fw-bold text-uppercase">Admin Management</div>
                        <a href="manage_fields.php" class="list-group-item list-group-item-action text-danger"><i class="bi bi-gear-fill"></i> จัดการสนาม (CRUD)</a>
                        <a href="manage_users.php" class="list-group-item list-group-item-action text-danger"><i class="bi bi-people-fill"></i> จัดการสมาชิก/สิทธิ์</a>
                        <a href="report.php" class="list-group-item list-group-item-action text-danger"><i class="bi bi-file-earmark-pdf-fill"></i> รายงานการจองทั้งหมด</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-9">
                <div class="mb-4">
                    <h3 class="fw-bold text-secondary mb-4"><i class="bi bi-activity text-primary me-2"></i>สถานะสนามวันนี้</h3>
                    
                    <div id="booking-status-container">
                        <div class="row">
                            <?php
                            $stmt = $pdo->query("SELECT * FROM fields");
                            $fields = $stmt->fetchAll();
                            foreach ($fields as $field) {
                                $now = date('H:i:s'); 
                                $today = date('Y-m-d');
                                $check_sql = "SELECT * FROM bookings WHERE field_id = ? AND booking_date = ? AND status = 'confirmed' AND (? BETWEEN start_time AND end_time)";
                                $check_stmt = $pdo->prepare($check_sql); 
                                $check_stmt->execute([$field['field_id'], $today, $now]);
                                $is_busy = $check_stmt->fetch();

                                if ($field['status'] == 'maintenance') { 
                                    $st = "ปิดปรับปรุง"; $color = "#6c757d"; $ic = "bi-wrench-adjustable"; 
                                } elseif ($is_busy) { 
                                    $st = "ไม่ว่าง (กำลังใช้งาน)"; $color = "#dc3545"; $ic = "bi-person-fill-lock"; 
                                } else { 
                                    $st = "ว่าง (พร้อมใช้งาน)"; $color = "#198754"; $ic = "bi-check-circle-fill"; 
                                }
                            ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card card-status h-100 shadow-sm" 
                                         style="background-color: <?php echo $color; ?> !important;"
                                         onclick="showSchedule(<?php echo $field['field_id']; ?>, '<?php echo $field['field_name']; ?>')">
                                        <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                                            <i class="bi <?php echo $ic; ?> display-5 mb-2"></i>
                                            <h5 class="card-title fw-bold"><?php echo $field['field_name']; ?></h5>
                                            <p class="card-text small mb-0" style="opacity: 0.9;"><?php echo $st; ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div> 
                    </div> 
                </div> 
            </div>
        </div>
    </div>

    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="modalFieldName">ตารางการจอง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive rounded-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>วันที่</th><th>เวลา</th><th class="text-center">สถานะ</th></tr>
                            </thead>
                            <tbody id="scheduleTableBody"></tbody>
                        </table>
                    </div>
                    <div id="noBookingMsg" class="text-center py-4 d-none">
                        <p class="text-muted">ยังไม่มีคิวการจองในขณะนี้</p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <a href="booking.php" id="directBookBtn" class="btn btn-primary rounded-pill px-4">จองสนามนี้</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSchedule(fieldId, fieldName) {
            document.getElementById('modalFieldName').innerText = fieldName;
            document.getElementById('directBookBtn').href = "booking.php?field_id=" + fieldId;
            const tableBody = document.getElementById('scheduleTableBody');
            const noMsg = document.getElementById('noBookingMsg');
            tableBody.innerHTML = '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';
            noMsg.classList.add('d-none');
            fetch('api/get_field_schedule.php?field_id=' + fieldId).then(res => res.json()).then(data => {
                tableBody.innerHTML = '';
                if (data.length === 0) { noMsg.classList.remove('d-none'); }
                else {
                    data.forEach(item => {
                        tableBody.innerHTML += `<tr><td>${item.date}</td><td>${item.start}-${item.end} น.</td><td class="text-center"><span class="badge bg-danger rounded-pill">จองแล้ว</span></td></tr>`;
                    });
                }
                new bootstrap.Modal(document.getElementById('scheduleModal')).show();
            });
        }

        function fetchStatus() {
            fetch(window.location.href).then(res => res.text()).then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newContent = doc.getElementById('booking-status-container').innerHTML;
                const container = document.getElementById('booking-status-container');
                if (container.innerHTML.trim() !== newContent.trim()) {
                    container.innerHTML = newContent;
                }
            });
        }
        setInterval(fetchStatus, 5000);

        // --- ส่วนจัดการ Theme (อัปเดตใหม่) ---
        function applyTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        }
        applyTheme();

        // ดักฟังการคลิกสลับโหมด
        window.addEventListener('click', (e) => {
            if (e.target.closest('#theme-toggle')) {
                setTimeout(applyTheme, 100);
            }
        });
    </script>
</body>
</html>