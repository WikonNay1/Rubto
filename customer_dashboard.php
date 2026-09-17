<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. ดึงรายชื่อร้านค้าทั้งหมด
$stmt_res = $pdo->query("SELECT * FROM restaurants");
$restaurants = $stmt_res->fetchAll();

// 2. รับค่าร้านที่เลือก (ถ้าไม่เลือก ให้ default เป็นร้านแรก)
$selected_restaurant_id = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : ($restaurants[0]['id'] ?? 0);

// 3. ดึงข้อมูลร้านค้าที่เลือก
$stmt_curr = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt_curr->execute([$selected_restaurant_id]);
$current_restaurant = $stmt_curr->fetch();
$layout_type = $current_restaurant['layout_type'] ?? 'dreadlocks';

// 4. ดึงสถานะงาน/โต๊ะที่มีการจองอยู่แล้วของร้านนี้
$stmt_jobs = $pdo->prepare("
    SELECT pj.*, b.table_number 
    FROM pickup_jobs pj 
    JOIN bookings b ON pj.booking_id = b.id 
    WHERE b.restaurant_id = ? AND pj.status NOT IN ('COMPLETED', 'CANCELLED', 'EXPIRED')
");
$stmt_jobs->execute([$selected_restaurant_id]);
$active_jobs = [];
while ($row = $stmt_jobs->fetch()) {
    $active_jobs[$row['table_number']] = $row;
}

// ฟังก์ชันคืนค่า class ของปุ่มโต๊ะ
function getCustomerBtnClass($table_no, $active_jobs, $default_bg = 'bg-amber-400 text-black') {
    if (isset($active_jobs[$table_no])) {
        // โต๊ะไม่ว่าง (มีคนจอง/กำลังดำเนินการ)
        return 'bg-gray-400 text-gray-700 cursor-not-allowed opacity-60';
    }
    // โต๊ะว่าง สามารถกดจองได้
    return $default_bg . ' hover:scale-110 cursor-pointer shadow-lg transition-transform';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกจองโต๊ะ - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
      tailwind.config = { theme: { extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } } } }
    </script>
