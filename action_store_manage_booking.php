<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $table_number = trim($_POST['table_number'] ?? '');
    $guests = intval($_POST['guests'] ?? 1);
    $status = $_POST['status'] ?? 'PENDING';
    $note = trim($_POST['note'] ?? '');

    if ($booking_id > 0) {
        // แก้ไข: เอา booking_time ออก เนื่องจากไม่มีคอลัมน์นี้ในฐานข้อมูล
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET table_number = ?, guests = ?, status = ?, note = ? 
            WHERE id = ?
        ");
        $stmt->execute([$table_number, $guests, $status, $note, $booking_id]);
    }
}

header('Location: manage_bookings.php');
exit;