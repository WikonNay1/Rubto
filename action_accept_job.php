<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'receiver') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = intval($_POST['job_id'] ?? 0);
    $receiver_id = $_SESSION['user_id'];

    if ($job_id > 0) {
        // 🔒 ตรวจสอบว่า Receiver มีงานค้างที่ยังไม่สำเร็จหรือไม่
        $stmt_check = $pdo->prepare("
            SELECT COUNT(*) as pending_count 
            FROM pickup_jobs 
            WHERE receiver_id = ? AND status IN ('ACCEPTED', 'ON_THE_WAY', 'ARRIVED')
        ");
        $stmt_check->execute([$receiver_id]);
        $has_pending_job = $stmt_check->fetch()['pending_count'] > 0;

        if ($has_pending_job) {
            $_SESSION['error'] = "คุณมีงานที่กำลังดำเนินการอยู่ ไม่สามารถรับงานซ้ำซ้อนได้จนกว่าจะส่งมอบงานเดิมเสร็จสิ้น!";
            header('Location: receiver_dashboard.php');
            exit;
        }

        // 🟢 เปลี่ยนสถานะเป็น ACCEPTED
        $stmt = $pdo->prepare("
            UPDATE pickup_jobs 
            SET receiver_id = ?, status = 'ACCEPTED' 
            WHERE id = ? AND status = 'OPEN'
        ");
        $stmt->execute([$receiver_id, $job_id]);
    }
}

header('Location: receiver_dashboard.php');
exit;