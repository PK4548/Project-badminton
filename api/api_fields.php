<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, x-api-key");

// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล
require '../includes/config.php'; 

// --- 1. ตรวจสอบ API Key เพื่อความปลอดภัย ---
$headers = getallheaders();
$api_key = isset($headers['x-api-key']) ? $headers['x-api-key'] : '';

if ($api_key !== 'MY_SECRET_TOKEN_123') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'API Key ไม่ถูกต้อง!']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    /**
     * [GET] - ดึงข้อมูลสนามทั้งหมด
     * ใช้สำหรับแสดงรายชื่อสนามในตารางหน้าจัดการ
     */
    case 'GET': 
        try {
            $stmt = $pdo->query("SELECT * FROM fields ORDER BY field_id ASC");
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    /**
     * [POST] - เพิ่มสนามใหม่
     * รับข้อมูลเป็น JSON: { "name": "สนาม 9", "status": "available" }
     */
    case 'POST': 
        $data = json_decode(file_get_contents('php://input'), true);
        if(!empty($data['name'])) {
            $stmt = $pdo->prepare("INSERT INTO fields (field_name, status) VALUES (?, ?)");
            $stmt->execute([$data['name'], $data['status']]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ชื่อสนามห้ามว่าง']);
        }
        break;

    /**
     * [PUT] - แก้ไขข้อมูลสนาม (ชื่อ หรือ สถานะ)
     * แก้ไขแล้ว: รองรับทั้งการอัปเดต "ชื่อสนาม" และ "สถานะสนาม"
     */
    case 'PUT': 
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(!empty($data['id'])) {
            // กรณีที่ 1: อัปเดตชื่อสนาม (ถ้ามีการส่งค่า name มา)
            if(!empty($data['name'])) {
                $stmt = $pdo->prepare("UPDATE fields SET field_name = ? WHERE field_id = ?");
                $stmt->execute([$data['name'], $data['id']]);
                echo json_encode(['status' => 'success', 'message' => 'แก้ไขชื่อสนามสำเร็จ']);
            } 
            // กรณีที่ 2: อัปเดตสถานะสนาม (ถ้ามีการส่งค่า status มา)
            elseif(!empty($data['status'])) {
                $stmt = $pdo->prepare("UPDATE fields SET status = ? WHERE field_id = ?");
                $stmt->execute([$data['status'], $data['id']]);
                echo json_encode(['status' => 'success', 'message' => 'อัปเดตสถานะสำเร็จ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ข้อมูลที่ต้องการอัปเดตไม่ครบถ้วน']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID สนาม']);
        }
        break;

    /**
     * [DELETE] - ลบสนาม
     * รับค่า ID ผ่าน URL Parameter: ?id=1
     */
    case 'DELETE': 
        if(isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("DELETE FROM fields WHERE field_id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID ที่ต้องการลบ']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}
?>