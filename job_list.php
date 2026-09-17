<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'receiver') {
    header('Location: login.php');
    exit;
}

$receiver_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';

// เช็กว่า Receiver มีงานค้างอยู่หรือไม่
$stmt_check = $pdo->prepare("
    SELECT COUNT(*) as pending_count 
    FROM pickup_jobs 
    WHERE receiver_id = ? AND status IN ('ACCEPTED', 'ON_THE_WAY', 'ARRIVED')
");
$stmt_check->execute([$receiver_id]);
$has_pending_job = $stmt_check->fetch()['pending_count'] > 0;

$query = "
    SELECT pj.*, 
           b.table_number, 
           b.guests, 
           b.note, 
           b.created_at as booking_time, 
           r.name as restaurant_name, 
           r.address
    FROM pickup_jobs pj
    JOIN bookings b ON pj.booking_id = b.id
    JOIN restaurants r ON b.restaurant_id = r.id
    WHERE pj.status = 'OPEN'
";

if ($search) {
    $query .= " AND r.name LIKE :search";
}
$query .= " ORDER BY pj.id DESC";

$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการงานที่เปิดรับ - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } } } }</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-dark text-white min-h-screen p-4">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">ตลาดงานรับโต๊ะด่วน</h1>
            <a href="receiver_dashboard.php" class="text-xs text-gray-400 hover:text-white">กลับหน้าแดชบอร์ด</a>
        </div>

        <form method="GET" class="mb-6">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="ค้นหาชื่อร้านหรือสถานที่..." class="w-full bg-card border border-gray-800 rounded-xl p-3 text-sm text-white focus:border-accent outline-none">
        </form>

        <div class="grid md:grid-cols-2 gap-4">
            <?php foreach ($jobs as $job): ?>
                <div class="bg-card border border-gray-800 p-5 rounded-2xl flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg text-white"><?= htmlspecialchars($job['restaurant_name']) ?></h3>
                            <span class="text-accent font-extrabold text-lg">฿<?= number_format($job['reward'] ?? 0) ?></span>
                        </div>
                        <p class="text-xs text-gray-400 mb-3"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($job['address'] ?? 'ไม่ระบุสถานที่') ?></p>
                        
                        <div class="bg-black/40 p-3 rounded-xl space-y-1 text-xs text-gray-300 mb-4">
                            <p><strong>โต๊ะหมายเลข:</strong> <span class="text-accent font-bold"><?= htmlspecialchars($job['table_number']) ?></span> (สำหรับ <?= $job['guests'] ?> ท่าน)</p>
                            <p><strong>หมายเหตุ:</strong> <?= htmlspecialchars($job['note'] ?: 'ไม่มี') ?></p>
                        </div>
                    </div>

                    <?php if ($has_pending_job): ?>
                        <button disabled class="w-full bg-gray-800 text-gray-500 font-bold py-2.5 rounded-xl text-center text-xs cursor-not-allowed border border-gray-700">
                            <i class="fa-solid fa-lock mr-1"></i> ต้องจบงานเก่าก่อนจึงรับงานนี้ได้
                        </button>
                    <?php else: ?>
                        <form action="action_accept_job.php" method="POST">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <button type="submit" class="w-full bg-accent hover:bg-red-600 font-bold py-2.5 rounded-xl text-center text-xs text-white transition">
                                กดรับงานนี้
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($jobs)): ?>
                <div class="col-span-2 text-center py-12 text-gray-500 bg-card rounded-2xl border border-gray-800">
                    ยังไม่มีรายการงานที่เปิดหาคนรับโต๊ะในขณะนี้
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>