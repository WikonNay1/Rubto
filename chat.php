<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$job_id = intval($_GET['job_id'] ?? 0);

// ดึงข้อมูลงานเพื่อเช็กสิทธิ์เข้าห้องแชท
$stmt_job = $pdo->prepare("
    SELECT pj.*, r.name as restaurant_name, b.table_number 
    FROM pickup_jobs pj
    JOIN bookings b ON pj.booking_id = b.id
    JOIN restaurants r ON b.restaurant_id = r.id
    WHERE pj.id = ? AND (pj.customer_id = ? OR pj.receiver_id = ?)
");
$stmt_job->execute([$job_id, $user_id, $user_id]);
$job = $stmt_job->fetch();

if (!$job) {
    die("ไม่พบรายการงาน หรือคุณไม่มีสิทธิ์เข้าถึงห้องแชทนี้");
}

// เมื่อมีการส่งข้อความ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== '') {
        $stmt_send = $pdo->prepare("INSERT INTO job_messages (job_id, sender_id, message) VALUES (?, ?, ?)");
        $stmt_send->execute([$job_id, $user_id, $msg]);
        header("Location: chat.php?job_id=" . $job_id);
        exit;
    }
}

// ดึงข้อความแชททั้งหมด
$stmt_msg = $pdo->prepare("
    SELECT m.*, u.name as sender_name 
    FROM job_messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.job_id = ? 
    ORDER BY m.id ASC
");
$stmt_msg->execute([$job_id]);
$messages = $stmt_msg->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ห้องแชทติดต่องาน - RUBTO</title>
    <!-- รีเฟรชเฉพาะส่วนแชทอัตโนมัติทุก 3 วินาที -->
    <meta http-equiv="refresh" content="3">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-950 text-white min-h-screen flex flex-col font-sans">

    <!-- Header -->
    <div class="bg-gray-900 border-b border-gray-800 p-4 sticky top-0 z-40 flex justify-between items-center">
        <div>
            <h1 class="font-black text-base text-white">แชทติดต่องาน</h1>
            <p class="text-xs text-emerald-400"><?= htmlspecialchars($job['restaurant_name']) ?> (โต๊ะ <?= htmlspecialchars($job['table_number']) ?>)</p>
        </div>
        <button onclick="history.back()" class="bg-gray-800 hover:bg-gray-700 text-xs px-3 py-2 rounded-xl text-gray-300 border border-gray-700">
            <i class="fa-solid fa-xmark"></i> ปิด
        </button>
    </div>

    <!-- Message History -->
    <div class="flex-1 p-4 space-y-3 overflow-y-auto max-w-2xl w-full mx-auto">
        <?php if (count($messages) > 0): ?>
            <?php foreach ($messages as $m): ?>
                <?php $is_me = ($m['sender_id'] == $user_id); ?>
                <div class="flex flex-col <?= $is_me ? 'items-end' : 'items-start' ?>">
                    <span class="text-[10px] text-gray-500 mb-0.5"><?= htmlspecialchars($m['sender_name']) ?> • <?= date('H:i', strtotime($m['created_at'])) ?></span>
                    
                    <div class="max-w-[75%] p-3 rounded-2xl text-xs <?= $is_me ? 'bg-emerald-600 text-white rounded-br-none' : 'bg-gray-800 text-gray-200 rounded-bl-none border border-gray-700' ?>">
                        
                        <?php if (strpos($m['message'], '[รูปถ่ายหลักฐาน]') !== false || strpos($m['message'], 'uploads/') !== false): ?>
                            <?php 
                                // แยกเอา path รูปภาพออกมาจากข้อความ
                                preg_match('/uploads\/[^\s]+/', $m['message'], $matches);
                                $img_src = $matches[0] ?? '';
                            ?>
                            <?php if ($img_src): ?>
                                <p class="font-bold mb-1.5 text-[11px] flex items-center gap-1">
                                    <i class="fa-solid fa-camera"></i> รูปถ่ายหลักฐาน
                                </p>
                                <a href="<?= htmlspecialchars($img_src) ?>" target="_blank" class="block overflow-hidden rounded-xl border border-white/20 hover:opacity-90 transition">
                                    <img src="<?= htmlspecialchars($img_src) ?>" alt="รูปหลักฐาน" class="w-full max-h-60 object-cover rounded-xl">
                                </a>
                            <?php else: ?>
                                <?= nl2br(htmlspecialchars($m['message'])) ?>
                            <?php endif; ?>

                        <?php else: ?>
                            <?= nl2br(htmlspecialchars($m['message'])) ?>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center text-xs text-gray-500 py-10">
                ยังไม่มีข้อความ เริ่มต้นพิมพ์คุยกันได้เลย!
            </div>
        <?php endif; ?>
    </div>
    <!-- Message Input Form -->
    <div class="bg-gray-900 border-t border-gray-800 p-3 sticky bottom-0">
        <form action="" method="POST" class="max-w-2xl mx-auto flex gap-2">
            <input type="text" name="message" placeholder="พิมพ์ข้อความที่นี่..." required autocomplete="off" class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-5 py-2.5 rounded-xl flex items-center gap-1 shadow-lg shadow-emerald-950">
                <i class="fa-solid fa-paper-plane"></i> ส่ง
            </button>
        </form>
    </div>

</body>
</html>