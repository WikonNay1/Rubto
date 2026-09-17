<?php
session_start();
require_once 'config/db.php';

// 1. ตรวจสอบสิทธิ์การเป็น Admin อย่างเข้มงวด
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php?error=unauthorized');
    exit;
}

// 2. จัดการการทำงานผ่าน POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // เพิ่มร้านค้าใหม่โดย Admin (บันทึกเฉพาะชื่อร้าน)
    if ($_POST['action'] === 'add_restaurant') {
        $name = trim($_POST['name']);
        $status = 'approved'; // แอดมินเพิ่มเอง อนุมัติทันที
        
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO restaurants (name, status) VALUES (?, ?)");
            $stmt->execute([$name, $status]);
            header('Location: admin_dashboard.php?msg=restaurant_added');
            exit;
        }
    }

    // ลบร้านค้าออกจากฐานข้อมูลถาวร
    if ($_POST['action'] === 'delete_restaurant') {
        $restaurant_id = $_POST['restaurant_id'];
        
        $stmt = $pdo->prepare("DELETE FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurant_id]);
        header('Location: admin_dashboard.php?msg=restaurant_deleted');
        exit;
    }
    
    // จัดการลบผู้ใช้ทั่วไปหรือผู้รับงาน
    if ($_POST['action'] === 'delete_user') {
        $user_id = $_POST['user_id'];
        if ($user_id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
        }
        header('Location: admin_dashboard.php?msg=user_deleted');
        exit;
    }
}

// 3. ดึงข้อมูลสถิติทั่วไป
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_receivers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'receiver'")->fetchColumn();
$total_restaurants = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
$pending_restaurants = $pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'pending'")->fetchColumn();

