<?php
session_start();
require_once 'config/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>โปรไฟล์ส่วนตัว - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } } } }</script>
</head>
<body class="bg-dark text-white p-4">
    <div class="max-w-md mx-auto bg-card border border-gray-800 p-6 rounded-2xl space-y-4">
        <h1 class="text-xl font-bold text-accent">ข้อมูลส่วนตัว</h1>
        <div class="text-sm space-y-2">
            <div><span class="text-gray-400 text-xs">ชื่อ:</span> <?= htmlspecialchars($user['name']) ?></div>
            <div><span class="text-gray-400 text-xs">อีเมล:</span> <?= htmlspecialchars($user['email']) ?></div>
            <div><span class="text-gray-400 text-xs">เบอร์โทรศัพท์:</span> <?= htmlspecialchars($user['phone']) ?></div>
            <div><span class="text-gray-400 text-xs">บทบาท:</span> <strong class="uppercase text-accent"><?= htmlspecialchars($user['role']) ?></strong></div>
        </div>
        <a href="logout.php" class="block w-full text-center bg-red-600 font-bold py-2 rounded-xl text-xs mt-4">ออกจากระบบ</a>
    </div>
</body>
</html>