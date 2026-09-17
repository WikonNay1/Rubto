<?php
session_start();
require_once 'config/db.php';

// 1. ตรวจสอบการล็อกอินของลูกค้า
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id   = $_SESSION['user_id']; // ดึง ID ลูกค้าจาก Session
    $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
    $table_number  = strtoupper(trim($_POST['table_number'] ?? ''));
    $guests        = intval($_POST['guests'] ?? 1);
    $note          = trim($_POST['note'] ?? '');

    // ตรวจสอบความถูกต้องของข้อมูล
    if ($restaurant_id <= 0 || empty($table_number)) {
        header('Location: my_bookings.php?error=invalid_data');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 2. ตรวจสอบว่าโต๊ะนี้มีคนจองไปก่อนแล้วหรือยัง (สถานะ PENDING หรือ CONFIRMED)
        $stmt_check = $pdo->prepare("
            SELECT id FROM bookings 
            WHERE restaurant_id = ? AND table_number = ? AND status IN ('PENDING', 'CONFIRMED')
        ");
        $stmt_check->execute([$restaurant_id, $table_number]);
        
        if ($stmt_check->fetch()) {
            $pdo->rollBack();
            header('Location: my_bookings.php?error=table_occupied');
            exit;
        }

        // 3. บันทึกข้อมูลการจองลงตาราง bookings (บันทึกทั้ง customer_id และ restaurant_id)
        $stmt = $pdo->prepare("
            INSERT INTO bookings (customer_id, restaurant_id, table_number, guests, note, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'PENDING', NOW())
        ");
        $stmt->execute([$customer_id, $restaurant_id, $table_number, $guests, $note]);

        $pdo->commit();

        // 4. เมื่อสำเร็จ ให้เด้งส่งต่อไปยังหน้า "โต๊ะที่ฉันจองไว้" (my_bookings.php)
        header('Location: my_bookings.php?msg=updated');
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Location: my_bookings.php?error=db_error');
        exit;
    }
} else {
    header('Location: customer_dashboard.php');
    exit;
}