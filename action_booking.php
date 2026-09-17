<?php
session_start();
require_once 'config/db.php';

// ตรวจสอบสิทธิ์เบื้องต้นว่าเข้าสู่ระบบหรือยัง
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$action = $_REQUEST['action'] ?? '';
$restaurant_id = intval($_REQUEST['restaurant_id'] ?? 0);
$user_role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    // -------------------------------------------------------------
    // 1. กรณี: ลูกค้ากดจองโต๊ะ (CUSTOMER BOOKING) - เพิ่มเข้ามาใหม่
    // -------------------------------------------------------------
    if ($action === 'customer_book' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $table_number = strtoupper(trim($_POST['table_number'] ?? ''));
        $guests = intval($_POST['guests'] ?? 1);
        $note = trim($_POST['note'] ?? '');

        if ($restaurant_id > 0 && !empty($table_number)) {
            $pdo->beginTransaction();

            // บันทึกการจองโดยกำหนดสถานะเป็น 'PENDING' (รอยืนยันจากทางร้าน)
            $stmt = $pdo->prepare("
                INSERT INTO bookings (customer_id, restaurant_id, table_number, guests, note, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'PENDING', NOW())
            ");
            $stmt->execute([$user_id, $restaurant_id, $table_number, $guests, $note]);

            $pdo->commit();
            header("Location: my_tables.php?msg=booked");
            exit;
        }
        
        header("Location: customer_dashboard.php?error=invalid_data");
        exit;
    }

    // ตรวจสอบสิทธิ์เฉพาะร้านค้า (Merchant/Restaurant) สำหรับฟังก์ชันด้านล่าง
    if ($user_role !== 'restaurant') {
        header('Location: login.php');
        exit;
    }

    // -------------------------------------------------------------
    // 2. กรณี: ร้านค้าเพิ่มการจอง (ADD Walk-in / Manual)
    // -------------------------------------------------------------
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $table_number = strtoupper(trim($_POST['table_number']));
        $guests = intval($_POST['guests'] ?? 1);
        $status = $_POST['status'] ?? 'CONFIRMED';
        $note = trim($_POST['note'] ?? '');
        $customer_name = trim($_POST['customer_name'] ?? '');

        if (empty($note) && !empty($customer_name)) {
            $note = "Walk-in: " . $customer_name;
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO bookings (customer_id, restaurant_id, table_number, guests, note, status, created_at)
            VALUES (NULL, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$restaurant_id, $table_number, $guests, $note, $status]);
        $booking_id = $pdo->lastInsertId();

        $job_status = ($status === 'CANCELLED') ? 'CANCELLED' : 'ARRIVED';
        $stmt_job = $pdo->prepare("
            INSERT INTO pickup_jobs (booking_id, customer_id, status, created_at)
            VALUES (?, NULL, ?, NOW())
        ");
        $stmt_job->execute([$booking_id, $job_status]);

        $pdo->commit();
        header("Location: manage_bookings.php?success=added");
        exit;
    }

    // -------------------------------------------------------------
    // 3. กรณี: ร้านค้าแก้ไขการจอง (EDIT)[cite: 11]
    // -------------------------------------------------------------
    elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $booking_id = intval($_POST['booking_id']);
        $table_number = strtoupper(trim($_POST['table_number']));
        $guests = intval($_POST['guests'] ?? 1);
        $status = $_POST['status'] ?? 'PENDING';
        $note = trim($_POST['note'] ?? '');

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET table_number = ?, guests = ?, status = ?, note = ? 
            WHERE id = ? AND restaurant_id = ?
        ");
        $stmt->execute([$table_number, $guests, $status, $note, $booking_id, $restaurant_id]);

        if ($status === 'CANCELLED') {
            $stmt_job = $pdo->prepare("UPDATE pickup_jobs SET status = 'CANCELLED' WHERE booking_id = ?");
            $stmt_job->execute([$booking_id]);
        }

        $pdo->commit();
        header("Location: manage_bookings.php?success=edited");
        exit;
    }

    // -------------------------------------------------------------
    // 4. กรณี: ร้านค้ายกเลิกการจอง (CANCEL)[cite: 11]
    // -------------------------------------------------------------
    elseif ($action === 'cancel') {
        $booking_id = intval($_GET['id']);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE bookings SET status = 'CANCELLED' WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$booking_id, $restaurant_id]);

        $stmt_job = $pdo->prepare("UPDATE pickup_jobs SET status = 'CANCELLED' WHERE booking_id = ?");
        $stmt_job->execute([$booking_id]);

        $pdo->commit();
        header("Location: manage_bookings.php?success=cancelled");
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header("Location: manage_bookings.php?error=db_error");
    exit;
}