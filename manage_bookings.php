<?php
session_start();
require_once 'config/db.php';

// ตรวจสอบสิทธิ์ (แก้ไขจาก 'merchant' เป็น 'restaurant')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลร้านค้าของ user ที่ล็อกอินอยู่
$stmt_res = $pdo->prepare("SELECT * FROM restaurants WHERE user_id = ? LIMIT 1");
$stmt_res->execute([$user_id]);
$restaurant = $stmt_res->fetch();
$restaurant_id = $restaurant['id'] ?? 0;



// ดึงรายการจองทั้งหมดของร้านนี้
$stmt_list = $pdo->prepare("
    SELECT b.*, u.name as customer_name, u.phone as customer_phone
    FROM bookings b
    LEFT JOIN users u ON b.customer_id = u.id
    WHERE b.restaurant_id = ?
    ORDER BY b.id DESC
");
$stmt_list->execute([$restaurant_id]);
$bookings = $stmt_list->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรายการจอง - ร้านค้า</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
      tailwind.config = { theme: { extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } } } }
    </script>
</head>
<body class="bg-dark text-white min-h-screen p-4 md:p-6">

    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Header พร้อมปุ่มย้อนกลับ -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-card p-5 rounded-2xl border border-gray-800 gap-4">
    <div class="flex items-center gap-3">
        <!-- ปุ่มย้อนกลับไปหน้าผังร้านค้า -->
        <a href="restaurant_tables.php" class="bg-gray-800 hover:bg-gray-700 text-gray-300 p-3 rounded-xl border border-gray-700 transition flex items-center justify-center" title="กลับไปหน้าผังร้าน">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-accent"><i class="fa-solid fa-calendar-check mr-2"></i>จัดการรายการจอง</h1>
            <p class="text-xs text-gray-400 mt-0.5">ร้าน: <?= htmlspecialchars($restaurant['name'] ?? 'ไม่พบข้อมูลร้าน') ?></p>
        </div>
    </div>
    <button onclick="openAddModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition shadow-lg">
        <i class="fa-solid fa-plus text-sm"></i> เพิ่มการจองใหม่ (Walk-in / โทรจอง)
    </button>
