<?php
session_start();

// ถ้าเข้าสู่ระบบไว้แล้ว ให้ Redirect ไปยัง Dashboard ตาม Role โดยอัตโนมัติ
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'customer') {
        header('Location: customer_dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'receiver') {
        header('Location: receiver_dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'restaurant') {
        header('Location: restaurant_dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RUBTO - รับโต๊ะแทนคุณ ก่อนโต๊ะจะหลุด</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              accent: '#FF3B30',
              dark: '#121212',
              card: '#1E1E1E',
              cardHover: '#2A2A2A'
            }
          }
        }
      }
    </script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-dark text-white font-sans min-h-screen flex flex-col justify-between">

    <!-- Navigation Bar -->
    <nav class="border-b border-gray-800 bg-black/40 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-2 text-2xl font-black tracking-wider text-accent">
                <i class="fa-solid font-bold fa-chair"></i> RUBTO
            </a>
            <div class="flex items-center gap-3">
                <a href="login.php" class="text-sm font-semibold text-gray-300 hover:text-white px-3 py-2 transition">
                    เข้าสู่ระบบ
                </a>
                <a href="register.php" class="text-sm font-bold bg-accent hover:bg-red-600 px-4 py-2 rounded-xl transition shadow-lg shadow-red-900/20">
                    สมัครสมาชิก
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="max-w-4xl mx-auto px-4 py-16 text-center flex flex-col items-center justify-center">
        <div class="inline-flex items-center gap-2 bg-accent/10 border border-accent/30 text-accent text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
            <i class="fa-solid fa-clock"></i> ระบบรับโต๊ะแทนสำหรับร้านอาหาร & สถานบันเทิง
        </div>
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-6 leading-tight">
            รับโต๊ะแทนคุณ <br><span class="text-accent">ก่อนโต๊ะจะหลุด!</span>
        </h1>
        <p class="text-gray-400 text-base md:text-lg max-w-2xl mb-8 leading-relaxed">
            ติดงาน เลิกงานช้า หรือรถติดแค่ไหนก็ไม่กลัวโต๊ะหลุด โพสต์หาคนไปนั่งเฝ้าโต๊ะไว้ก่อนที่ร้านจะปล่อยให้คิวอื่น ปลอดภัย มั่นใจได้ด้วยระบบยืนยันตัวตน Real-time
        </p>
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <a href="register.php?role=customer" class="bg-accent hover:bg-red-600 text-white font-bold px-8 py-4 rounded-xl text-center shadow-lg transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus-circle"></i> โพสต์รับโต๊ะด่วน
            </a>
            <a href="register.php?role=receiver" class="bg-card hover:bg-cardHover border border-gray-700 text-white font-bold px-8 py-4 rounded-xl text-center transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-person-running"></i> สมัครเป็นผู้รับโต๊ะ (สร้างรายได้)
            </a>
        </div>
    </header>

    <!-- How It Works Section -->
    <section class="max-w-6xl mx-auto px-4 py-12 w-full">
        <h2 class="text-2xl font-bold text-center mb-10 text-gray-200">
            ขั้นตอนการใช้งาน <span class="text-accent">3 ขั้นตอแนง่ายๆ</span>
        </h2>
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Step 1 -->
            <div class="bg-card border border-gray-800 p-6 rounded-2xl relative overflow-hidden">
                <div class="text-4xl font-black text-accent/20 absolute top-4 right-4">01</div>
                <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center text-accent text-xl font-bold mb-4">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">1. โพสต์งานรับโต๊ะ</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    ลูกค้ากรอกรายละเอียดชื่อร้าน หมายเลขโต๊ะ เวลาที่โต๊ะจะหลุด และระบุค่าตอบแทนสำหรับคนไปรับโต๊ะ
                </p>
            </div>

            <!-- Step 2 -->
            <div class="bg-card border border-gray-800 p-6 rounded-2xl relative overflow-hidden">
                <div class="text-4xl font-black text-accent/20 absolute top-4 right-4">02</div>
                <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center text-accent text-xl font-bold mb-4">
                    <i class="fa-solid fa-person-walking-luggage"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">2. ผู้รับงานเดินทางไปร้าน</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    ผู้รับงาน (Runner) กดรับงานแล้วเดินทางไปยังร้านอาหารก่อนเวลาที่กำหนด พร้อมอัปโหลดหลักฐานเมื่อถึงร้าน
                </p>
            </div>

            <!-- Step 3 -->
            <div class="bg-card border border-gray-800 p-6 rounded-2xl relative overflow-hidden">
                <div class="text-4xl font-black text-accent/20 absolute top-4 right-4">03</div>
                <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center text-accent text-xl font-bold mb-4">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">3. ยืนยัน & รับโต๊ะสำเร็จ</h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    ร้านค้าตรวจสอบและยืนยันการมาถึง โต๊ะของคุณจะได้รับการรักษาไว้ปลอดภัย จนกว่าคุณจะเดินทางมาถึง!
                </p>
            </div>
        </div>
    </section>

    <!-- Core Features Highlight -->
    <section class="max-w-6xl mx-auto px-4 py-12 w-full border-t border-gray-800/60">
        <div class="grid md:grid-cols-3 gap-6 text-center md:text-left">
            <div class="flex items-start gap-4 p-4">
                <div class="text-accent text-2xl mt-1"><i class="fa-solid fa-hourglass-half"></i></div>
                <div>
                    <h4 class="font-bold text-white mb-1">Real-time Countdown</h4>
                    <p class="text-gray-400 text-xs">คำนวณเวลาถอยหลังแบบเรียลไทม์ ป้องกันการเสียโต๊ะหลุดคิวโดยอัตโนมัติ</p>
                </div>
            </div>
            <div class="flex items-start gap-4 p-4">
                <div class="text-accent text-2xl mt-1"><i class="fa-solid fa-shield-halved"></i></div>
                <div>
                    <h4 class="font-bold text-white mb-1">ระบบป้องกันการแย่งงาน</h4>
                    <p class="text-gray-400 text-xs">ใช้ Transaction Locking ป้องกันผู้รับงานกดรับงานเดียวกันซ้ำ</p>
                </div>
            </div>
            <div class="flex items-start gap-4 p-4">
                <div class="text-accent text-2xl mt-1"><i class="fa-solid fa-store"></i></div>
                <div>
                    <h4 class="font-bold text-white mb-1">รองรับ 3 บทบาท</h4>
                    <p class="text-gray-400 text-xs">แบ่งแยกหน้าจอและสิทธิ์การใช้งานชัดเจน ระหว่างลูกค้า ผู้รับงาน และพนักงานร้าน</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-800 bg-black/60 py-6 text-center text-xs text-gray-500">
        <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                &copy; <?= date('Y') ?> <span class="text-accent font-bold">RUBTO</span>. All rights reserved.
            </div>
            <div class="flex gap-4">
                <a href="login.php" class="hover:text-gray-300">เข้าสู่ระบบ</a>
                <a href="register.php" class="hover:text-gray-300">สมัครสมาชิก</a>
            </div>
        </div>
    </footer>

</body>
</html>