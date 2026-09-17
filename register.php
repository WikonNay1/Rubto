<?php
session_start();
require_once 'config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $role = $_POST['role']; // customer, receiver, restaurant
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        // ใช้ Transaction เพื่อความถูกต้องของข้อมูลทั้งสองตาราง
        $pdo->beginTransaction();

        // 1. เพิ่มข้อมูลผู้ใช้งานลงตาราง users
        $stmt = $pdo->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $password, $role]);
        $user_id = $pdo->lastInsertId();

        // 2. ถ้าสมัครเป็นประเภท "ร้านค้า" ให้สร้างแถวข้อมูลในตาราง restaurants ทันที
        if ($role === 'restaurant') {
            $restaurant_name = 'ร้าน ' . $name; // หรือปรับตามต้องการ
            $stmt_res = $pdo->prepare("INSERT INTO restaurants (user_id, name, address) VALUES (?, ?, 'ระบุที่อยู่ร้านค้า')");
            $stmt_res->execute([$user_id, $restaurant_name]);
        }

        $pdo->commit();
        header('Location: login.php?registered=1');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'อีเมลนี้ถูกใช้งานแล้ว หรือข้อมูลไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { theme: { extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } } } }
    </script>
</head>
<body class="bg-dark text-white min-h-screen flex items-center justify-center p-4">
    <div class="bg-card p-8 rounded-2xl w-full max-w-md border border-gray-800 shadow-2xl">
        <h2 class="text-2xl font-bold mb-6 text-center text-accent">สมัครสมาชิก RUBTO</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-400 p-3 rounded-lg text-sm mb-4 text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">ชื่อ-นามสกุล / ชื่อร้านค้า</label>
                <input type="text" name="name" required placeholder="เช่น สมชาย หรือ ชงเจริญ GROOVE" class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white focus:outline-none focus:border-accent">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">เบอร์โทรศัพท์</label>
                <input type="tel" name="phone" required placeholder="08X-XXX-XXXX" class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white focus:outline-none focus:border-accent">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">อีเมล</label>
                <input type="email" name="email" required placeholder="example@email.com" class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white focus:outline-none focus:border-accent">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">รหัสผ่าน</label>
                <input type="password" name="password" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white focus:outline-none focus:border-accent">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">ประเภทผู้ใช้งาน</label>
                <select name="role" class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white focus:outline-none focus:border-accent">
                    <option value="customer">ลูกค้า (ผู้จองโต๊ะ)</option>
                    <option value="receiver">ผู้รับโต๊ะ (Runner)</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-accent font-bold py-3 rounded-lg mt-4 hover:opacity-90 transition">สมัครสมาชิก</button>
        </form>

        <div class="mt-4 text-center">
            <a href="login.php" class="text-xs text-gray-400 hover:text-white">มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</a>
        </div>
    </div>
</body>
</html>