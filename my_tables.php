<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id =$_SESSION['user_id'];

// 1. ดึงรายการจองปัจจุบัน
$stmt_active =$pdo->prepare("
    SELECT b.*, r.name as restaurant_name, pj.id as job_id, pj.status as job_status
    FROM bookings b
    JOIN restaurants r ON b.restaurant_id = r.id
    LEFT JOIN pickup_jobs pj ON b.id = pj.booking_id
    WHERE b.customer_id = ? 
      AND (b.status NOT IN ('COMPLETED', 'CANCELLED') OR b.status IS NULL)
    ORDER BY b.id DESC
");
$stmt_active->execute([$customer_id]);
$active_bookings =$stmt_active->fetchAll();

// 2. ดึง job_id งานล่าสุดที่มีผู้รับงานแล้ว เพื่อเอาไปใส่ในปุ่มแชทลอยข้างนอก
$stmt_latest_chat =$pdo->prepare("
    SELECT pj.id as job_id 
    FROM pickup_jobs pj
    JOIN bookings b ON pj.booking_id = b.id
    WHERE b.customer_id = ? AND pj.status NOT IN ('OPEN', 'CANCELLED', 'COMPLETED')
    ORDER BY pj.id DESC LIMIT 1
");
$stmt_latest_chat->execute([$customer_id]);$latest_chat = $stmt_latest_chat->fetch();$active_job_id = $latest_chat ? $latest_chat['job_id'] : null;

// 3. ดึงประวัติการจองย้อนหลัง
$stmt_history =$pdo->prepare("
    SELECT b.*, r.name as restaurant_name
    FROM bookings b
    JOIN restaurants r ON b.restaurant_id = r.id
    WHERE b.customer_id = ? AND b.status IN ('COMPLETED', 'CANCELLED')
    ORDER BY b.id DESC LIMIT 10
");
$stmt_history->execute([$customer_id]);
$history_bookings =$stmt_history->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการโต๊ะที่ฉันจองไว้ - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-950 text-white min-h-screen font-sans pb-24">

    <!-- เมนูด้านบน -->
    <div class="bg-gray-900 border-b border-gray-800 p-4 sticky top-0 z-40 mb-6 backdrop-blur-md bg-opacity-80">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-chair text-red-500 text-xl"></i>
                <span class="font-black text-lg text-white">โต๊ะที่ฉันจองไว้</span>
            </div>
            <a href="customer_dashboard.php" class="bg-gray-800 hover:bg-gray-700 text-white text-xs px-4 py-2.5 rounded-xl font-bold flex items-center gap-2 border border-gray-700 transition">
                <i class="fa-solid fa-map"></i> กลับไปผังร้าน
            </a>
        </div>
    </div>

    <div class="max-w-3xl mx-auto p-4 space-y-6">

        <!-- แจ้งเตือนสถานะ -->
        <?php if (isset($_GET['msg']) &&$_GET['msg'] === 'runner_created'): ?>
            <div class="bg-emerald-500/20 border border-emerald-500 text-emerald-400 p-3.5 rounded-2xl text-xs font-bold text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check text-base"></i> ส่งคำขอหาคนรับโต๊ะเรียบร้อยแล้ว! งานของคุณไปอยู่ในตลาดงานแล้ว
            </div>
        <?php elseif (isset($_GET['msg']) &&$_GET['msg'] === 'booked'): ?>
            <div class="bg-emerald-500/20 border border-emerald-500 text-emerald-400 p-3.5 rounded-2xl text-xs font-bold text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check text-base"></i> บันทึกการจองสำเร็จแล้ว! กรุณารอทางร้านกดยืนยันการจอง
            </div>
        <?php elseif (isset($_GET['msg']) &&$_GET['msg'] === 'updated'): ?>
            <div class="bg-emerald-500/20 border border-emerald-500 text-emerald-400 p-3.5 rounded-2xl text-xs font-bold text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-pen-to-square text-base"></i> อัปเดตข้อมูลการจองเรียบร้อยแล้ว
            </div>
        <?php elseif (isset($_GET['msg']) &&$_GET['msg'] === 'cancelled'): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-400 p-3.5 rounded-2xl text-xs font-bold text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-ban text-base"></i> ยกเลิกรายการจองเรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <!-- รายการโต๊ะที่กำลังจองอยู่ปัจจุบัน -->
        <div>
            <h2 class="text-sm font-bold text-gray-400 mb-3 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-clock text-amber-400"></i> รายการที่กำลังใช้งานอยู่ (Active)
            </h2>

            <?php if (count($active_bookings) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($active_bookings as$b): ?>
                        <div class="bg-gradient-to-r from-gray-900 via-gray-900 to-gray-950 border border-gray-800 p-5 rounded-3xl shadow-xl space-y-4 relative overflow-hidden">
                            
                            <!-- Header: สถานะร้าน + ชื่อร้าน + หมายเลขโต๊ะ -->
                            <div class="flex justify-between items-start">
                                <div>
                                    <?php if ($b['status'] === 'CONFIRMED'): ?>
                                        <span class="bg-emerald-500/10 text-emerald-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase border border-emerald-500/20 inline-flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i> ร้านยืนยันแล้ว
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-amber-500/10 text-amber-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase border border-amber-500/20 animate-pulse inline-flex items-center gap-1">
                                            <i class="fa-solid fa-hourglass-half"></i> รอยืนยันจากทางร้าน
                                        </span>
                                    <?php endif; ?>

                                    <h3 class="text-xl font-black text-white mt-2"><?= htmlspecialchars($b['restaurant_name']) ?></h3>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-500 block font-bold uppercase tracking-wider">หมายเลขโต๊ะ</span>
                                    <span class="text-3xl font-black text-red-500"><?= htmlspecialchars($b['table_number']) ?></span>
                                </div>
                            </div>

                            <!-- ข้อมูลรายละเอียดโต๊ะ -->
                            <div class="grid grid-cols-2 gap-2 text-xs bg-black/40 p-3 rounded-2xl border border-gray-800/80 text-gray-300">
                                <p><strong>จำนวนที่นั่ง:</strong> <?= $b['guests'] ?> ท่าน</p>
                                <p><strong>เวลาที่จอง:</strong> <?= date('H:i น.', strtotime($b['created_at'])) ?></p>
                                <p class="col-span-2"><strong>หมายเหตุ:</strong> <?= htmlspecialchars($b['note'] ?: 'ไม่มี') ?></p>
                            </div>

                            <!-- โซนหาคนรับโต๊ะ / ติดตามสถานะงาน (ต้องได้รับการยืนยันจากร้านค้าก่อนเท่านั้นถึงจะแสดง) -->
                            <?php if ($b['status'] === 'CONFIRMED'): ?>
                                <?php if (!empty($b['job_id'])): ?>
                                    <?php if ($b['job_status'] === 'COMPLETED'): ?>
                                        <div class="bg-emerald-950/40 border border-emerald-500/50 p-3.5 rounded-2xl text-center shadow-lg space-y-1">
                                            <span class="text-xs text-emerald-400 font-black flex items-center justify-center gap-2">
                                                <i class="fa-solid fa-circle-check text-sm"></i> ส่งมอบโต๊ะสำเร็จ / จบงานเรียบร้อยแล้ว!
                                            </span>
                                            <p class="text-[10px] text-gray-400">ขอบคุณที่ใช้บริการส่งคนรับโต๊ะ RUBTO</p>
                                        </div>

                                    <?php elseif ($b['job_status'] !== 'OPEN'): ?>
                                        <div class="bg-blue-950/30 border border-blue-800/60 p-3 rounded-2xl text-center space-y-2">
                                            <span class="text-xs text-blue-400 font-bold flex items-center justify-center gap-2">
                                                <i class="fa-solid fa-user-check"></i> มีคนรับงานโต๊ะนี้แล้ว
                                            </span>
                                            <div class="flex gap-2">
                                                <a href="job_tracking.php?id=<?= $b['job_id'] ?>" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded-xl text-[11px] flex items-center justify-center gap-1.5 transition">
                                                    <i class="fa-solid fa-route"></i> ติดตามสถานะ
                                                </a>
                                                <a href="chat.php?job_id=<?= $b['job_id'] ?>" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 rounded-xl text-[11px] flex items-center justify-center gap-1.5 transition">
                                                    <i class="fa-solid fa-comments"></i> เปิดห้องแชท
                                                </a>
                                            </div>
                                        </div>

                                    <?php else: ?>
                                        <button type="button" onclick="openRunnerModal(<?= $b['id'] ?>, '<?= htmlspecialchars($b['restaurant_name']) ?>', '<?= htmlspecialchars($b['table_number']) ?>')" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3 rounded-2xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-950 transition">
                                            <i class="fa-solid fa-coins text-base"></i> แก้ไขค่าตอบแทนการรับโต๊ะ
                                        </button>
                                        <p class="text-[10px] text-emerald-400 text-center mt-1">กำลังประกาศหาคนรับโต๊ะอยู่...</p>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <button type="button" onclick="openRunnerModal(<?= $b['id'] ?>, '<?= htmlspecialchars($b['restaurant_name']) ?>', '<?= htmlspecialchars($b['table_number']) ?>')" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3 rounded-2xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-950 transition">
                                        <i class="fa-solid fa-coins text-base"></i> หาคนรับโต๊ะ (ระบุค่าตอบแทน)
                                    </button>
                                <?php endif; ?>

                            <!-- กรณีสถานะยังเป็น PENDING (รอยืนยัน) จะแสดงกล่องล็อกและไม่แสดงปุ่มหาคนรับโต๊ะ -->
                            <?php else: ?>
                                <div class="bg-black/40 border border-gray-800/80 p-3 rounded-2xl text-center">
                                    <span class="text-xs text-gray-500 font-medium flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-lock text-amber-500"></i> ต้องรอให้ทางร้านกดยืนยันการจองก่อน
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- ปุ่มจัดการ (แก้ไข / ยกเลิก) -->
                            <div class="flex gap-2 pt-2 border-t border-gray-800/60">
                                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($b)) ?>)" class="flex-1 bg-gray-800/60 hover:bg-gray-800 text-gray-300 hover:text-amber-400 border border-gray-700/50 py-2.5 rounded-xl text-[11px] font-bold transition flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-pen-to-square"></i> แก้ไขการจอง
                                </button>
                                
                                <form action="action_manage_booking.php" method="POST" class="flex-1" onsubmit="return confirm('คุณต้องการยกเลิกการจองโต๊ะนี้ใช่หรือไม่?')">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="w-full bg-red-950/30 hover:bg-red-900/50 text-red-400 border border-red-900/40 py-2.5 rounded-xl text-[11px] font-bold transition flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-ban"></i> ยกเลิกการจอง
                                    </button>
                                </form>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-gray-900 border border-gray-800 p-8 rounded-3xl text-center space-y-3">
                    <i class="fa-solid fa-chair text-gray-600 text-4xl"></i>
                    <p class="text-gray-400 text-sm">คุณยังไม่มีรายการจองโต๊ะในขณะนี้</p>
                    <a href="customer_dashboard.php" class="inline-block bg-red-600 hover:bg-red-500 text-white text-xs px-4 py-2.5 rounded-xl font-bold transition">กดจองโต๊ะเลย</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ประวัติการจองย้อนหลัง -->
        <div class="pt-4">
            <h2 class="text-sm font-bold text-gray-400 mb-3 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-history text-gray-400"></i> ประวัติการจองย้อนหลัง
            </h2>
            <div class="bg-gray-900 border border-gray-800 rounded-3xl p-4 space-y-2">
                <?php if (count($history_bookings) > 0): ?>
                    <?php foreach ($history_bookings as$h): ?>
                        <div class="flex justify-between items-center bg-gray-950 p-3 rounded-2xl border border-gray-800/80 text-xs">
                            <div>
                                <p class="font-bold text-white"><?= htmlspecialchars($h['restaurant_name']) ?> - โต๊ะ <span class="text-red-400"><?= htmlspecialchars($h['table_number']) ?></span></p>
                                <p class="text-[10px] text-gray-500"><?= $h['created_at'] ?> (<?=$h['guests'] ?> ท่าน)</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold <?= $h['status'] === 'COMPLETED' ? 'bg-emerald-950/60 text-emerald-400 border border-emerald-800/60' : 'bg-gray-800 text-gray-400' ?>">
                                <?= $h['status'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4 text-xs">ไม่มีประวัติย้อนหลัง</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- 💬 ปุ่มแชทด่วนลอย (Floating Action Button) ด้านนอก -->
    <?php if ($active_job_id): ?>
        <a href="chat.php?job_id=<?= $active_job_id ?>" class="fixed bottom-6 right-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white p-4 rounded-full shadow-2xl shadow-blue-500/50 flex items-center gap-3 z-50 border border-blue-400/30 group transition-all duration-300 hover:scale-105">
            <div class="relative">
                <i class="fa-solid fa-comments text-xl"></i>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping"></span>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
            </div>
            <span class="font-black text-xs pr-1 hidden sm:inline">แชทกับคนรับโต๊ะ</span>
        </a>
    <?php endif; ?>

    <!-- Modal ระบุราคาการไปรับโต๊ะ -->
    <div id="runnerModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-gray-900 border border-gray-700 p-6 rounded-3xl max-w-sm w-full text-white space-y-4 shadow-2xl">
            <div class="flex items-center gap-2 text-emerald-400">
                <i class="fa-solid fa-hand-holding-dollar text-xl"></i>
                <h3 class="text-lg font-black">กำหนดค่าตอบแทนการรับโต๊ะ</h3>
            </div>
            <p id="runnerModalSub" class="text-xs text-gray-400"></p>

            <form action="action_find_runner.php" method="POST" class="space-y-4">
                <input type="hidden" name="booking_id" id="runnerBookingId">

                <div>
                    <label class="block text-xs text-gray-300 font-bold mb-1">ระบุค่าตอบแทนสำหรับคนรับโต๊ะ (บาท)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-gray-400 font-bold">฿</span>
                        <input type="number" name="reward" min="20" step="10" value="300" required class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-8 pr-3 py-2 text-lg text-emerald-400 font-black focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('runnerModal').classList.add('hidden')" class="w-1/2 bg-gray-800 py-2.5 rounded-xl text-xs font-bold text-gray-300 hover:bg-gray-700 transition">ยกเลิก</button>
                    <button type="submit" class="w-1/2 bg-emerald-600 hover:bg-emerald-500 text-white py-2.5 rounded-xl text-xs font-black shadow-lg shadow-emerald-950 transition">ยืนยันโพสต์หาคนรับ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal แก้ไขข้อมูลการจอง -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-gray-900 border border-gray-700 p-6 rounded-3xl max-w-sm w-full text-white space-y-4 shadow-2xl">
            <h3 class="text-lg font-black text-amber-400">แก้ไขข้อมูลการจอง</h3>
            
            <form action="action_manage_booking.php" method="POST" class="space-y-3">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="booking_id" id="editBookingId">

                <div>
                    <label class="block text-xs text-gray-400 font-bold mb-1">หมายเลขโต๊ะที่ต้องการเปลี่ยน</label>
                    <input type="text" name="table_number" id="editTableNumber" required class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                </div>

                <div>
                    <label class="block text-xs text-gray-400 font-bold mb-1">จำนวนลูกค้า (ท่าน)</label>
                    <input type="number" name="guests" id="editGuests" min="1" max="15" required class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                </div>

                <div>
                    <label class="block text-xs text-gray-400 font-bold mb-1">หมายเหตุเพิ่มเติม</label>
                    <input type="text" name="note" id="editNote" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="w-1/2 bg-gray-800 py-2.5 rounded-xl text-xs font-bold text-gray-300 hover:bg-gray-700 transition">ยกเลิก</button>
                    <button type="submit" class="w-1/2 bg-amber-500 hover:bg-amber-400 text-black py-2.5 rounded-xl text-xs font-black shadow-lg transition">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRunnerModal(bookingId, restaurantName, tableNumber) {
            document.getElementById('runnerBookingId').value = bookingId;
            document.getElementById('runnerModalSub').innerText = `ร้าน ${restaurantName} (โต๊ะ ${tableNumber})`;
            document.getElementById('runnerModal').classList.remove('hidden');
        }

        function openEditModal(booking) {
            document.getElementById('editBookingId').value = booking.id;
            document.getElementById('editTableNumber').value = booking.table_number;
            document.getElementById('editGuests').value = booking.guests;
            document.getElementById('editNote').value = booking.note || '';
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</body>
</html>