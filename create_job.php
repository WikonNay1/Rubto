<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurant_name = trim($_POST['restaurant_name']);
    $table_number = trim($_POST['table_number']);
    $booking_time = $_POST['booking_time'];
    $expire_time = $_POST['expire_time']; // เวลาที่โต๊ะจะหลุด
    $people = (int)$_POST['people'];
    $reward = (float)$_POST['reward'];
    $notes = trim($_POST['notes']);

    // 1. เพิ่มข้อมูลร้านถ้ายังไม่มีในระบบ
    $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE name = ?");
    $stmt->execute([$restaurant_name]);
    $restaurant = $stmt->fetch();
    
    if (!$restaurant) {
        $stmt = $pdo->prepare("INSERT INTO restaurants (name, address) VALUES (?, 'ระบุในรายละเอียดเพิ่มเติม')");
        $stmt->execute([$restaurant_name]);
        $restaurant_id = $pdo->lastInsertId();
    } else {
        $restaurant_id = $restaurant['id'];
    }

    // 2. สร้างรายการจอง (Booking)
    $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, restaurant_id, table_number, booking_time, expire_time, people, status) VALUES (?, ?, ?, ?, ?, ?, 'FINDING_RECEIVER')");
    $stmt->execute([$_SESSION['user_id'], $restaurant_id, $table_number, $booking_time, $expire_time, $people]);
    $booking_id = $pdo->lastInsertId();

    // 3. สร้าง Job งานรับโต๊ะ
    $stmt = $pdo->prepare("INSERT INTO pickup_jobs (booking_id, customer_id, reward, notes, status) VALUES (?, ?, ?, ?, 'OPEN')");
    $stmt->execute([$booking_id, $_SESSION['user_id'], $reward, $notes]);

    header('Location: customer_dashboard.php?success=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สร้างรายการรับโต๊ะ - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { theme: { extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } } } }
    </script>
</head>
<body class="bg-dark text-white p-4">
    <div class="max-w-xl mx-auto bg-card p-6 rounded-2xl border border-gray-800 my-8">
        <h2 class="text-xl font-bold mb-4 text-accent">โพสต์หาคนรับโต๊ะแทน</h2>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">ชื่อร้านอาหาร / สถานบันเทิง</label>
                <input type="text" name="restaurant_name" placeholder="เช่น ชงเจริญ, TDERM" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">หมายเลขโต๊ะ</label>
                    <input type="text" name="table_number" placeholder="เช่น A12" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">จำนวนคน</label>
                    <input type="number" name="people" value="2" min="1" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">เวลาที่จองไว้</label>
                    <input type="datetime-local" name="booking_time" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">เวลาที่โต๊ะจะหลุด (Deadline)</label>
                    <input type="datetime-local" name="expire_time" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">ค่าตอบแทนสำหรับผู้รับโต๊ะ (บาท)</label>
                <input type="number" name="reward" value="150" min="0" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">รายละเอียดเพิ่มเติม</label>
                <textarea name="notes" placeholder="เช่น ฝากสั่งเครื่องดื่มไว้ก่อนได้เลย" class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white h-24"></textarea>
            </div>
            <button type="submit" class="w-full bg-accent font-bold py-3 rounded-lg">สร้างรายการรับโต๊ะ</button>
        </form>
    </div>
</body>
</html>