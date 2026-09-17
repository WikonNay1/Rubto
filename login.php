<?php
session_start();
require_once 'config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        // Redirect ตาม Role ของผู้ใช้งาน
        if ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
            exit;
        } elseif ($user['role'] === 'customer') {
            header('Location: customer_dashboard.php');
            exit;
        } elseif ($user['role'] === 'receiver') {
            header('Location: receiver_dashboard.php');
            exit;
        } elseif ($user['role'] === 'restaurant') {
            header('Location: restaurant_dashboard.php');
            exit;
        } else {
            header('Location: customer_dashboard.php');
            exit;
        }
    } else {
        $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - RUBTO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: { colors: { accent: '#FF3B30', dark: '#121212', card: '#1E1E1E' } }
        }
      }
    </script>
</head>
<body class="bg-dark text-white min-h-screen flex items-center justify-center p-4">
    <div class="bg-card p-8 rounded-2xl w-full max-w-md border border-gray-800 shadow-2xl">
        <h1 class="text-3xl font-bold text-center mb-2 text-accent">RUBTO</h1>
        <p class="text-gray-400 text-center text-sm mb-6">รับโต๊ะแทนคุณ ก่อนโต๊ะจะหลุด</p>
        
        <?php if ($error): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded-lg text-sm mb-4 border border-red-500/30 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">อีเมล</label>
                <input type="email" name="email" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white focus:border-accent outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">รหัสผ่าน</label>
                <input type="password" name="password" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 text-white focus:border-accent outline-none">
            </div>
            <button type="submit" class="w-full bg-accent hover:bg-red-600 font-bold py-3 rounded-lg transition shadow-lg">
                เข้าสู่ระบบ
            </button>
        </form>
        <p class="text-xs text-gray-400 text-center mt-6">
            ยังไม่มีบัญชี? <a href="register.php" class="text-accent underline">สมัครสมาชิก</a>
        </p>
    </div>
</body>
</html>