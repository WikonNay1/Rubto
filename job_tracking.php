<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$job_id = $_GET['id'] ?? 0;

// 🟢 แก้ไข SQL ดึงข้อมูลรายละเอียดการจองให้ครบถ้วน
$stmt = $pdo->prepare("
    SELECT pj.*, 
           b.table_number, 
           b.guests, 
           b.note, 
           b.created_at as booking_time,
           r.name as restaurant_name, 
           r.address as restaurant_address
    FROM pickup_jobs pj
    JOIN bookings b ON pj.booking_id = b.id
    JOIN restaurants r ON b.restaurant_id = r.id
    WHERE pj.id = ?
");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    die("<div style='color: white; background: #121212; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: sans-serif;'>ไม่พบข้อมูลรายการนี้</div>");
}

// กำหนดเวลาหมดอายุ (หากใน DB ไม่มี expire_time ให้บวกเพิ่ม 30 นาทีจากเวลาสร้าง)
$expire_timestamp = !empty($job['expire_time']) 
    ? strtotime($job['expire_time']) 
    : strtotime($job['created_at']) + (30 * 60);

// ลิสต์สถานะสำหรับทำ Stepper Timeline
$statuses = ['ACCEPTED', 'ON_THE_WAY', 'ARRIVED', 'COMPLETED'];
$current_status = $job['status'];
$current_step = array_search($current_status, $statuses);
if ($current_step === false) $current_step = 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตามสถานะงาน - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { accent: '#FF3B30', dark: '#0F0F12', card: '#18181C', borderCard: '#2A2A30' }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-dark text-white min-h-screen p-4 pb-12 font-sans flex flex-col justify-center items-center">

    <div class="w-full max-w-md space-y-5">

        <!-- Top Navigation -->
        <div class="flex justify-between items-center">
            <a href="<?= $_SESSION['role'] === 'receiver' ? 'receiver_dashboard.php' : 'my_tables.php' ?>" class="bg-card border border-borderCard text-gray-400 hover:text-white px-3 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition">
                <i class="fa-solid fa-chevron-left"></i> กลับย้อนกลับ
            </a>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">JOB ID: #<?= sprintf('%04d', $job['id']) ?></span>
        </div>

        <!-- Main Card Status -->
        <div class="bg-card border border-borderCard rounded-3xl p-6 shadow-2xl relative overflow-hidden space-y-6">
            
            <div class="text-center border-b border-gray-800/80 pb-5">
                <span class="bg-red-500/10 text-accent text-[10px] font-black px-3 py-1 rounded-full uppercase border border-red-500/20 tracking-wider">
                    <?= htmlspecialchars($job['restaurant_name']) ?>
                </span>
                <h1 class="text-4xl font-black text-white mt-2 tracking-tight">
                    โต๊ะ <span class="text-accent"><?= htmlspecialchars($job['table_number']) ?></span>
                </h1>
                <p class="text-xs text-gray-400 mt-1"><i class="fa-solid fa-users text-gray-500 mr-1"></i> สำหรับ <?= $job['guests'] ?> ท่าน</p>
            </div>

            <!-- ⏳ Real-time Countdown Box -->
            <div class="bg-black/50 border border-gray-800 rounded-2xl p-4 text-center relative space-y-1">
                <span class="text-[10px] text-gray-400 uppercase font-black tracking-wider block">
                    <i class="fa-solid fa-stopwatch text-amber-400 mr-1"></i> เวลาคงเหลือก่อนโต๊ะหลุด
                </span>
                <div id="countdown" class="text-3xl font-black text-accent tracking-wider font-mono">00:00:00</div>
            </div>

            <!-- 🚦 Timeline Status Stepper -->
            <div class="py-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase block mb-3 text-center">ขั้นตอนการดำเนินงาน</span>
                <div class="flex justify-between items-center relative">
                    <!-- Line behind icons -->
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-800 -z-0"></div>
                    
                    <!-- Step 1: Accepted -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold <?= $current_step >= 0 ? 'bg-emerald-500 text-black shadow-lg shadow-emerald-500/30' : 'bg-gray-800 text-gray-500' ?>">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <span class="text-[9px] font-bold mt-1.5 <?= $current_step >= 0 ? 'text-emerald-400' : 'text-gray-500' ?>">รับงานแล้ว</span>
                    </div>

                    <!-- Step 2: On the way -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold <?= $current_step >= 1 ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' : 'bg-gray-800 text-gray-500' ?>">
                            <i class="fa-solid fa-person-walking"></i>
                        </div>
                        <span class="text-[9px] font-bold mt-1.5 <?= $current_step >= 1 ? 'text-blue-400' : 'text-gray-500' ?>">กำลังไปร้าน</span>
                    </div>

                    <!-- Step 3: Arrived -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold <?= $current_step >= 2 ? 'bg-purple-500 text-white shadow-lg shadow-purple-500/30' : 'bg-gray-800 text-gray-500' ?>">
                            <i class="fa-solid fa-chair"></i>
                        </div>
                        <span class="text-[9px] font-bold mt-1.5 <?= $current_step >= 2 ? 'text-purple-400' : 'text-gray-500' ?>">ถึงร้านแล้ว</span>
                    </div>

                    <!-- Step 4: Completed -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold <?= $current_step >= 3 ? 'bg-emerald-400 text-black shadow-lg shadow-emerald-400/30' : 'bg-gray-800 text-gray-500' ?>">
                            <i class="fa-solid fa-flag-checkered"></i>
                        </div>
                        <span class="text-[9px] font-bold mt-1.5 <?= $current_step >= 3 ? 'text-emerald-400' : 'text-gray-500' ?>">ส่งมอบสำเร็จ</span>
                    </div>
                </div>
            </div>

            <!-- Job Information Details -->
            <div class="bg-black/30 border border-borderCard p-4 rounded-2xl text-xs space-y-2 text-gray-300">
                <div class="flex justify-between border-b border-gray-800/80 pb-2">
                    <span class="text-gray-500">ค่าตอบแทนโต๊ะนี้</span>
                    <span class="font-extrabold text-emerald-400 text-sm">฿<?= number_format($job['reward'] ?? 0) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">ที่อยู่ร้าน</span>
                    <span class="font-bold text-white text-right max-w-[200px]"><?= htmlspecialchars($job['restaurant_address'] ?: 'ไม่ระบุ') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">หมายเหตุจากลูกค้า</span>
                    <span class="font-bold text-amber-400"><?= htmlspecialchars($job['note'] ?: 'ไม่มี') ?></span>
                </div>

                <?php if ($job['proof_image']): ?>
                    <div class="pt-2 border-t border-gray-800/80 flex justify-between items-center">
                        <span class="text-gray-400">หลักฐานรูปถ่ายโต๊ะ</span>
                        <a href="uploads/<?= $job['proof_image'] ?>" target="_blank" class="bg-blue-950 text-blue-400 border border-blue-800 px-3 py-1 rounded-lg font-bold text-[10px] hover:bg-blue-900 transition flex items-center gap-1">
                            <i class="fa-solid fa-image"></i> คลิกเพื่อดูรูป
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 🛠️ Actions Area for Receiver Only -->
            <?php if ($_SESSION['role'] === 'receiver' && $_SESSION['user_id'] == $job['receiver_id']): ?>
                <div class="pt-2 border-t border-gray-800/80">
                    
                    <!-- State 1: ACCEPTED -> Update to ON_THE_WAY -->
                    <?php if ($job['status'] === 'ACCEPTED'): ?>
                        <form action="api/update_status.php" method="POST">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <input type="hidden" name="status" value="ON_THE_WAY">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-3.5 rounded-2xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-blue-900/40 transition">
                                <i class="fa-solid fa-person-walking text-base"></i> กดอัปเดต: ออกเดินทางไปร้านแล้ว
                            </button>
                        </form>

                    <!-- State 2: ON_THE_WAY -> Upload Proof & Update to ARRIVED -->
                    <?php elseif ($job['status'] === 'ON_THE_WAY'): ?>
                        <form action="api/update_status.php" method="POST" enctype="multipart/form-data" class="space-y-3">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <input type="hidden" name="status" value="ARRIVED">
                            
                            <div>
                                <label class="block text-[10px] text-gray-400 font-bold mb-1 uppercase">อัปโหลดรูปภาพโต๊ะ/ร้านค้า เพื่อยืนยัน</label>
                                <input type="file" name="proof_image" accept="image/*" required class="w-full text-xs text-gray-400 bg-black/60 p-2.5 rounded-xl border border-gray-700 focus:outline-none">
                            </div>

                            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 text-white font-extrabold py-3.5 rounded-2xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-purple-900/40 transition">
                                <i class="fa-solid fa-camera text-base"></i> อัปโหลดรูป & แจ้ง "ถึงโต๊ะแล้ว"
                            </button>
                        </form>

                    <!-- State 3: ARRIVED -> Update to COMPLETED -->
                    <?php elseif ($job['status'] === 'ARRIVED'): ?>
                        <form action="api/update_status.php" method="POST">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <input type="hidden" name="status" value="COMPLETED">
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3.5 rounded-2xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-900/40 transition">
                                <i class="fa-solid fa-circle-check text-base"></i> ส่งมอบโต๊ะให้ลูกค้าเรียบร้อย (จบงาน)
                            </button>
                        </form>

                    <!-- State 4: COMPLETED -->
                    <?php elseif ($job['status'] === 'COMPLETED'): ?>
                        <div class="bg-emerald-950/40 border border-emerald-500/40 p-3 rounded-xl text-center text-emerald-400 text-xs font-bold">
                            🎉 งานนี้ดำเนินการส่งมอบเสร็จสิ้นแล้ว!
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        const expireTime = <?= $expire_timestamp ?> * 1000;
        
        function updateTimer() {
            const now = new Date().getTime();
            const diff = expireTime - now;

            if (diff <= 0) {
                document.getElementById('countdown').innerHTML = '<span class="text-red-500">EXPIRED (โต๊ะหลุดแล้ว)</span>';
                return;
            }

            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            const formattedMinutes = String(minutes).padStart(2, '0');
            const formattedSeconds = String(seconds).padStart(2, '0');

            document.getElementById('countdown').innerText = `${formattedMinutes}:${formattedSeconds} นาที`;
        }

        setInterval(updateTimer, 1000);
        updateTimer();
    </script>
</body>
</html>