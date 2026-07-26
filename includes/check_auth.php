<?php

// ไฟล์นี้เอาไว้เช็คว่าถ้ายังไม่ Login ให้เด้งไปหน้า login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ฟังก์ชันสำหรับจำกัดสิทธิ์หน้าเว็บ
function checkRole($allowed_roles) {
    if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
        header("Location: no_access.php"); // หรือเด้งไปหน้าแจ้งเตือนว่าไม่มีสิทธิ์
        exit();
    }
}

/* ตัวอย่างการใช้งานในหน้า Dashboard */
// session_start();
// checkRole(['admin', 'staff']); // เฉพาะ Admin และ Staff เท่านั้นที่เข้าหน้านี้ได้
?>