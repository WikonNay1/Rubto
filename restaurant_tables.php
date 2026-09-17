<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลร้านค้าที่ล็อกอินอยู่
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE user_id = ?");
$stmt->execute([$user_id]);
$restaurant = $stmt->fetch();

$restaurant_id = $restaurant['id'] ?? 0;
$layout_type = $restaurant['layout_type'] ?? 'dreadlocks';

// ดึงงานที่ค้างอยู่ของร้านนี้
$stmt = $pdo->prepare("
    SELECT pj.*, b.table_number, u.name as customer_name
    FROM pickup_jobs pj
    JOIN bookings b ON pj.booking_id = b.id
    LEFT JOIN users u ON pj.customer_id = u.id
    WHERE b.restaurant_id = ? AND pj.status NOT IN ('COMPLETED', 'CANCELLED', 'EXPIRED')
");
$stmt->execute([$restaurant_id]);
$active_jobs = [];
while ($row = $stmt->fetch()) {
    $active_jobs[$row['table_number']] = $row;
}

function getBtnClass($table_no, $active_jobs, $default_bg = 'bg-amber-400 text-black') {
    if (isset($active_jobs[$table_no])) {
        $status = $active_jobs[$table_no]['status'];
        if ($status === 'ARRIVED') return 'bg-purple-600 text-white ring-4 ring-purple-400 animate-pulse';
        if ($status === 'ACCEPTED' || $status === 'ON_THE_WAY') return 'bg-blue-600 text-white ring-2 ring-blue-400';
        if ($status === 'OPEN') return 'bg-red-500 text-white animate-bounce';
    }
    return $default_bg;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผังร้าน <?= htmlspecialchars($restaurant['name'] ?? 'ร้านค้า') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white min-h-screen p-4 font-sans">

    <div class="max-w-5xl mx-auto bg-white text-black p-4 md:p-6 rounded-3xl shadow-2xl border-4 border-black">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-4 pb-3 border-b-2 border-gray-300">
            <div>
                <h1 class="text-2xl font-black text-black"><?= strtoupper(htmlspecialchars($restaurant['name'] ?? 'RUBTO FLOOR PLAN')) ?></h1>
                <p class="text-xs text-gray-600">ประเภทผังร้าน: <span class="font-bold text-red-600"><?= $layout_type ?></span></p>
            </div>
            <a href="logout.php" class="bg-red-500 text-white text-xs px-3 py-2 rounded-xl font-bold">ออกจากระบบ</a>
        </div>

        <?php if ($layout_type === 'dreadlocks'): ?>
            
            <!-- แถบเมนูด้านบนผังร้านค้า -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-900 p-4 rounded-2xl border border-gray-800 mb-6 gap-3">
                <div>
                    <h1 class="text-xl font-bold text-white"><i class="fa-solid fa-store text-red-500 mr-2"></i>ผังร้านค้าและสถานะโต๊ะ</h1>
                    <p class="text-xs text-gray-400">ดูผังโต๊ะ Real-time และกดจัดการการจอง</p>
                </div>

                <div class="flex items-center gap-2">
                    <!-- ปุ่มไปหน้าจัดการการจอง -->
                    <a href="manage_bookings.php" class="bg-red-600 hover:bg-red-500 text-white font-bold px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition shadow-lg">
                        <i class="fa-solid fa-calendar-check"></i> จัดการรายการจอง
                    </a>
                </div>
            </div>

            <!-- LAYOUT 1: DREADLOCKS BAR -->
            <div class="max-w-2xl mx-auto bg-white text-black p-4 md:p-6 rounded-3xl border-4 border-black font-sans relative shadow-2xl">

                <div class="flex justify-between items-start border-b-4 border-black pb-3 mb-4">
                    <div>
                        <h2 class="text-3xl font-black tracking-tighter text-black">DREADLOCKS BAR</h2>
                        <p class="text-xs font-bold text-red-600">ผังโต๊ะและสถานะ Real-time</p>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black block leading-none">27</span>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-700">MAY 2026</span>
                    </div>
                </div>

                <div class="grid grid-cols-12 gap-3 bg-gray-50 p-4 rounded-2xl border-2 border-gray-300 relative">

                    <!-- ZONE WALK-IN -->
                    <div class="col-span-5 bg-emerald-500 text-white font-black rounded-xl p-4 text-center border-2 border-dashed border-emerald-700 flex flex-col justify-center items-center shadow-md">
                        <span class="text-sm md:text-base">ZONE</span>
                        <span class="text-lg md:text-xl">WALK-IN</span>
                    </div>

                    <!-- STOCK & ห้องน้ำ -->
                    <div class="col-span-7 grid grid-cols-2 gap-2">
                        <div class="bg-red-800 text-white font-black text-center flex items-center justify-center rounded-xl p-2 text-sm tracking-wider shadow">
                            STOCK
                        </div>
                        <div class="bg-gray-700 text-white font-bold flex flex-col items-center justify-center rounded-xl p-2 text-xs shadow">
                            <i class="fa-solid fa-arrow-right text-lg mb-1"></i>
                            <span class="text-[11px] leading-tight text-center">ห้องน้ำ ชั้น 2</span>
                        </div>
                    </div>

                    <!-- โซนกลาง -->
                    <div class="col-span-12 grid grid-cols-12 gap-2 my-2 items-start">
                        
                        <!-- STAGE -->
                        <div class="col-span-2 bg-blue-800 text-white font-black text-center flex items-center justify-center rounded-xl py-12 [writing-mode:vertical-lr] rotate-180 text-sm tracking-widest shadow">
                            STAGE
                        </div>

                        <!-- DJ + EX + A -->
                        <div class="col-span-6 space-y-3">
                            <div class="bg-red-800 text-white font-black text-center py-1.5 rounded-lg text-xs tracking-widest w-3/4 mx-auto shadow">
                                DJ
                            </div>

                            <!-- โต๊ะ EX -->
                            <div class="grid grid-cols-3 gap-1.5 text-center">
                                <?php foreach (['EX1', 'EX2', 'EX3', 'EX4', 'EX5'] as $t): ?>
                                    <button data-table="<?= $t ?>" data-default-bg="bg-amber-400 text-black" onclick="checkTable('<?= $t ?>')" class="w-8 h-8 md:w-10 md:h-10 rounded-full font-black text-[11px] mx-auto shadow-md flex items-center justify-center border-2 border-amber-600 transition-transform hover:scale-110 <?= getBtnClass($t, $active_jobs, 'bg-amber-400 text-black') ?>">
                                        <?= $t ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <!-- โต๊ะ A -->
                            <div class="grid grid-cols-3 gap-1.5 text-center pt-1">
                                <?php foreach (['A1','A2','A3','A7','A8','A9','A10','A11','A12','A13','A14','A15'] as $t): ?>
                                    <button data-table="<?= $t ?>" data-default-bg="bg-red-600 text-white" onclick="checkTable('<?= $t ?>')" class="w-8 h-8 md:w-10 md:h-10 rounded-full font-black text-[11px] mx-auto shadow-md flex items-center justify-center border-2 border-red-800 transition-transform hover:scale-110 <?= getBtnClass($t, $active_jobs, 'bg-red-600 text-white') ?>">
                                        <?= $t ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- โต๊ะ C + BAR -->
                        <div class="col-span-4 grid grid-cols-2 gap-1.5 h-full items-center">
                            <div class="flex flex-col gap-1.5 items-center justify-between h-full py-1">
                                <?php foreach (['C1','C2','C4','C5','C6','C7','C8','C9','C10'] as $t): ?>
                                    <button data-table="<?= $t ?>" data-default-bg="bg-sky-500 text-white" onclick="checkTable('<?= $t ?>')" class="w-7 h-7 md:w-8 md:h-8 rounded-full font-black text-[10px] shadow flex items-center justify-center border border-sky-700 transition-transform hover:scale-110 <?= getBtnClass($t, $active_jobs, 'bg-sky-500 text-white') ?>">
                                        <?= $t ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div class="h-full bg-red-800 text-white font-black text-center flex items-center justify-center rounded-xl [writing-mode:vertical-lr] text-sm tracking-widest shadow">
                                BAR
                            </div>
                        </div>

                    </div>

                    <!-- ทางเข้า - ทางออก -->
                    <div class="col-span-12 text-center pt-3 mt-1">
                        <div class="inline-flex items-center gap-2 text-red-600 font-black text-sm md:text-base border-2 border-red-600 px-6 py-1.5 rounded-full shadow-sm">
                            <i class="fa-solid fa-up-down text-lg"></i>
                            ทางเข้า - ทางออก
                        </div>
                    </div>

                </div>

            </div>
        <?php else: ?>
            <!-- LAYOUT 2: RATCH HOUR -->
<div class="grid grid-cols-12 gap-3 bg-gray-100 p-4 rounded-2xl border-2 border-gray-300 shadow-inner">
    
    <!-- MAIN STAGE -->
    <div class="col-span-12 bg-black text-yellow-400 font-black text-center py-4 rounded-xl border-2 border-yellow-500 shadow text-lg tracking-widest">
        <i class="fa-solid fa-music mr-2"></i> MAIN STAGE / LIVE BAND
    </div>

    <!-- VIP ZONE -->
    <div class="col-span-12 bg-amber-100 border border-amber-300 p-3 rounded-xl text-center">
        <span class="text-xs font-bold text-amber-800 block mb-2">โซน VIP Front Stage</span>
        <div class="flex justify-center gap-3">
            <?php foreach (['VIP1', 'VIP2', 'VIP3', 'VIP4'] as $t): ?>
                <button data-table="<?= $t ?>" data-default-bg="bg-amber-500 text-white" onclick="checkTable('<?= $t ?>')" class="w-11 h-11 rounded-xl font-black text-xs shadow border-2 border-amber-600 <?= getBtnClass($t, $active_jobs, 'bg-amber-500 text-white') ?>"><?= $t ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- INDOOR ZONE -->
    <div class="col-span-8 bg-white p-3 rounded-xl border border-gray-300 shadow-sm flex flex-col justify-between">
        <span class="text-xs font-bold text-gray-500 block mb-2">โซนกลางร้าน (Indoor Zone):</span>
        <div class="grid grid-cols-4 gap-2">
            <?php for($i=1; $i<=12; $i++): $t = "R$i"; ?>
                <button data-table="<?= $t ?>" data-default-bg="bg-red-500 text-white" onclick="checkTable('<?= $t ?>')" class="w-9 h-9 rounded-full font-bold text-xs mx-auto shadow flex items-center justify-center border <?= getBtnClass($t, $active_jobs, 'bg-red-500 text-white') ?>"><?= $t ?></button>
            <?php endfor; ?>
        </div>
    </div>

    <!-- COCKTAIL BAR ZONE -->
    <div class="col-span-4 bg-zinc-800 text-white p-3 rounded-xl flex flex-col justify-between items-center text-center shadow-sm">
        <span class="text-[10px] text-gray-400 font-bold">COCKTAIL BAR</span>
        <div class="space-y-1 my-auto py-2">
            <?php foreach (['BAR1', 'BAR2', 'BAR3'] as $t): ?>
                <button data-table="<?= $t ?>" data-default-bg="bg-zinc-700 text-white" onclick="checkTable('<?= $t ?>')" class="w-8 h-8 rounded-lg font-bold text-[10px] border border-gray-500 block mx-auto <?= getBtnClass($t, $active_jobs, 'bg-zinc-700 text-white') ?>"><?= $t ?></button>
            <?php endforeach; ?>
        </div>
        <span class="text-[10px] text-yellow-400 font-bold">RATCH BAR</span>
    </div>

    <!-- OUTDOOR ZONE -->
    <div class="col-span-12 bg-emerald-50 border border-emerald-200 p-3 rounded-xl">
        <span class="text-xs font-bold text-emerald-800 block mb-2">โซน Outdoor ด้านนอก:</span>
        <div class="flex justify-around">
            <?php foreach (['OD1', 'OD2', 'OD3', 'OD4', 'OD5'] as $t): ?>
                <button data-table="<?= $t ?>" data-default-bg="bg-emerald-500 text-white" onclick="checkTable('<?= $t ?>')" class="w-9 h-9 rounded-full font-bold text-xs shadow border border-emerald-600 <?= getBtnClass($t, $active_jobs, 'bg-emerald-500 text-white') ?>"><?= $t ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ACTION BUTTON: ปุ่มจัดการรายการจอง -->
    <div class="col-span-12 pt-2">
        <a href="manage_bookings.php" class="w-full bg-red-600 hover:bg-red-500 text-white font-black px-4 py-3 rounded-xl text-sm flex items-center justify-center gap-2 transition shadow-lg active:scale-95">
            <i class="fa-solid fa-calendar-check text-base"></i> จัดการรายการจอง
        </a>
    </div>

</div>
        <?php endif; ?>

    </div>

    <!-- Modal แสดงข้อมูล -->
    <div id="tableModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50">
        <div class="bg-gray-900 border border-gray-700 p-6 rounded-2xl max-w-sm w-full text-white space-y-4">
            <h3 class="text-xl font-bold text-red-500" id="modalTitle">รายละเอียดโต๊ะ</h3>
            <div id="modalBody" class="text-sm space-y-2 text-gray-300"></div>
            <button onclick="document.getElementById('tableModal').classList.add('hidden')" class="w-full bg-gray-800 py-2 rounded-xl text-xs font-bold hover:bg-gray-700">ปิด</button>
        </div>
    </div>

    <!-- JavaScript Real-time & Modal Control -->
    <script>
        const RESTAURANT_ID = <?= json_encode($restaurant_id) ?>;
        let activeJobsData = <?= json_encode($active_jobs) ?>;

        function checkTable(tableNo) {
            const modal = document.getElementById('tableModal');
            const title = document.getElementById('modalTitle');
            const body = document.getElementById('modalBody');
            title.innerText = `โต๊ะ ${tableNo}`;

            if (activeJobsData[tableNo]) {
                const job = activeJobsData[tableNo];
                const custName = job.customer_name ? job.customer_name : 'ลูกค้า Walk-in';
                body.innerHTML = `
                    <p><strong>ผู้จอง:</strong> ${custName}</p>
                    <p><strong>สถานะ:</strong> <span class="text-red-400 font-bold">${job.status}</span></p>
                `;
            } else {
                body.innerHTML = `<p class="text-gray-400 text-center py-2">โต๊ะนี้ว่างอยู่</p>`;
            }
            modal.classList.remove('hidden');
        }

        function fetchRealtimeTables() {
            if (!RESTAURANT_ID) return;
            
            fetch(`get_table_status.php?restaurant_id=${RESTAURANT_ID}&t=${new Date().getTime()}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const occupied = data.occupied_tables || [];
                        
                        document.querySelectorAll('button[data-table]').forEach(btn => {
                            const tableNo = btn.getAttribute('data-table');
                            const defaultBg = btn.getAttribute('data-default-bg');

                            if (occupied.includes(tableNo)) {
                                btn.classList.add('bg-gray-600', 'text-gray-300', 'opacity-60');
                                if (defaultBg) {
                                    defaultBg.split(' ').forEach(cls => btn.classList.remove(cls));
                                }
                            } else {
                                btn.classList.remove('bg-gray-600', 'text-gray-300', 'opacity-60');
                                if (defaultBg) {
                                    defaultBg.split(' ').forEach(cls => btn.classList.add(cls));
                                }
                            }
                        });
                    }
                })
                .catch(err => console.error("Realtime update error:", err));
        }

        // เรียกทำงาน Real-time ทุก 2.5 วินาที
        fetchRealtimeTables();
        setInterval(fetchRealtimeTables, 2500);
    </script>
</body>
</html>