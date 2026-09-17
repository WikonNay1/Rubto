<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_tables.php');
    exit;
}

$customer_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$booking_id = intval($_POST['booking_id'] ?? 0);

if ($booking_id <= 0) {
    header('Location: my_tables.php');
    exit;
}

// ตรวจสอบว่าเป็นรายการจองของลูกค้ารายนี้จริง และสถานะยังเป็น PENDING หรือ CONFIRMED
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND customer_id = ? AND status IN ('PENDING', 'CONFIRMED')");
$stmt->execute([$booking_id, $customer_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: my_tables.php?error=not_found');
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. ยกเลิกการจอง
    if ($action === 'cancel') {
        // อัปเดตสถานะการจองเป็น CANCELLED เพื่อให้หลุดจากรายการ Active ไปอยู่ประวัติย้อนหลัง
        $stmt_cancel = $pdo->prepare("UPDATE bookings SET status = 'CANCELLED' WHERE id = ?");
        $stmt_cancel->execute([$booking_id]);

        // ยกเลิกงาน Runner ที่ค้างอยู่ (ถ้ามี) ให้เป็น CANCELLED ทั้งหมด
        $stmt_job = $pdo->prepare("UPDATE pickup_jobs SET status = 'CANCELLED' WHERE booking_id = ?");
        $stmt_job->execute([$booking_id]);

        $pdo->commit();
        header('Location: my_tables.php?msg=cancelled');
        exit;
    }

    // 2. แก้ไขข้อมูล (จำนวนคน / เปลี่ยนโต๊ะ)
    if ($action === 'edit') {
        $new_guests = intval($_POST['guests'] ?? $booking['guests']);
        $new_table = trim($_POST['table_number'] ?? $booking['table_number']);
        $new_note = trim($_POST['note'] ?? $booking['note']);

        // ตรวจสอบว่าโต๊ะใหม่ซ้ำกับคนอื่นหรือไม่ (กรณีเปลี่ยนโต๊ะ)
        if ($new_table !== $booking['table_number']) {
            $stmt_check = $pdo->prepare("SELECT id FROM bookings WHERE restaurant_id = ? AND table_number = ? AND status IN ('PENDING', 'CONFIRMED') AND id != ?");
            $stmt_check->execute([$booking['restaurant_id'], $new_table, $booking_id]);
            if ($stmt_check->fetch()) {
                $pdo->rollBack();
                header('Location: my_tables.php?error=table_occupied');
                exit;
            }
        }

        // อัปเดตข้อมูลการจอง
        $stmt_update = $pdo->prepare("UPDATE bookings SET guests = ?, table_number = ?, note = ? WHERE id = ?");
        $stmt_update->execute([$new_guests, $new_table, $new_note, $booking_id]);

        $pdo->commit();
        header('Location: my_tables.php?msg=updated');
        exit;
    }

    $pdo->rollBack();
    header('Location: my_tables.php');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: my_tables.php?error=db_error');
    exit;
}