<?php
session_start();
require_once 'config/db.php';

// ตรวจสอบสิทธิ์การเข้าถึง (ต้องล็อกอินและเป็นลูกค้า)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['user_id'];
    $restaurant_id = isset($_POST['restaurant_id']) ? intval($_POST['restaurant_id']) : 0;
    $table_number = trim($_POST['table_number'] ?? '');
    $guests = isset($_POST['guests']) ? intval($_POST['guests']) : 1;
    $note = trim($_POST['note'] ?? '');

    // ตรวจสอบข้อมูลเบื้องต้น
    if ($restaurant_id <= 0 || empty($table_number)) {
        header('Location: customer_dashboard.php?error=invalid_data');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. ตรวจสอบว่าโต๊ะนี้มีงานที่ยังดำเนินอยู่หรือไม่ (กันจองซ้ำ)
        $stmt_check = $pdo->prepare("
            SELECT pj.id 
            FROM pickup_jobs pj
            JOIN bookings b ON pj.booking_id = b.id
            WHERE b.restaurant_id = ? 
              AND b.table_number = ? 
              AND pj.status NOT IN ('COMPLETED', 'CANCELLED', 'EXPIRED')
        ");
        $stmt_check->execute([$restaurant_id, $table_number]);
        
        if ($stmt_check->fetch()) {
            $pdo->rollBack();
            header('Location: customer_dashboard.php?restaurant_id=' . $restaurant_id . '&error=table_taken');
            exit;
        }

        // 2. บันทึกข้อมูลลงตาราง bookings (ระบุสถานะ 'PENDING' ชัดเจน)
        $stmt_booking = $pdo->prepare("
            INSERT INTO bookings (customer_id, restaurant_id, table_number, guests, note, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'PENDING', NOW())
        ");
        $stmt_booking->execute([$customer_id, $restaurant_id, $table_number, $guests, $note]);
        $booking_id = $pdo->lastInsertId();

        // 3. สร้างงานใหม่ในตาราง pickup_jobs (สถานะ OPEN เพื่อรอ Runner กดรับงาน)
        $stmt_job = $pdo->prepare("
            INSERT INTO pickup_jobs (booking_id, customer_id, status, created_at) 
            VALUES (?, ?, 'OPEN', NOW())
        ");
        $stmt_job->execute([$booking_id, $customer_id]);

        $pdo->commit();

        // 🟢 สำเร็จ! ส่งผู้ใช้เด้งตรงไปยังหน้า my_tables.php ทันที
        header('Location: my_tables.php?msg=booked');
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // หากต้องการดูข้อความ Error จริง ให้ยกเลิกคอมเมนต์บรรทัดล่างนี้
        // die("Database Error: " . $e->getMessage());
        header('Location: customer_dashboard.php?restaurant_id=' . $restaurant_id . '&error=system_error');
        exit;
    }
} else {
    header('Location: customer_dashboard.php');
    exit;
}