</head>
<body class="bg-dark text-white min-h-screen p-4 font-sans">

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Top Bar -->
        <div class="flex justify-between items-center bg-card p-4 rounded-2xl border border-gray-800">
            <div>
                <h1 class="text-xl font-bold text-accent">RUBTO จองโต๊ะ</h1>
                <p class="text-xs text-gray-400">ยินดีต้อนรับคุณ <?= htmlspecialchars($_SESSION['name'] ?? 'ลูกค้า') ?></p>
            </div>
            <a href="logout.php" class="text-xs bg-red-500/20 text-red-400 px-3 py-2 rounded-xl border border-red-500/30 font-bold">ออกจากระบบ</a>
        </div>

        <!-- ตัวเลือกร้านค้า -->
        <div class="bg-card p-4 rounded-2xl border border-gray-800">
            <label class="block text-xs text-gray-400 mb-2">เลือกร้านค้าที่ต้องการจอง:</label>
            <div class="flex gap-2 overflow-x-auto pb-1">
                <?php foreach ($restaurants as $r): ?>
                    <a href="?restaurant_id=<?= $r['id'] ?>" 
                       class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $r['id'] == $selected_restaurant_id ? 'bg-accent text-white' : 'bg-black/50 text-gray-400 border border-gray-700 hover:text-white' ?>">
                        <i class="fa-solid fa-store mr-1"></i> <?= htmlspecialchars($r['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- คำอธิบายสัญลักษณ์สถานะ -->
        <div class="flex justify-center gap-4 text-xs bg-card p-3 rounded-xl border border-gray-800">
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-600 inline-block"></span> โซน A / ว่าง</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span> โซน EX / ว่าง</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-sky-500 inline-block"></span> โซน C / ว่าง</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gray-400 inline-block"></span> ไม่ว่าง / มีคนจองแล้ว</div>
        </div>

        <!-- ================= CONTAINER ผังร้านค้า (อิงตามระบบหลังบ้าน) ================= -->
        <div class="bg-white text-black p-4 md:p-6 rounded-3xl border-4 border-black relative shadow-2xl">

            <!-- Header ผังร้าน -->
            <div class="flex justify-between items-start border-b-4 border-black pb-3 mb-4">
                <div>
                    <h2 class="text-2xl font-black text-black"><?= strtoupper(htmlspecialchars($current_restaurant['name'] ?? 'RUBTO FLOOR PLAN')) ?></h2>
                    <p class="text-xs font-bold text-red-600">คลิกที่หมายเลขโต๊ะเพื่อดำเนินการจอง</p>
                </div>
            </div>

            <?php if ($layout_type === 'dreadlocks'): ?>
                <!-- ================= LAYOUT 1: DREADLOCKS BAR ================= -->
                <div class="grid grid-cols-12 gap-3 bg-gray-50 p-4 rounded-2xl border-2 border-gray-300 relative">

                    <!-- ZONE WALK-IN -->
                    <div class="col-span-5 bg-emerald-500 text-white font-black rounded-xl p-4 text-center border-2 border-dashed border-emerald-700 flex flex-col justify-center items-center shadow-md">
                        <span class="text-sm">ZONE</span>
                        <span class="text-lg">WALK-IN</span>
                    </div>

                    <!-- STOCK & ห้องน้ำ -->
                    <div class="col-span-7 grid grid-cols-2 gap-2">
                        <div class="bg-red-800 text-white font-black text-center flex items-center justify-center rounded-xl p-2 text-sm tracking-wider shadow">STOCK</div>
                        <div class="bg-gray-700 text-white font-bold flex flex-col items-center justify-center rounded-xl p-2 text-xs shadow">
                            <i class="fa-solid fa-arrow-right text-lg mb-1"></i>
                            <span class="text-[10px] leading-tight text-center">ห้องน้ำ ชั้น 2</span>
                        </div>
                    </div>

                    <!-- โซนกลาง: BAR E + DJ + โซน EX & A + โซน C + BAR หลัก -->
                    <div class="col-span-12 grid grid-cols-12 gap-2 my-2 items-start">
                        
                        <!-- BAR E (ย้ายขยับตำแหน่งแนวเดียวกับ EX1/A1) -->
                        <div class="col-span-2 flex flex-col items-center justify-between h-full py-2">
                            <div class="bg-blue-800 text-white font-black text-center flex items-center justify-center rounded-xl py-16 px-2 [writing-mode:vertical-lr] rotate-180 text-xs tracking-widest shadow mt-12">
                                Stage
                            </div>
                        </div>

                        <!-- โซนตรงกลาง: DJ + EX + A -->
                        <div class="col-span-6 space-y-3">
                            <div class="bg-red-800 text-white font-black text-center py-1.5 rounded-lg text-xs tracking-widest w-3/4 mx-auto shadow">DJ</div>

                            <!-- EX -->
                            <div class="grid grid-cols-3 gap-1.5 text-center">
                                <?php foreach (['EX1', 'EX2', 'EX3', 'EX4', 'EX5'] as $t): ?>
                                    <button type="button" onclick="selectBooking('<?= $t ?>')" <?= isset($active_jobs[$t]) ? 'disabled' : '' ?> class="w-8 h-8 md:w-9 md:h-9 rounded-full font-black text-[11px] mx-auto flex items-center justify-center border-2 border-amber-600 <?= getCustomerBtnClass($t, $active_jobs, 'bg-amber-400 text-black') ?>">
                                        <?= $t ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <!-- A -->
                            <div class="grid grid-cols-3 gap-1.5 text-center pt-1">
                                <?php foreach (['A1','A2','A3','A7','A8','A9','A10','A11','A12','A13','A14','A15'] as $t): ?>
                                    <button type="button" onclick="selectBooking('<?= $t ?>')" <?= isset($active_jobs[$t]) ? 'disabled' : '' ?> class="w-8 h-8 md:w-9 md:h-9 rounded-full font-black text-[11px] mx-auto flex items-center justify-center border-2 border-red-800 <?= getCustomerBtnClass($t, $active_jobs, 'bg-red-600 text-white') ?>">
                                        <?= $t ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- โซนขวามือ: C + BAR -->
                        <div class="col-span-4 grid grid-cols-2 gap-1.5 h-full items-center">
                            <div class="flex flex-col gap-1.5 items-center justify-between h-full py-1">
                                <?php foreach (['C1','C2','C4','C5','C6','C7','C8','C9','C10'] as $t): ?>
                                    <button type="button" onclick="selectBooking('<?= $t ?>')" <?= isset($active_jobs[$t]) ? 'disabled' : '' ?> class="w-7 h-7 md:w-8 md:h-8 rounded-full font-black text-[10px] flex items-center justify-center border border-sky-700 <?= getCustomerBtnClass($t, $active_jobs, 'bg-sky-500 text-white') ?>">
                                        <?= $t ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div class="h-full bg-red-800 text-white font-black text-center flex items-center justify-center rounded-xl [writing-mode:vertical-lr] text-xs tracking-widest shadow">
                                BAR
                            </div>
                        </div>

                    </div>

                    <!-- โซน B ด้านล่าง -->
                
                    <!-- ทางเข้า - ทางออก -->
                    <div class="col-span-12 text-center pt-3 mt-1">
                        <div class="inline-flex items-center gap-2 text-red-600 font-black text-xs md:text-sm border-2 border-red-600 px-6 py-1.5 rounded-full shadow-sm">
                            <i class="fa-solid fa-up-down"></i> ทางเข้า - ทางออก
                        </div>
                    </div>

                </div>

            <?php else: ?>
                <!-- ================= LAYOUT 2: RATCH HOUR ================= -->
                <div class="grid grid-cols-12 gap-3 bg-gray-100 p-4 rounded-2xl border-2 border-gray-300">
                    <div class="col-span-12 bg-black text-yellow-400 font-black text-center py-4 rounded-xl border-2 border-yellow-500 shadow tracking-widest">
                        <i class="fa-solid fa-music mr-2"></i> MAIN STAGE / LIVE BAND
                    </div>

                    <div class="col-span-12 bg-amber-100 border border-amber-300 p-2 rounded-xl text-center">
                        <span class="text-xs font-bold text-amber-800 block mb-2">โซน VIP Front Stage</span>
                        <div class="flex justify-center gap-3">
                            <?php foreach (['VIP1', 'VIP2', 'VIP3', 'VIP4'] as $t): ?>
                                <button type="button" onclick="selectBooking('<?= $t ?>')" <?= isset($active_jobs[$t]) ? 'disabled' : '' ?> class="w-11 h-11 rounded-xl font-black text-xs border-2 border-amber-600 <?= getCustomerBtnClass($t, $active_jobs, 'bg-amber-500 text-white') ?>"><?= $t ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-span-8 bg-white p-3 rounded-xl border border-gray-300">
                        <span class="text-xs font-bold text-gray-500 block mb-2">โซนกลางร้าน (Indoor Zone):</span>
                        <div class="grid grid-cols-4 gap-2">
                            <?php for($i=1; $i<=12; $i++): $t = "R$i"; ?>
                                <button type="button" onclick="selectBooking('<?= $t ?>')" <?= isset($active_jobs[$t]) ? 'disabled' : '' ?> class="w-9 h-9 rounded-full font-bold text-xs mx-auto flex items-center justify-center border <?= getCustomerBtnClass($t, $active_jobs, 'bg-red-500 text-white') ?>"><?= $t ?></button>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="col-span-4 bg-zinc-800 text-white p-2 rounded-xl flex flex-col justify-between items-center text-center">
                        <span class="text-[10px] text-gray-400 font-bold">COCKTAIL BAR</span>
                        <div class="space-y-1 my-auto">
                            <?php foreach (['BAR1', 'BAR2', 'BAR3'] as $t): ?>
                                <button type="button" onclick="selectBooking('<?= $t ?>')" <?= isset($active_jobs[$t]) ? 'disabled' : '' ?> class="w-8 h-8 rounded-lg font-bold text-[10px] border border-gray-500 block mx-auto <?= getCustomerBtnClass($t, $active_jobs, 'bg-zinc-700 text-white') ?>"><?= $t ?></button>
                            <?php endforeach; ?>
                        </div>
                        <span class="text-[10px] text-yellow-400">RATCH BAR</span>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </div>

<!-- แถบเมนูด้านบนสำหรับฝั่งลูกค้า (booking_map.php) -->
<div class="bg-gray-900 border-b border-gray-800 p-4 sticky top-0 z-40 mb-6">
    <div class="max-w-4xl mx-auto flex justify-between items-center">
        <!-- โลโก้/ชื่อระบบ -->
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-utensils text-red-500 text-xl"></i>
            <span class="font-black text-lg text-white">RUBTO Customer</span>
        </div>

        <!-- ปุ่มกดไปหน้าดูโต๊ะที่ฉันจองไว้ -->
        <div class="flex items-center gap-2">
            <a href="my_tables.php" class="bg-red-600 hover:bg-red-500 text-white text-xs px-4 py-2.5 rounded-xl font-bold flex items-center gap-2 transition shadow-lg border border-red-500">
                <i class="fa-solid fa-chair text-sm"></i>
                <span>โต๊ะที่ฉันจองไว้</span>
            </a>
            <a href="logout.php" class="bg-gray-800 hover:bg-gray-700 text-red-400 px-3 py-2.5 rounded-xl text-xs font-bold border border-gray-700">
                ออกจากระบบ
            </a>
        </div>
    </div>
</div>

    <!-- Modal ยืนยันจองโต๊ะ -->
    <div id="bookingModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50">
        <div class="bg-card border border-gray-800 p-6 rounded-2xl max-w-sm w-full text-white space-y-4">
            <h3 class="text-xl font-bold text-accent" id="modalTitle">จองโต๊ะ</h3>
            <p class="text-xs text-gray-400">คุณกำลังจะส่งคำขอจองโต๊ะ <span id="targetTable" class="text-white font-bold"></span> ที่ร้าน <span class="text-accent font-bold"><?= htmlspecialchars($current_restaurant['name'] ?? '') ?></span></p>
            
            <form action="create_booking.php" method="POST" class="space-y-3">
                <input type="hidden" name="restaurant_id" value="<?= $selected_restaurant_id ?>">
                <input type="hidden" name="table_number" id="inputTableNumber">
                
                <div>
                    <label class="block text-xs text-gray-400 mb-1">จำนวนคน</label>
                    <input type="number" name="guests" min="1" max="10" value="2" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-2.5 text-white">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">หมายเหตุเพิ่มเติม</label>
                    <input type="text" name="note" placeholder="เช่น ขอใกล้เวที / ยืนยันโต๊ะช่วง 20.00 น." class="w-full bg-black/50 border border-gray-700 rounded-lg p-2.5 text-white">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeModal()" class="w-1/2 bg-gray-800 py-2.5 rounded-xl text-xs font-bold">ยกเลิก</button>
                    <button type="submit" class="w-1/2 bg-accent font-bold py-2.5 rounded-xl text-xs hover:opacity-90">ยืนยันการจอง</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectBooking(tableNo) {
            document.getElementById('inputTableNumber').value = tableNo;
            document.getElementById('targetTable').innerText = tableNo;
            document.getElementById('modalTitle').innerText = `ยืนยันจองโต๊ะ ${tableNo}`;
            document.getElementById('bookingModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }
    </script>
</body>
</html>

<script>
const CURRENT_RESTAURANT_ID = <?= json_encode($selected_restaurant_id) ?>;

function updateCustomerFloorPlan() {
    fetch(`get_table_status.php?restaurant_id=${CURRENT_RESTAURANT_ID}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const occupied = data.occupied_tables;

                document.querySelectorAll('button[onclick*="selectBooking"]').forEach(btn => {
                    const match = btn.getAttribute('onclick').match(/'([^']+)'/);
                    if (!match) return;
                    const tableNo = match[1];

                    if (occupied.includes(tableNo)) {
                        // ล็อกโต๊ะไม่ให้ลูกค้ากดจอง
                        btn.disabled = true;
                        btn.classList.add('bg-gray-400', 'text-gray-700', 'cursor-not-allowed', 'opacity-60');
                        btn.classList.remove('hover:scale-110', 'cursor-pointer', 'shadow-lg');
                    } else {
                        // ปลดล็อกให้จองได้
                        btn.disabled = false;
                        btn.classList.remove('bg-gray-400', 'text-gray-700', 'cursor-not-allowed', 'opacity-60');
                        btn.classList.add('hover:scale-110', 'cursor-pointer', 'shadow-lg');
                    }
                });
            }
        });
}

// สั่งให้ อัปเดตผังฝั่งลูกค้าทุกๆ 3 วินาที
setInterval(updateCustomerFloorPlan, 3000);
</script>