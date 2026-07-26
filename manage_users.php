<?php
// เริ่มต้น Session เพื่อใช้งานข้อมูลการล็อกอิน
session_start();
// ดึงไฟล์เชื่อมต่อฐานข้อมูล
require_once 'includes/config.php';

/**
 * 1. ระบบรักษาความปลอดภัย (Security Check)
 * ตรวจสอบว่าผู้ที่เข้ามาหน้านี้มีสิทธิ์เป็น 'admin' หรือไม่
 * หากไม่มีสิทธิ์ หรือไม่ได้ล็อกอิน ให้ดีดกลับไปหน้า Dashboard ทันที
 */
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

/**
 * 2. การดึงข้อมูลผู้ใช้งาน (Fetch Users)
 * ดึงรายชื่อผู้ใช้ทั้งหมดจากตาราง 'users' 
 * โดยใช้เงื่อนไข user_id != ? เพื่อ "ไม่แสดงชื่อตัวเอง" ในรายการ (ป้องกันแอดมินเผลอเปลี่ยนสิทธิ์ตัวเองจนเข้าหน้าจัดการไม่ได้)
 */
$stmt = $pdo->prepare("SELECT user_id, name, surname, email, role FROM users WHERE user_id != ?");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสิทธิ์ผู้ใช้งาน - Badminton System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        body { font-family: 'Sarabun', sans-serif; transition: 0.3s; }
        
        /* ตกแต่งหน้าจอสำหรับ Dark Mode */
        [data-bs-theme="dark"] body { background-color: #121212 !important; }
        [data-bs-theme="dark"] .card { background-color: #2c2c2c !important; color: #fff; }
        [data-bs-theme="dark"] .table { color: #eee; }
        [data-bs-theme="dark"] .form-select { background-color: #333; color: #fff; border-color: #444; }
        
        .card { border-radius: 15px; border: none; }
    </style>
</head>
<body class="bg-light">

    <?php include 'includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="card shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary"></i> จัดการสิทธิ์ผู้ใช้งาน</h3>
                    <p class="text-muted small">เปลี่ยนบทบาทและสิทธิ์การเข้าถึงเมนูต่างๆ ของสมาชิก</p>
                </div>
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left"></i> กลับหน้าหลัก
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ชื่อ-นามสกุล</th>
                            <th>อีเมล</th>
                            <th>สิทธิ์ปัจจุบัน</th>
                            <th class="text-center">เปลี่ยนสิทธิ์เป็น</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['name'] . " " . $user['surname']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php 
                                        /**
                                         * ส่วนกำหนดสี Badge ตามประเภทสิทธิ์
                                         * Admin = สีแดง (Danger)
                                         * Staff = สีส้ม (Warning)
                                         * Student/ทั่วไป = สีฟ้า (Info)
                                         */
                                        $role = strtolower($user['role']);
                                        $badgeClass = 'bg-info';
                                        if($role == 'admin') $badgeClass = 'bg-danger';
                                        if($role == 'staff') $badgeClass = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-3">
                                        <?= strtoupper($user['role']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <select class="form-select form-select-sm d-inline-block w-auto rounded-pill" 
                                            onchange="updateRole(<?= $user['user_id'] ?>, this.value)">
                                        <option value="student" <?= $role == 'student' ? 'selected' : '' ?>>Student</option>
                                        <option value="staff" <?= $role == 'staff' ? 'selected' : '' ?>>Staff</option>
                                        <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm rounded-circle" onclick="deleteUser(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['name']) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">ไม่พบผู้ใช้งานอื่นในระบบ</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    /**
     * --- 3. ระบบจัดการหน้าบ้าน (Client-side Logic) ---
     */

    // ตั้งค่าธีมสีตามที่ผู้ใช้เคยเลือกไว้ในเบราว์เซอร์
    const htmlElement = document.documentElement;
    htmlElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');

    /**
     * ฟังก์ชัน: updateRole (อัปเดตสิทธิ์ผู้ใช้)
     * ทำหน้าที่ส่งข้อมูล userId และ newRole ไปยังไฟล์ API หลังบ้านโดยไม่ต้องรีโหลดหน้าเว็บ
     */
    function updateRole(userId, newRole) {
        // แสดงกล่องยืนยันการทำรายการด้วย SweetAlert2
        Swal.fire({
            title: 'ยืนยันการเปลี่ยนสิทธิ์?',
            text: `คุณกำลังจะเปลี่ยนสิทธิ์ผู้ใช้เป็น ${newRole.toUpperCase()}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // หากกดยืนยัน: เตรียมข้อมูลส่งแบบ URL Encoded (รองรับ $_POST ใน PHP)
                const params = new URLSearchParams();
                params.append('user_id', userId);
                params.append('role', newRole);

                // สั่งประมวลผลข้อมูลไปยัง API
                fetch('api/update_role_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params
                })
                .then(res => res.json()) // แปลงผลลัพธ์ที่ได้เป็น JSON
                .then(data => {
                    if (data.status === 'success') {
                        // แจ้งเตือนสำเร็จ และรีโหลดหน้าเพื่ออัปเดต Badge สีสิทธิ์
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'อัปเดตสิทธิ์เรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        // กรณี API แจ้ง Error กลับมา
                        Swal.fire('ผิดพลาด!', data.message || 'เกิดข้อผิดพลาดจากเซิร์ฟเวอร์', 'error');
                    }
                })
                .catch(err => {
                    // กรณีเชื่อมต่อเซิร์ฟเวอร์ไม่ได้ (เช่น เน็ตหลุด หรือ Path ไฟล์ผิด)
                    console.error('Fetch Error:', err);
                    Swal.fire('Error', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ (404 หรือ Path ผิด)', 'error');
                });
            } else {
                // หากกดยกเลิก: รีโหลดหน้าเพื่อรีเซ็ตค่าใน Select กลับเป็นค่าเดิมในฐานข้อมูล
                location.reload(); 
            }
        });
    }
    function deleteUser(userId, userName) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `คุณกำลังจะลบผู้ใช้ "${userName}" ออกจากระบบ ข้อมูลนี้ไม่สามารถกู้คืนได้!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ยืนยันการลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            const params = new URLSearchParams();
            params.append('user_id', userId);

            fetch('api/delete_user_process.php', { // เรียกไปยังไฟล์ API ใหม่
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'ลบสำเร็จ!',
                        text: 'ข้อมูลผู้ใช้งานถูกลบออกจากระบบแล้ว',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('ผิดพลาด!', data.message || 'ไม่สามารถลบข้อมูลได้', 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                Swal.fire('Error', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
            });
        }
        });
    }
    </script>
</body>
</html>