// 4. ดึงข้อมูลตารางต่างๆ
$restaurants = $pdo->query("SELECT * FROM restaurants ORDER BY FIELD(status, 'pending', 'approved'), id DESC")->fetchAll();
$customers = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY id DESC")->fetchAll();
$receivers = $pdo->query("SELECT * FROM users WHERE role = 'receiver' ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-950 text-white min-h-screen font-sans pb-24">

    <!-- เมนูด้านบนแอดมิน -->
    <div class="bg-gray-900 border-b border-gray-800 p-4 sticky top-0 z-40 mb-6 backdrop-blur-md bg-opacity-80">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-red-500 text-xl"></i>
                <span class="font-black text-lg text-white">RUBTO Admin Control Panel</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 hidden sm:inline">ผู้ดูแลระบบ (<?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>)</span>
                <a href="logout.php" class="bg-red-950/40 hover:bg-red-900/60 text-red-400 border border-red-800/50 text-xs px-3 py-2 rounded-xl font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto p-4 space-y-6">

        <!-- แจ้งเตือนข้อความสถานะ -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="bg-emerald-500/20 border border-emerald-500 text-emerald-400 p-3.5 rounded-2xl text-xs font-bold text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check text-base"></i> 
                <?php 
                    if($_GET['msg'] === 'restaurant_added') echo 'เพิ่มร้านค้าใหม่เข้าระบบสำเร็จแล้ว';
                    elseif($_GET['msg'] === 'restaurant_deleted') echo 'ลบร้านค้าออกจากระบบเรียบร้อยแล้ว';
                    elseif($_GET['msg'] === 'user_deleted') echo 'ลบผู้ใช้งานออกจากระบบเรียบร้อยแล้ว';
                ?>
            </div>
        <?php endif; ?>

        <!-- กล่องสถิติภาพรวม -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-gray-900 border border-gray-800 p-5 rounded-3xl flex items-center gap-4 shadow-lg">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-xl">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase">ร้านค้าทั้งหมด</p>
                    <h3 class="text-2xl font-black text-white"><?= number_format($total_restaurants) ?> ร้าน</h3>
                </div>
            </div>

            <div class="bg-gray-900 border border-gray-800 p-5 rounded-3xl flex items-center gap-4 shadow-lg">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase">รออนุมัติเปิดร้าน</p>
                    <h3 class="text-2xl font-black text-amber-400"><?= number_format($pending_restaurants) ?> ร้าน</h3>
                </div>
            </div>

            <div class="bg-gray-900 border border-gray-800 p-5 rounded-3xl flex items-center gap-4 shadow-lg">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
                    <i class="fa-solid fa-motorcycle"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase">ผู้รับงาน (Receiver)</p>
                    <h3 class="text-2xl font-black text-white"><?= number_format($total_receivers) ?> คน</h3>
                </div>
            </div>

            <div class="bg-gray-900 border border-gray-800 p-5 rounded-3xl flex items-center gap-4 shadow-lg">
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 text-xl">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase">ลูกค้าทั้งหมด</p>
                    <h3 class="text-2xl font-black text-white"><?= number_format($total_customers) ?> คน</h3>
                </div>
            </div>
        </div>

        <!-- 🏪 ส่วนจัดการร้านค้า (Restaurants Management) -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-5 space-y-4 shadow-xl">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-800 pb-3 gap-3">
                <h2 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-store text-red-500"></i> จัดการร้านค้าในระบบ
                </h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400">ทั้งหมด <?= count($restaurants) ?> ร้าน</span>
                    <button onclick="openAddModal()" class="bg-red-600 hover:bg-red-500 text-white text-xs font-bold px-4 py-2 rounded-xl transition flex items-center gap-1.5 shadow-lg">
                        <i class="fa-solid fa-plus"></i> เพิ่มร้านค้าใหม่
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-300">
                    <thead class="bg-gray-950 text-gray-400 uppercase tracking-wider border-b border-gray-800">
                        <tr>
                            <th class="p-3">ชื่อร้านค้า</th>
                            <th class="p-3 text-center">สถานะ</th>
                            <th class="p-3 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        <?php if (count($restaurants) > 0): ?>
                            <?php foreach ($restaurants as $r): ?>
                                <tr class="hover:bg-gray-800/40 transition">
                                    <td class="p-3 font-bold text-white flex items-center gap-2">
                                        <i class="fa-solid fa-utensils text-gray-500"></i>
                                        <?= htmlspecialchars($r['name']) ?>
                                    </td>
                                    <td class="p-3 text-center">
                                        <?php if (($r['status'] ?? 'approved') === 'approved'): ?>
                                            <span class="bg-emerald-500/10 text-emerald-400 px-2.5 py-1 rounded-full text-[10px] font-bold border border-emerald-500/20">อนุมัติแล้ว</span>
                                        <?php else: ?>
                                            <span class="bg-amber-500/10 text-amber-400 px-2.5 py-1 rounded-full text-[10px] font-bold border border-amber-500/20 animate-pulse">รอตรวจสอบ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-right">
                                        <form action="" method="POST" class="inline-flex gap-1" onsubmit="return confirm('คุณต้องการลบร้านค้านี้ออกจากระบบถาวรใช่หรือไม่?');">
                                            <input type="hidden" name="action" value="delete_restaurant">
                                            <input type="hidden" name="restaurant_id" value="<?= $r['id'] ?>">
                                            
                                            <button type="submit" class="bg-red-950/60 hover:bg-red-900 text-red-400 border border-red-800 px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1">
                                                <i class="fa-solid fa-trash"></i> ลบร้านค้า
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-6 text-gray-500">ยังไม่มีร้านค้าในระบบ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 🛵 ส่วนจัดการผู้รับงาน (Receiver Management) -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-5 space-y-4 shadow-xl">
            <div class="flex justify-between items-center border-b border-gray-800 pb-3">
                <h2 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-motorcycle text-emerald-500"></i> รายชื่อผู้รับงาน (Receiver)
                </h2>
                <span class="text-xs text-gray-400">ทั้งหมด <?= count($receivers) ?> คน</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-300">
                    <thead class="bg-gray-950 text-gray-400 uppercase tracking-wider border-b border-gray-800">
                        <tr>
                            <th class="p-3">ชื่อ - นามสกุล</th>
                            <th class="p-3">อีเมล / เบอร์โทร</th>
                            <th class="p-3">วันที่สมัคร</th>
                            <th class="p-3 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        <?php if (count($receivers) > 0): ?>
                            <?php foreach ($receivers as $rc): ?>
                                <tr class="hover:bg-gray-800/40 transition">
                                    <td class="p-3 font-bold text-white flex items-center gap-2">
                                        <i class="fa-solid fa-user-shield text-gray-500"></i>
                                        <?= htmlspecialchars($rc['name']) ?>
                                    </td>
                                    <td class="p-3 text-gray-400">
                                        <?= htmlspecialchars($rc['email']) ?><br>
                                        <span class="text-[10px] text-gray-500"><?= htmlspecialchars($rc['phone']) ?></span>
                                    </td>
                                    <td class="p-3 text-gray-400"><?= htmlspecialchars($rc['created_at']) ?></td>
                                    <td class="p-3 text-right">
                                        <form action="" method="POST" onsubmit="return confirm('คุณต้องการลบบัญชีผู้รับงานนี้ใช่หรือไม่?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $rc['id'] ?>">
                                            <button type="submit" class="bg-red-950/60 hover:bg-red-900 text-red-400 border border-red-800 px-3 py-1.5 rounded-xl font-bold transition">
                                                <i class="fa-solid fa-trash"></i> ลบ
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-6 text-gray-500">ยังไม่มีผู้รับงานในระบบ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 👤 ส่วนจัดการลูกค้า (Customer Management) -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-5 space-y-4 shadow-xl">
            <div class="flex justify-between items-center border-b border-gray-800 pb-3">
                <h2 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-users text-blue-500"></i> รายชื่อลูกค้าทั้งหมด
                </h2>
                <span class="text-xs text-gray-400">ทั้งหมด <?= count($customers) ?> คน</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-300">
                    <thead class="bg-gray-950 text-gray-400 uppercase tracking-wider border-b border-gray-800">
                        <tr>
                            <th class="p-3">ชื่อ - นามสกุล</th>
                            <th class="p-3">อีเมล / เบอร์โทร</th>
                            <th class="p-3">วันที่สมัครสมาชิก</th>
                            <th class="p-3 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        <?php if (count($customers) > 0): ?>
                            <?php foreach ($customers as $c): ?>
                                <tr class="hover:bg-gray-800/40 transition">
                                    <td class="p-3 font-bold text-white flex items-center gap-2">
                                        <i class="fa-solid fa-user-circle text-gray-500 text-base"></i>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </td>
                                    <td class="p-3 text-gray-400">
                                        <?= htmlspecialchars($c['email']) ?><br>
                                        <span class="text-[10px] text-gray-500"><?= htmlspecialchars($c['phone']) ?></span>
                                    </td>
                                    <td class="p-3 text-gray-400"><?= htmlspecialchars($c['created_at']) ?></td>
                                    <td class="p-3 text-right">
                                        <form action="" method="POST" onsubmit="return confirm('คุณต้องการลบบัญชีลูกค้านี้ใช่หรือไม่?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="bg-red-950/60 hover:bg-red-900 text-red-400 border border-red-800 px-3 py-1.5 rounded-xl font-bold transition">
                                                <i class="fa-solid fa-trash"></i> ลบ
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-6 text-gray-500">ยังไม่มีข้อมูลลูกค้า</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal ฟอร์มเพิ่มร้านค้าใหม่ -->
    <div id="addRestaurantModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 w-full max-w-md rounded-3xl p-6 space-y-5 shadow-2xl">
            <div class="flex justify-between items-center border-b border-gray-800 pb-3">
                <h3 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-store text-red-500"></i> เพิ่มร้านค้าใหม่เข้าระบบ
                </h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-white text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_restaurant">
                
                <div>
                    <label class="block text-xs text-gray-400 mb-1 font-bold">ชื่อร้านค้า</label>
                    <input type="text" name="name" required placeholder="เช่น ชาบูนางin, ส้มตำยายศรี" class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-xs text-white focus:border-red-500 outline-none">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeAddModal()" class="w-1/2 bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold py-3 rounded-xl text-xs transition">
                        ยกเลิก
                    </button>
                    <button type="submit" class="w-1/2 bg-red-600 hover:bg-red-500 text-white font-bold py-3 rounded-xl text-xs transition shadow-lg">
                        บันทึกร้านค้า
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- สคริปต์ควบคุมเปิดปิด Modal -->
    <script>
        function openAddModal() {
            document.getElementById('addRestaurantModal').classList.remove('hidden');
        }
        function closeAddModal() {
            document.getElementById('addRestaurantModal').classList.add('hidden');
        }
    </script>

</body>
</html>