<?php
session_start();
require_once 'includes/config.php';

/**
 * ส่วนที่ 1: การตรวจสอบสิทธิ์ (Security Check)
 * เฉพาะผู้ใช้งานระดับ 'admin' เท่านั้นที่เข้าถึงหน้ารายงานสรุปผลได้
 */
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

/**
 * ส่วนที่ 2: ดึงข้อมูลประวัติการจองทั้งหมด (Data Fetching)
 * ใช้ JOIN 3 ตาราง: bookings (การจอง), users (ผู้ใช้งาน), fields (สนาม)
 * เพื่อแสดงชื่อคนและชื่อสนามแทนที่จะแสดงแค่ ID
 */
$stmt = $pdo->query("SELECT b.*, u.name, u.surname, f.field_name 
                    FROM bookings b 
                    JOIN users u ON b.user_id = u.user_id 
                    JOIN fields f ON b.field_id = f.field_id
                    ORDER BY b.booking_date DESC, b.start_time DESC");
$bookings = $stmt->fetchAll();

/**
 * ส่วนที่ 3: เตรียมข้อมูลสำหรับแสดงกราฟ (Statistics for Chart.js)
 * นับจำนวนการจองแยกตามสนาม เพื่อนำไปแสดงในรูปแบบ Pie/Doughnut Chart
 */
$chart_sql = "SELECT f.field_name, COUNT(b.booking_id) as total 
              FROM fields f 
              LEFT JOIN bookings b ON f.field_id = b.field_id 
              GROUP BY f.field_id";
$chart_stmt = $pdo->query($chart_sql);
$chart_results = $chart_stmt->fetchAll();

// แยกข้อมูล Field Name และ Total ใส่ Array เพื่อนำไปแปลงเป็น JSON สำหรับ JavaScript
$labels = [];
$counts = [];
foreach($chart_results as $row) {
    $labels[] = $row['field_name'];
    $counts[] = (int)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการจองสนาม - Badminton System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        body { font-family: 'Sarabun', sans-serif; transition: 0.3s; }
        [data-bs-theme="dark"] body { background-color: #121212 !important; }
        [data-bs-theme="dark"] .card { background-color: #2c2c2c !important; color: #fff; }
        [data-bs-theme="dark"] .table { color: #eee; }
        
        /* CSS สำหรับการพิมพ์: ซ่อนองค์ประกอบที่ไม่จำเป็นเมื่อสั่ง Print หรือเซฟเป็น PDF */
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="row mb-4 no-print">
            <div class="col-md-6 mx-auto">
                <div class="card shadow-sm border-0 p-4 text-center">
                    <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-pie-chart-fill me-2"></i>สถิติการจองแยกตามสนาม</h5>
                    <div style="max-height: 300px; display: flex; justify-content: center;">
                        <canvas id="bookingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <a href="dashboard.php" class="btn btn-outline-primary rounded-pill px-4"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
                <button onclick="window.print()" class="btn btn-primary shadow-sm rounded-pill px-4"><i class="bi bi-printer-fill me-1"></i> พิมพ์รายงาน (PDF)</button>
            </div>

            <div class="text-center mb-5">
                <h2 class="fw-bold mt-2">รายงานสรุปการจองสนามแบดมินตันทั้งหมด</h2>
                <p class="text-muted">ข้อมูลอัปเดต ณ วันที่ <?= date('d/m/Y H:i') ?> น.</p>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>ลำดับ</th>
                            <th>ผู้จอง</th>
                            <th>สนาม</th>
                            <th>วันที่จอง</th>
                            <th>ช่วงเวลา</th>
                            <th class="no-print">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach ($bookings as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="text-start ps-3"><?= htmlspecialchars($row['name'] . ' ' . $row['surname']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['field_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['booking_date'])) ?></td>
                                <td><?= substr($row['start_time'], 0, 5) ?> - <?= substr($row['end_time'], 0, 5) ?> น.</td>
                                <td class="no-print">
                                    <span class="badge <?= $row['status'] == 'confirmed' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> rounded-pill px-3">
                                        <?= $row['status'] == 'confirmed' ? 'ยืนยันแล้ว' : 'ยกเลิกแล้ว' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="py-5 text-muted">ยังไม่มีข้อมูลการจองในขณะนี้</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        /**
         * การสร้างกราฟ Chart.js
         * รับค่าจากตัวแปร PHP $labels และ $counts ผ่านฟังก์ชัน json_encode()
         */
        const ctx = document.getElementById('bookingChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut', // กราฟรูปโดนัท
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($counts); ?>,
                    backgroundColor: ['#0d6efd', '#20c997', '#ffca28', '#fd7e14', '#dc3545', '#6610f2'],
                    hoverOffset: 10
                }]
            },
            options: { 
                plugins: { 
                    legend: { position: 'bottom' } // แสดงคำอธิบายสนามไว้ด้านล่างกราฟ
                }, 
                responsive: true 
            }
        });

        /**
         * ระบบ Dark Mode (รักษาธีมเดิมจากหน้าที่แล้ว)
         */
        const htmlElement = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
    </script>
</body>
</html>