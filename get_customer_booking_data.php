<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json; charset=utf-8');

$customer_id = $_SESSION['user_id'] ?? 0;
$restaurant_id = intval($_GET['restaurant_id'] ?? 0);

if ($customer_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. ดึงข้อมูลโต๊ะที่ไม่ว่างของร้าน
    $stmt_occupied = $pdo->prepare("
        SELECT DISTINCT table_number 
        FROM bookings 
        WHERE restaurant_id = ? AND status IN ('PENDING', 'CONFIRMED')
    ");
    $stmt_occupied->execute([$restaurant_id]);
    $occupied_tables = $stmt_occupied->fetchAll(PDO::FETCH_COLUMN);

    // 2. ดึงการจองล่าสุดที่ยังใช้งานอยู่ของลูกค้ารายนี้
    $stmt_active = $pdo->prepare("
        SELECT b.*, pj.status as runner_status, r.name as restaurant_name
        FROM bookings b
        JOIN restaurants r ON b.restaurant_id = r.id
        LEFT JOIN pickup_jobs pj ON b.id = pj.booking_id
        WHERE b.customer_id = ? AND b.status IN ('PENDING', 'CONFIRMED')
        ORDER BY b.id DESC LIMIT 1
    ");
    $stmt_active->execute([$customer_id]);
    $active_booking = $stmt_active->fetch(PDO::FETCH_ASSOC);

    // 3. ดึงประวัติการจองย้อนหลัง ( completed / cancelled )
    $stmt_history = $pdo->prepare("
        SELECT b.*, r.name as restaurant_name
        FROM bookings b
        JOIN restaurants r ON b.restaurant_id = r.id
        WHERE b.customer_id = ? AND b.status IN ('COMPLETED', 'CANCELLED')
        ORDER BY b.id DESC LIMIT 10
    ");
    $stmt_history->execute([$customer_id]);
    $history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'occupied_tables' => $occupied_tables,
        'active_booking' => $active_booking ?: null,
        'history' => $history
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}