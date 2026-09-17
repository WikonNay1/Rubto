<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $customer_id = $_SESSION['user_id'];
    $reward = floatval($_POST['reward'] ?? 0); // รับค่า reward จาก Modal my_tables.php

    if ($booking_id > 0 && $reward > 0) {
        try {
            $pdo->beginTransaction();

            // 1. เช็กว่าเคยสร้างรายการใน pickup_jobs หรือยัง
            $stmt_check = $pdo->prepare("SELECT id FROM pickup_jobs WHERE booking_id = ?");
            $stmt_check->execute([$booking_id]);
            $existing = $stmt_check->fetch();

            if ($existing) {
                // 2. ถ้ามีอยู่แล้ว ให้อัปเดต reward, เปลี่ยน status เป็น OPEN และล้าง receiver_id
                $stmt_update = $pdo->prepare("
                    UPDATE pickup_jobs 
                    SET reward = ?, 
                        status = 'OPEN', 
                        receiver_id = NULL, 
                        proof_image = NULL,
                        accepted_at = NULL,
                        arrived_at = NULL,
                        confirmed_at = NULL,
                        created_at = NOW() 
                    WHERE id = ?
                ");
                $stmt_update->execute([$reward, $existing['id']]);
            } else {
                // 3. ถ้ายังไม่มี ให้สร้างแถวใหม่พร้อม status = 'OPEN'
                $stmt_insert = $pdo->prepare("
                    INSERT INTO pickup_jobs (booking_id, customer_id, reward, status, created_at) 
                    VALUES (?, ?, ?, 'OPEN', NOW())
                ");
                $stmt_insert->execute([$booking_id, $customer_id, $reward]);
            }

            // 4. อัปเดตสถานะในตาราง bookings
            $stmt_booking = $pdo->prepare("UPDATE bookings SET status = 'FINDING_RUNNER' WHERE id = ?");
            $stmt_booking->execute([$booking_id]);

            $pdo->commit();
            header('Location: my_tables.php?msg=runner_created');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: my_tables.php?msg=error');
            exit;
        }
    }
}

header('Location: my_tables.php');
exit;