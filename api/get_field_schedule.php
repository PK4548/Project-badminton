<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if (!isset($_GET['field_id'])) {
    echo json_encode([]);
    exit();
}

$field_id = $_GET['field_id'];
$today = date('Y-m-d');

try {
    // ดึงคิวการจองของ "วันนี้" ที่สถานะเป็น "confirmed"
    $sql = "SELECT booking_date, start_time, end_time 
            FROM bookings 
            WHERE field_id = ? 
            AND booking_date = ? 
            AND status = 'confirmed' 
            ORDER BY start_time ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$field_id, $today]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'date' => date('d/m/Y', strtotime($row['booking_date'])),
            'start' => substr($row['start_time'], 0, 5), // เอาแค่ HH:mm
            'end' => substr($row['end_time'], 0, 5)     // เอาแค่ HH:mm
        ];
    }

    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode([]);
}