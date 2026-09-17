<?php
session_start();
require_once 'config/db.php';

// ปิดการพ่น HTML error ออกมาปนกับ JSON
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

$restaurant_id = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : 0;

if ($restaurant_id <= 0) {
    echo json_encode(['success' => false, 'occupied_tables' => []]);
    exit;
}

try {
    // ดึงเฉพาะโต๊ะที่สถานะเป็น PENDING หรือ CONFIRMED (ตัด CANCELLED ออก)
    $stmt = $pdo->prepare("
        SELECT DISTINCT table_number 
        FROM bookings 
        WHERE restaurant_id = ? 
          AND status IN ('PENDING', 'CONFIRMED')
    ");
    $stmt->execute([$restaurant_id]);
    $occupied_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'occupied_tables' => $occupied_tables
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}