</div>

        <!-- Notification Alerts -->
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-xs font-bold">
                <i class="fa-solid fa-circle-check mr-2"></i> ดำเนินการสำเร็จเรียบร้อยแล้ว!
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl text-xs font-bold">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> เกิดข้อผิดพลาดในการทำรายการ
            </div>
        <?php endif; ?>

        <!-- Table List -->
        <div class="bg-card rounded-2xl border border-gray-800 overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-300">
                    <thead class="bg-black/50 text-gray-400 uppercase border-b border-gray-800 font-bold">
                        <tr>
                            <th class="p-4">ID</th>
                            <th class="p-4">โต๊ะ</th>
                            <th class="p-4">ชื่อลูกค้า / เบอร์โทร</th>
                            <th class="p-4">จำนวน</th>
                            <th class="p-4">หมายเหตุ</th>
                            <th class="p-4">สถานะ</th>
                            <th class="p-4">เวลาจอง</th>
                            <th class="p-4 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="8" class="p-8 text-center text-gray-500">ยังไม่มีรายการจองในขณะนี้</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr class="hover:bg-white/5 transition">
                                    <td class="p-4 font-mono text-gray-400">#<?= $b['id'] ?></td>
                                    <td class="p-4 font-black text-amber-400 text-sm"><?= htmlspecialchars($b['table_number']) ?></td>
                                    <td class="p-4">
                                        <div class="font-bold text-white"><?= htmlspecialchars($b['customer_name'] ?? 'Walk-in / โทรจอง') ?></div>
                                        <div class="text-[10px] text-gray-400"><?= htmlspecialchars($b['customer_phone'] ?? '-') ?></div>
                                    </td>
                                    <td class="p-4 font-bold"><?= $b['guests'] ?? 1 ?> คน</td>
                                    <td class="p-4 text-gray-400 max-w-xs truncate"><?= htmlspecialchars($b['note'] ?? '-') ?></td>
                                    <td class="p-4">
                                        <?php if ($b['status'] === 'PENDING'): ?>
                                            <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2.5 py-1 rounded-full font-bold">รอยืนยัน</span>
                                        <?php elseif ($b['status'] === 'CONFIRMED'): ?>
                                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2.5 py-1 rounded-full font-bold">ยืนยันแล้ว</span>
                                        <?php elseif ($b['status'] === 'CANCELLED'): ?>
                                            <span class="bg-red-500/20 text-red-400 border border-red-500/30 px-2.5 py-1 rounded-full font-bold">ยกเลิก</span>
                                        <?php else: ?>
                                            <span class="bg-gray-500/20 text-gray-400 border border-gray-500/30 px-2.5 py-1 rounded-full font-bold"><?= $b['status'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-gray-400 text-[11px]"><?= date('d/m/Y H:i', strtotime($b['created_at'])) ?></td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- ปุ่มแก้ไข -->
                                            <button onclick='openEditModal(<?= json_encode($b) ?>)' class="bg-blue-600/20 text-blue-400 hover:bg-blue-600 hover:text-white border border-blue-500/30 p-2 rounded-lg transition" title="แก้ไข">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            
                                            <!-- ปุ่มยกเลิกการจอง -->
                                            <?php if ($b['status'] !== 'CANCELLED'): ?>
                                                <a href="action_booking.php?action=cancel&id=<?= $b['id'] ?>&restaurant_id=<?= $restaurant_id ?>" 
                                                   onclick="return confirm('คุณต้องการยกเลิกการจองโต๊ะ <?= $b['table_number'] ?> ใช่หรือไม่?')" 
                                                   class="bg-red-600/20 text-red-400 hover:bg-red-600 hover:text-white border border-red-500/30 p-2 rounded-lg transition" 
                                                   title="ยกเลิกการจอง">
                                                    <i class="fa-solid fa-ban"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal เพิ่มการจอง (Walk-in / หน้าร้าน) -->
    <div id="addModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50">
        <div class="bg-card border border-gray-800 p-6 rounded-2xl max-w-md w-full text-white space-y-4 shadow-2xl">
            <h3 class="text-lg font-bold text-emerald-400"><i class="fa-solid fa-plus-circle mr-2"></i>เพิ่มการจองใหม่</h3>
            
            <form action="action_booking.php" method="POST" class="space-y-3">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="restaurant_id" value="<?= $restaurant_id ?>">

                <div>
                    <label class="block text-xs text-gray-400 mb-1">หมายเลขโต๊ะ (เช่น EX1, A1, B3)</label>
                    <input type="text" name="table_number" required placeholder="ระบุโต๊ะ..." class="w-full bg-black/50 border border-gray-700 rounded-xl p-2.5 text-xs text-white uppercase focus:border-accent outline-none">
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">ชื่อลูกค้า (ตัวเลือก)</label>
                        <input type="text" name="customer_name" placeholder="ชื่อลูกค้า" class="w-full bg-black/50 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-accent outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">จำนวนคน</label>
                        <input type="number" name="guests" min="1" value="2" required class="w-full bg-black/50 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-accent outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">สถานะ</label>
                    <select name="status" class="w-full bg-black/50 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-accent outline-none">
                        <option value="CONFIRMED">CONFIRMED (ยืนยันแล้ว)</option>
                        <option value="PENDING">PENDING (รอยืนยัน)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">หมายเหตุ</label>
                    <input type="text" name="note" placeholder="ข้อความเพิ่มเติม..." class="w-full bg-black/50 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-accent outline-none">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeModal('addModal')" class="w-1/2 bg-gray-800 py-2.5 rounded-xl text-xs font-bold">ยกเลิก</button>
                    <button type="submit" class="w-1/2 bg-emerald-600 font-bold py-2.5 rounded-xl text-xs hover:bg-emerald-500">บันทึกการจอง</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal แก้ไขการจอง -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50">
        <div class="bg-card border border-gray-800 p-6 rounded-2xl max-w-md w-full text-white space-y-4 shadow-2xl">
            <h3 class="text-lg font-bold text-blue-400"><i class="fa-solid fa-pen-to-square mr-2"></i>แก้ไขการจอง</h3>
            
            <form action="action_booking.php" method="POST" class="space-y-3">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="booking_id" id="edit_booking_id">
                <input type="hidden" name="restaurant_id" value="<?= $restaurant_id ?>">

                <div>
                    <label class="block text-xs text-gray-400 mb-1">หมายเลขโต๊ะ</label>
                    <input type="text" name="table_number" id="edit_table_number" required class="w-full bg-black/50 border border-gray-700 rounded-xl p-2.5 text-xs text-white uppercase focus:border-accent outline-none">
                </div>
                
                <div>
                    <label class="block text-xs text-gray-400 mb-1">จำนวนคน</label>
                    <input type="number" name="guests" id="edit_guests" min="1" required class="w-full bg-black/50 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-accent outline-none">
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">สถานะการจอง</label>
                    <select name="status" id="edit_status" class="w-full bg-black/50 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-accent outline-none">
                        <option value="PENDING">PENDING (รอยืนยัน)</option>
                        <option value="CONFIRMED">CONFIRMED (ยืนยันแล้ว)</option>
                        <option value="CANCELLED">CANCELLED (ยกเลิกการจอง)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">หมายเหตุ</label>
                    <input type="text" name="note" id="edit_note" class="w-full bg-black/50 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-accent outline-none">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeModal('editModal')" class="w-1/2 bg-gray-800 py-2.5 rounded-xl text-xs font-bold">ยกเลิก</button>
                    <button type="submit" class="w-1/2 bg-blue-600 font-bold py-2.5 rounded-xl text-xs hover:bg-blue-500">อัปเดตข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function openEditModal(booking) {
            document.getElementById('edit_booking_id').value = booking.id;
            document.getElementById('edit_table_number').value = booking.table_number;
            document.getElementById('edit_guests').value = booking.guests || 1;
            document.getElementById('edit_status').value = booking.status;
            document.getElementById('edit_note').value = booking.note || '';
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>

</body>
</html>