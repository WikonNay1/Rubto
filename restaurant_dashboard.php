<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header('Location: login.php');
    exit;
}

$completed = $pdo->query("SELECT COUNT(*) FROM pickup_jobs WHERE status = 'COMPLETED'")->fetchColumn();
$expired = $pdo->query("SELECT COUNT(*) FROM pickup_jobs WHERE status = 'EXPIRED'")->fetchColumn();
$active = $pdo->query("SELECT COUNT(*) FROM pickup_jobs WHERE status IN ('OPEN', 'ACCEPTED', 'ARRIVED')")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Dashboard ร้านค้า - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } } } }</script>
</head>
<body class="bg-dark text-white p-4">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">ภาพรวมสถานะโต๊ะจอง</h1>
            <a href="restaurant_tables.php" class="bg-accent text-xs font-bold px-4 py-2 rounded-lg">ไปหน้าจัดการโต๊ะ</a>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-card p-4 rounded-xl border border-gray-800 text-center">
                <span class="text-xs text-gray-400">กำลังรอดำเนินการ</span>
                <div class="text-3xl font-black text-yellow-400 mt-1"><?= $active ?></div>
            </div>
            <div class="bg-card p-4 rounded-xl border border-gray-800 text-center">
                <span class="text-xs text-gray-400">รับโต๊ะสำเร็จ</span>
                <div class="text-3xl font-black text-green-400 mt-1"><?= $completed ?></div>
            </div>
            <div class="bg-card p-4 rounded-xl border border-gray-800 text-center">
                <span class="text-xs text-gray-400">โต๊ะหลุด (Expired)</span>
                <div class="text-3xl font-black text-red-400 mt-1"><?= $expired ?></div>
            </div>
        </div>
    </div>
</body>
</html>