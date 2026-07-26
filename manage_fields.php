<?php
session_start();
// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล
require_once 'includes/config.php';

/**
 * 1. ระบบรักษาความปลอดภัย (Security Check)
 * ตรวจสอบว่าผู้ใช้มีสิทธิ์เป็น 'admin' หรือไม่
 * ถ้าไม่มีสิทธิ์ หรือไม่ได้ Login ให้ดีดกลับหน้า Dashboard ทันที
 */
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสนาม - Badminton System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        body { font-family: 'Sarabun', sans-serif; transition: 0.3s; }
        
        /* สไตล์เสริมสำหรับ Dark Mode เพื่อให้อ่านตารางง่ายขึ้น */
        [data-bs-theme="dark"] body { background-color: #121212 !important; }
        [data-bs-theme="dark"] .card { background-color: #2c2c2c !important; color: #fff; }
        [data-bs-theme="dark"] .table { color: #eee; }
        
        .card { border-radius: 15px; border: none; }
        .badge { font-weight: 500; }
    </style>
</head>
<body class="bg-light">

    <?php include 'includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="card shadow-sm p-4 text-dark">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-primary mb-0"><i class="bi bi-grid-3x3-gap-fill me-2"></i>รายชื่อสนามแบดมินตัน</h4>
                    <small class="text-muted">จัดการข้อมูลสถานะสนามแบบ Real-time</small>
                </div>
                <button class="btn btn-success rounded-pill px-4 shadow-sm" onclick="addField()">
                    <i class="bi bi-plus-circle me-1"></i> เพิ่มสนามใหม่
                </button>
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left"></i> กลับหน้าหลัก
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="10%" class="ps-3 text-center">ID</th>
                            <th width="35%">ชื่อสนาม</th>
                            <th width="20%">สถานะ</th>
                            <th width="35%" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="fieldTableBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    /**
     * API_KEY: รหัสลับสำหรับการเรียกใช้งาน API 
     * (ต้องตรงกับในไฟล์ api_fields.php เพื่อความปลอดภัย)
     */
    const API_KEY = 'MY_SECRET_TOKEN_123';

    /**
     * --- 1. ฟังก์ชันโหลดข้อมูลสนาม (GET) ---
     * เรียกใช้งาน api/api_fields.php เพื่อดึงข้อมูลสนามทั้งหมดมาแสดงในตาราง
     */
    function loadFields() {
        const tableBody = document.getElementById('fieldTableBody');
        
        fetch('api/api_fields.php', {
            headers: { 'x-api-key': API_KEY }
        })
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (!data || data.length === 0) {
                html = '<tr><td colspan="4" class="text-center py-5 text-muted">ยังไม่มีข้อมูลสนามในระบบ</td></tr>';
            } else {
                data.forEach(field => {
                    // กำหนด Badge สถานะ: สีเขียว (ว่าง) / สีแดง (ปิดปรับปรุง)
                    const isAvailable = field.status === 'available';
                    const statusBadge = isAvailable 
                        ? '<span class="badge bg-success-subtle text-success px-3 rounded-pill border border-success-subtle">พร้อมใช้งาน</span>' 
                        : '<span class="badge bg-danger-subtle text-danger px-3 rounded-pill border border-danger-subtle">ปิดปรับปรุง</span>';
                    
                    // ปุ่มสลับสถานะ (ถ้าว่าง -> ให้กดปิด, ถ้าปิด -> ให้กดเปิด)
                    const toggleBtn = isAvailable
                        ? `<button class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1" onclick="updateStatus(${field.field_id}, 'maintenance')"><i class="bi bi-pause-circle"></i> ปิด</button>`
                        : `<button class="btn btn-sm btn-outline-success rounded-pill px-3 me-1" onclick="updateStatus(${field.field_id}, 'available')"><i class="bi bi-play-circle"></i> เปิด</button>`;

                    // สร้างแถวตารางพร้อมปุ่มแก้ไขและลบ
                    html += `<tr>
                        <td class="text-center text-muted small">#${field.field_id}</td>
                        <td class="fw-bold">${field.field_name}</td>
                        <td>${statusBadge}</td>
                        <td class="text-center">
                            ${toggleBtn}
                            <button class="btn btn-sm btn-warning rounded-pill px-3 me-1" onclick="editFieldName(${field.field_id}, '${field.field_name}')">
                                <i class="bi bi-pencil-square"></i> แก้ไข
                            </button>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="deleteField(${field.field_id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }
            tableBody.innerHTML = html;
        })
        .catch(err => {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">เกิดข้อผิดพลาดในการโหลดข้อมูลสนาม</td></tr>';
        });
    }

    /**
     * --- 2. ฟังก์ชันแก้ไขสถานะสนาม (PUT) ---
     * ส่งคำสั่งไปเปลี่ยนสถานะสนาม (ว่าง/ปิดปรับปรุง) โดยไม่รีโหลดหน้าเว็บ
     */
    function updateStatus(id, newStatus) {
        fetch('api/api_fields.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'x-api-key': API_KEY },
            body: JSON.stringify({ id: id, status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') loadFields(); // โหลดตารางใหม่หลังทำรายการสำเร็จ
        });
    }

    /**
     * --- 3. ฟังก์ชันแก้ไขชื่อสนาม (PUT) ---
     * ใช้ SweetAlert2 รับค่าชื่อใหม่ แล้วส่งไปอัปเดตที่ API
     */
    function editFieldName(id, currentName) {
        Swal.fire({
            title: 'แก้ไขชื่อสนาม',
            input: 'text',
            inputValue: currentName,
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก',
            inputValidator: (value) => { if (!value) return 'ชื่อสนามห้ามว่าง!' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('api/api_fields.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'x-api-key': API_KEY },
                    body: JSON.stringify({ id: id, name: result.value })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        Swal.fire('สำเร็จ!', 'แก้ไขชื่อสนามเรียบร้อย', 'success');
                        loadFields();
                    }
                });
            }
        });
    }

    /**
     * --- 4. ฟังก์ชันลบสนาม (DELETE) ---
     * แจ้งเตือนยืนยันก่อนลบข้อมูลสนามออกจากฐานข้อมูลถาวร
     */
    function deleteField(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลสนามนี้จะถูกลบถาวร!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบข้อมูล'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`api/api_fields.php?id=${id}`, { 
                    method: 'DELETE',
                    headers: { 'x-api-key': API_KEY }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        Swal.fire('ลบสำเร็จ!', '', 'success');
                        loadFields();
                    }
                });
            }
        })
    }

    /**
     * --- 5. ฟังก์ชันเพิ่มสนามใหม่ (POST) ---
     * รับค่าชื่อสนามใหม่และส่งไปบันทึกลงฐานข้อมูล
     */
    function addField() {
        Swal.fire({
            title: 'เพิ่มสนามใหม่',
            input: 'text',
            inputLabel: 'ระบุชื่อสนามที่ต้องการเพิ่ม',
            showCancelButton: true,
            inputValidator: (value) => { if (!value) return 'กรุณาระบุชื่อสนาม!' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('api/api_fields.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'x-api-key': API_KEY },
                    body: JSON.stringify({ name: result.value, status: 'available' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('สำเร็จ!', 'เพิ่มสนามใหม่เรียบร้อย', 'success');
                        loadFields();
                    }
                });
            }
        });
    }

    /**
     * ส่วนการทำงานเริ่มต้นเมื่อโหลดหน้าเว็บ
     * - ตั้งค่าธีมสี (Theme) ตามที่เก็บไว้ใน LocalStorage
     * - เรียกใช้งาน loadFields() เพื่อดึงข้อมูลมาแสดงครั้งแรก
     */
    const htmlElement = document.documentElement;
    htmlElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');

    loadFields();
    </script>
</body>
</html>