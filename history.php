<?php
session_start();
require_once 'config/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? '';

$stmt = $pdo->prepare("
    SELECT pj.*, r.name as restaurant_name, b.table_number 
    FROM pickup_jobs pj
    JOIN bookings b ON pj.booking_id = b.id
    JOIN restaurants r ON b.restaurant_id = r.id
    WHERE pj.customer_id = ? OR pj.receiver_id = ?
    ORDER BY pj.id DESC
");
$stmt->execute([$user_id, $user_id]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติรายการ - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } } } }</script>
</head>
<body class="bg-dark text-white p-4">
    <div class="max-w-2xl mx-auto space-y-4">
        <h1 class="text-2xl font-bold mb-4">ประวัติการย้อนหลัง</h1>
        <?php foreach ($history as $item): ?>
            <div class="bg-card border border-gray-800 p-4 rounded-xl flex justify-between items-center text-sm">
                <div>
                    <h3 class="font-bold"><?= htmlspecialchars($item['restaurant_name']) ?></h3>
                    <span class="text-xs text-gray-400">โต๊ะ <?= $item['table_number'] ?></span>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded bg-gray-800 text-gray-300"><?= $item['status'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>