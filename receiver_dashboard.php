<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'receiver') {
    header('Location: login.php');
    exit;
}

$receiver_id = $_SESSION['user_id'];

// สรุปข้อมูลสถิติ & รายได้
$stmt_earnings = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN status = 'COMPLETED' THEN reward ELSE 0 END) as total_earnings,
        COUNT(CASE WHEN status = 'COMPLETED' THEN 1 END) as completed_jobs,
        COUNT(CASE WHEN status IN ('ACCEPTED', 'ON_THE_WAY', 'ARRIVED') THEN 1 END) as pending_jobs
    FROM pickup_jobs 
    WHERE receiver_id = ?
");
$stmt_earnings->execute([$receiver_id]);
$stats = $stmt_earnings->fetch();

$total_earnings = $stats['total_earnings'] ?? 0;
$completed_jobs = $stats['completed_jobs'] ?? 0;
$pending_jobs = $stats['pending_jobs'] ?? 0;

// 🟢 ดึงงานใหม่ที่กำลังเปิดรับ (OPEN) ทั้งหมด ไม่จำกัดจำนวน
$stmt_available = $pdo->prepare("
    SELECT pj.*, r.name as restaurant_name, b.table_number, b.guests, b.note
    FROM pickup_jobs pj
    JOIN bookings b ON pj.booking_id = b.id
    JOIN restaurants r ON b.restaurant_id = r.id
    WHERE pj.status = 'OPEN'
    ORDER BY pj.id DESC
");
$stmt_available->execute();
$available_jobs = $stmt_available->fetchAll();

// ดึงงานที่กำลังดำเนินการอยู่ (Active Jobs)
$stmt_active = $pdo->prepare("
    SELECT pj.*, r.name as restaurant_name, r.address, b.table_number, b.guests, b.note
    FROM pickup_jobs pj
    JOIN bookings b ON pj.booking_id = b.id
    JOIN restaurants r ON b.restaurant_id = r.id
    WHERE pj.receiver_id = ? AND pj.status IN ('ACCEPTED', 'ON_THE_WAY', 'ARRIVED')
    ORDER BY pj.id DESC
");
$stmt_active->execute([$receiver_id]);
$active_jobs = $stmt_active->fetchAll();

// 🟢 ดึง job_id งานล่าสุดที่กำลังดำเนินการ สำหรับปุ่มแชทลอยด้านนอก
$active_job_id_for_chat = count($active_jobs) > 0 ? $active_jobs[0]['id'] : null;

// ดึงประวัติงานย้อนหลัง
$stmt_history = $pdo->prepare("
    SELECT pj.*, r.name as restaurant_name, b.table_number
    FROM pickup_jobs pj
    JOIN bookings b ON pj.booking_id = b.id
    JOIN restaurants r ON b.restaurant_id = r.id
    WHERE pj.receiver_id = ? AND pj.status = 'COMPLETED'
    ORDER BY pj.id DESC LIMIT 5
");
$stmt_history->execute([$receiver_id]);
$history_jobs = $stmt_history->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดผู้รับงาน - RUBTO</title>
    <!-- 🔄 Auto Refresh ทุกๆ 10 วินาที เพื่อดึงงานใหม่เข้าหน้าจอทันที -->
    <meta http-equiv="refresh" content="10">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } } } }</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- 🔔 เพิ่ม SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-dark text-white min-h-screen p-4 pb-24 font-sans">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-center border-b border-gray-800 pb-4">
            <div>
                <h1 class="text-2xl font-black text-white">แดชบอร์ดผู้รับโต๊ะ</h1>
                <p class="text-xs text-gray-400">จัดการงานรับโต๊ะของคุณ (อัปเดตอัตโนมัติทุก 10 วิ)</p>
            </div>
            <a href="logout.php" class="bg-red-950/50 border border-red-800 text-red-400 hover:bg-red-900 text-xs px-3 py-2 rounded-xl font-bold flex items-center gap-1 transition">
                <i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ
            </a>
        </div>

        <!-- Stats Panel -->
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-card border border-gray-800 p-4 rounded-2xl">
                <span class="text-[10px] text-gray-400 uppercase font-bold block mb-1">รายได้สะสม</span>
                <span class="text-xl md:text-2xl font-black text-emerald-400">฿<?= number_format($total_earnings) ?></span>
            </div>
            <div class="bg-card border border-gray-800 p-4 rounded-2xl">
                <span class="text-[10px] text-gray-400 uppercase font-bold block mb-1">งานที่กำลังทำ</span>
                <span class="text-xl md:text-2xl font-black text-amber-400"><?= $pending_jobs ?> <span class="text-xs text-gray-500 font-normal">งาน</span></span>
            </div>
            <div class="bg-card border border-gray-800 p-4 rounded-2xl">
                <span class="text-[10px] text-gray-400 uppercase font-bold block mb-1">งานที่สำเร็จ</span>
                <span class="text-xl md:text-2xl font-black text-blue-400"><?= $completed_jobs ?> <span class="text-xs text-gray-500 font-normal">ครั้ง</span></span>
            </div>
        </div>

        <!-- Active Jobs Section -->
        <div>
            <h2 class="text-sm font-bold text-gray-300 mb-3 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-spinner text-amber-400 animate-spin"></i> งานที่คุณกำลังดำเนินการ
            </h2>

            <?php if (count($active_jobs) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($active_jobs as $job): ?>
                        <div class="bg-card border border-amber-500/40 p-5 rounded-2xl shadow-xl space-y-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="bg-amber-500/20 text-amber-400 text-[10px] font-black px-2.5 py-1 rounded-full border border-amber-500/30 uppercase">
                                        สถานะ: <?= $job['status'] ?>
                                    </span>
                                    <h3 class="text-lg font-black text-white mt-2"><?= htmlspecialchars($job['restaurant_name']) ?></h3>
                                    <p class="text-xs text-gray-400"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($job['address'] ?: 'ไม่ระบุที่อยู่') ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 block font-bold">หมายเลขโต๊ะ</span>
                                    <span class="text-2xl font-black text-accent"><?= htmlspecialchars($job['table_number']) ?></span>
                                </div>
                            </div>

                            <div class="bg-black/50 p-3 rounded-xl text-xs space-y-1 text-gray-300 border border-gray-800">
                                <p><strong>จำนวน:</strong> <?= $job['guests'] ?> ท่าน | <strong>ค่าตอบแทน:</strong> <span class="text-emerald-400 font-bold">฿<?= number_format($job['reward'] ?? 0) ?></span></p>
                                <p><strong>หมายเหตุ:</strong> <?= htmlspecialchars($job['note'] ?: 'ไม่มี') ?></p>
                            </div>

                            <!-- ปุ่มจัดการงาน & ปุ่มแชทในการ์ด -->
                            <div class="flex gap-2 pt-1">
                                <a href="job_tracking.php?id=<?= $job['id'] ?>" class="flex-1 bg-gray-800 hover:bg-gray-700 text-white text-xs py-2.5 rounded-xl font-bold text-center border border-gray-700 transition">
                                    <i class="fa-solid fa-route mr-1"></i> อัปเดตสถานะ / ติดตามงาน
                                </a>
                                <a href="chat.php?job_id=<?= $job['id'] ?>" class="bg-blue-600 hover:bg-blue-500 text-white text-xs px-4 py-2.5 rounded-xl font-bold flex items-center justify-center gap-1 transition shadow-lg shadow-blue-950">
                                    <i class="fa-solid fa-comments"></i> แชท
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-card border border-gray-800 p-6 rounded-2xl text-center text-xs text-gray-500">
                    ไม่มีงานที่คุณกำลังดำเนินการอยู่
                </div>
            <?php endif; ?>
        </div>

        <!-- Available Jobs Section -->
        <div>
            <h2 class="text-sm font-bold text-gray-300 mb-3 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-bell text-accent"></i> งานใหม่ล่าสุดที่รอรับโต๊ะ (<?= count($available_jobs) ?> รายการ)
            </h2>

            <?php if (count($available_jobs) > 0): ?>
                <div class="space-y-3">
                    <?php foreach ($available_jobs as $job): ?>
                        <div class="bg-card border border-gray-800 p-4 rounded-2xl flex justify-between items-center">
                            <div class="space-y-1">
                                <span class="bg-emerald-500/20 text-emerald-400 text-[9px] font-black px-2 py-0.5 rounded border border-emerald-500/30 uppercase">
                                    งานใหม่
                                </span>
                                <h3 class="font-bold text-base text-white">
                                    <?= htmlspecialchars($job['restaurant_name']) ?> <span class="text-accent">(โต๊ะ <?= htmlspecialchars($job['table_number']) ?>)</span>
                                </h3>
                                <p class="text-xs text-gray-400">สำหรับ <?= $job['guests'] ?> ท่าน | ค่าตอบแทน: <span class="text-emerald-400 font-bold">฿<?= number_format($job['reward'] ?? 0) ?></span></p>
                            </div>

                            <?php if ($pending_jobs > 0): ?>
                                <button type="button" onclick="showLockedAlert()" class="bg-gray-800 text-gray-500 font-bold text-xs px-3 py-2.5 rounded-xl border border-gray-700 flex items-center gap-1">
                                    <i class="fa-solid fa-lock"></i> ติดงานเดิมอยู่
                                </button>
                            <?php else: ?>
                                <form action="action_accept_job.php" method="POST" onsubmit="return confirmAcceptJob(event, this)">
                                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-1 shadow-lg shadow-emerald-950">
                                        <i class="fa-solid fa-check"></i> รับงาน
                                    </button>
                                </form>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-card border border-gray-800 p-6 rounded-2xl text-center text-xs text-gray-500">
                    ขณะนี้ยังไม่มีงานใหม่ประกาศเข้ามา
                </div>
            <?php endif; ?>
        </div>

        <!-- History Section -->
        <div>
            <h2 class="text-sm font-bold text-gray-300 mb-3 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-gray-400"></i> ประวัติงานที่เสร็จสิ้นล่าสุด
            </h2>
            
            <div class="bg-card border border-gray-800 rounded-2xl p-4 space-y-2">
                <?php if (count($history_jobs) > 0): ?>
                    <?php foreach ($history_jobs as $hj): ?>
                        <div class="flex justify-between items-center bg-black/40 p-3 rounded-xl text-xs border border-gray-800/60">
                            <div>
                                <p class="font-bold text-white"><?= htmlspecialchars($hj['restaurant_name']) ?> (โต๊ะ <?= htmlspecialchars($hj['table_number']) ?>)</p>
                                <p class="text-[10px] text-gray-500">เวลา: <?= date('d/m/Y H:i น.', strtotime($hj['created_at'])) ?></p>
                            </div>
                            <div class="text-right">
                                <span class="text-emerald-400 font-black block">+฿<?= number_format($hj['reward'] ?? 0) ?></span>
                                <span class="text-[9px] text-gray-400 bg-gray-800 px-2 py-0.5 rounded">สำเร็จ</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-xs text-gray-500 text-center py-3">ยังไม่มีประวัติงานเสร็จสิ้น</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- 💬 ปุ่มแชทด่วนลอย (Floating Chat Button) มุมขวาล่าง (แสดงผลเมื่อมีงานที่กำลังทำอยู่) -->
    <?php if ($active_job_id_for_chat): ?>
        <a href="chat.php?job_id=<?= $active_job_id_for_chat ?>" class="fixed bottom-6 right-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white p-4 rounded-full shadow-2xl shadow-blue-500/50 flex items-center gap-3 z-50 border border-blue-400/30 group transition-all duration-300 hover:scale-105">
            <div class="relative">
                <i class="fa-solid fa-comments text-xl"></i>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full animate-ping"></span>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full"></span>
            </div>
            <span class="font-black text-xs pr-1 hidden sm:inline">แชทคุยกับลูกค้า</span>
        </a>
    <?php endif; ?>

    <script>
        const darkSwal = Swal.mixin({
            background: '#1E1E1E',
            color: '#fff',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#374151'
        });

        function confirmAcceptJob(e, form) {
            e.preventDefault();
            darkSwal.fire({
                title: 'ยืนยันการรับงาน?',
                text: "เมื่อรับงานแล้ว กรุณาเดินทางไปแสตนบายที่ร้านตามเวลา",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'รับงานเลย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function showLockedAlert() {
            darkSwal.fire({
                title: 'ไม่สามารถรับงานเพิ่มได้!',
                text: 'คุณต้องทำรายการงานที่กำลังดำเนินการให้เสร็จสิ้นก่อน',
                icon: 'warning',
                confirmButtonColor: '#374151'
            });
        }

        // อ่านแจ้งเตือน Error
        <?php if (isset($_SESSION['error'])): ?>
            darkSwal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: '<?= htmlspecialchars($_SESSION['error']) ?>',
                icon: 'error'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>