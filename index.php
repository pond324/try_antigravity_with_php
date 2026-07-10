<?php
/**
 * index.php - UDRU E-Sports Club Portal - หน้าแรก
 * ธีม: Cyberpunk E-Sports | Thai: Mitr | EN: Space Grotesk
 */
require_once 'db.php';
session_start();

// ดึงสถิติจากฐานข้อมูล
$total_applicants = 0;
$top_score = 0;
$top_player = '-';
try {
    $total_applicants = (int)$pdo->query("SELECT COUNT(*) FROM rov_applicants")->fetchColumn();
    $row = $pdo->query("SELECT in_game_name, game_score FROM rov_applicants ORDER BY game_score DESC LIMIT 1")->fetch();
    if ($row) {
        $top_score  = $row['game_score'];
        $top_player = $row['in_game_name'];
    }
} catch (PDOException $e) {}

// ดึง Leaderboard Top 5
$leaderboard = [];
try {
    $stmt = $pdo->query("SELECT in_game_name, primary_role, game_score FROM rov_applicants ORDER BY game_score DESC LIMIT 5");
    $leaderboard = $stmt->fetchAll();
} catch (PDOException $e) {}

$role_colors = [
    'Carry'    => 'text-yellow-400',
    'Fighter'  => 'text-red-400',
    'Mage'     => 'text-purple-400',
    'Assassin' => 'text-pink-400',
    'Support'  => 'text-cyan-400',
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="UDRU E-Sports Club Portal - ระบบรับสมัครและคัดเลือกนักกีฬาอีสปอร์ต มหาวิทยาลัยราชภัฏอุดรธานี ประเภทเกม RoV">
<title>UDRU E-Sports Club | Portal</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<style>
  :root { --cyan: #00fff7; --green: #39ff14; --dark: #050508; --card: #0d0d1a; }
  * { box-sizing: border-box; }
  body { font-family: 'Mitr', 'Space Grotesk', sans-serif; background: var(--dark); color: #e0e0e0; overflow-x: hidden; }

  /* Grid bg */
  .grid-bg {
    background-image:
      linear-gradient(rgba(0,255,247,0.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,255,247,0.04) 1px, transparent 1px);
    background-size: 40px 40px;
  }

  /* Glowing text */
  .glow-cyan { color: var(--cyan); text-shadow: 0 0 10px var(--cyan), 0 0 30px var(--cyan), 0 0 60px rgba(0,255,247,0.4); }
  .glow-green { color: var(--green); text-shadow: 0 0 10px var(--green), 0 0 30px var(--green); }

  /* Neon border */
  .neon-border { border: 1px solid rgba(0,255,247,0.3); box-shadow: 0 0 20px rgba(0,255,247,0.1), inset 0 0 20px rgba(0,255,247,0.03); }
  .neon-border-green { border: 1px solid rgba(57,255,20,0.4); box-shadow: 0 0 20px rgba(57,255,20,0.15); }

  /* Glowing btn cyan */
  .btn-cyan {
    background: linear-gradient(135deg, rgba(0,255,247,0.15), rgba(0,200,200,0.05));
    border: 1px solid var(--cyan);
    color: var(--cyan);
    box-shadow: 0 0 15px rgba(0,255,247,0.3);
    transition: all 0.3s ease;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 600;
    letter-spacing: 0.05em;
  }
  .btn-cyan:hover {
    background: var(--cyan);
    color: #000;
    box-shadow: 0 0 30px rgba(0,255,247,0.7), 0 0 60px rgba(0,255,247,0.4);
    transform: translateY(-2px);
  }

  /* Glowing btn green */
  .btn-green {
    background: linear-gradient(135deg, rgba(57,255,20,0.15), rgba(30,200,10,0.05));
    border: 1px solid var(--green);
    color: var(--green);
    box-shadow: 0 0 15px rgba(57,255,20,0.3);
    transition: all 0.3s ease;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 600;
    letter-spacing: 0.05em;
  }
  .btn-green:hover {
    background: var(--green);
    color: #000;
    box-shadow: 0 0 30px rgba(57,255,20,0.7), 0 0 60px rgba(57,255,20,0.4);
    transform: translateY(-2px);
  }

  /* Stat card */
  .stat-card {
    background: var(--card);
    border: 1px solid rgba(0,255,247,0.2);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
  }
  .stat-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, transparent, var(--cyan), transparent);
  }
  .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 40px rgba(0,255,247,0.2); border-color: var(--cyan); }

  /* Scanline animation */
  .scanline-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.03) 2px, rgba(0,0,0,0.03) 4px);
    pointer-events: none; z-index: 9999;
  }

  /* Animated logo */
  @keyframes pulse-glow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
  }
  .logo-pulse { animation: pulse-glow 3s ease-in-out infinite; }

  /* Floating particles */
  @keyframes float-up {
    0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-10vh) rotate(360deg); opacity: 0; }
  }
  .particle {
    position: fixed; width: 4px; height: 4px; border-radius: 50%;
    background: var(--cyan); pointer-events: none; z-index: 1;
    animation: float-up linear infinite;
  }

  /* Nav */
  nav { background: rgba(5,5,8,0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,255,247,0.15); }

  /* Leaderboard row */
  .lb-row { border-bottom: 1px solid rgba(0,255,247,0.08); transition: background 0.2s; }
  .lb-row:hover { background: rgba(0,255,247,0.04); }

  /* Typewriter */
  @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0; } }
  .cursor { animation: blink 1s step-end infinite; }

  /* Hero gradient */
  .hero-gradient { background: radial-gradient(ellipse at center, rgba(0,255,247,0.08) 0%, transparent 60%); }

  /* corner decoration */
  .corner-tl::before, .corner-tl::after { content: ''; position: absolute; }
  .corner-tl::before { top: 0; left: 0; width: 20px; height: 2px; background: var(--cyan); }
  .corner-tl::after { top: 0; left: 0; width: 2px; height: 20px; background: var(--cyan); }
</style>
</head>
<body class="grid-bg min-h-screen">
<div class="scanline-overlay"></div>

<!-- Floating particles -->
<div id="particles"></div>

<!-- ============ NAVBAR ============ -->
<nav class="sticky top-0 z-50 px-6 py-4">
  <div class="max-w-7xl mx-auto flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center neon-border logo-pulse" style="background:rgba(0,255,247,0.1)">
        <i data-lucide="zap" class="w-5 h-5" style="color:var(--cyan)"></i>
      </div>
      <div>
        <div class="text-sm font-bold tracking-widest" style="font-family:'Space Grotesk',sans-serif;color:var(--cyan)">UDRU</div>
        <div class="text-xs text-gray-400" style="font-family:'Mitr',sans-serif;">E-Sports Club</div>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <?php if (isset($_SESSION['applicant_id'])): ?>
        <span class="text-sm text-cyan-300 flex items-center gap-1">
          <i data-lucide="user-check" class="w-4 h-4"></i>
          <?= h($_SESSION['in_game_name']) ?>
        </span>
        <a href="game.html" class="btn-green px-4 py-2 rounded-lg text-sm flex items-center gap-2">
          <i data-lucide="play" class="w-4 h-4"></i>เล่นเกม
        </a>
        <a href="logout.php" class="text-gray-500 hover:text-red-400 text-sm transition-colors">
          <i data-lucide="log-out" class="w-4 h-4"></i>
        </a>
      <?php else: ?>
        <a href="login.php" class="btn-cyan px-5 py-2 rounded-lg text-sm flex items-center gap-2">
          <i data-lucide="log-in" class="w-4 h-4"></i>เข้าสู่ระบบ
        </a>
        <a href="register.php" class="btn-green px-5 py-2 rounded-lg text-sm flex items-center gap-2">
          <i data-lucide="user-plus" class="w-4 h-4"></i>สมัครคัดตัว
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- ============ HERO ============ -->
<section class="hero-gradient relative min-h-[80vh] flex items-center justify-center text-center px-4 py-20">
  <div class="max-w-4xl mx-auto relative z-10">
    <!-- Badge -->
    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full mb-8 neon-border text-xs tracking-widest uppercase" style="color:var(--green);font-family:'Space Grotesk',sans-serif;">
      <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
      SEASON 2025 · OPEN RECRUITMENT
    </div>

    <!-- Main title -->
    <h1 class="text-5xl md:text-7xl font-bold mb-4 leading-tight">
      <span class="glow-cyan" style="font-family:'Space Grotesk',sans-serif;">UDRU</span><br>
      <span class="text-white" style="font-family:'Mitr',sans-serif;">อีสปอร์ต คลับ</span>
    </h1>
    <div class="text-2xl md:text-3xl font-medium mb-3" style="color:var(--cyan);font-family:'Space Grotesk',sans-serif;">
      E-Sports Club Portal
    </div>
    <p class="text-gray-400 text-lg mb-12 max-w-2xl mx-auto" style="font-family:'Mitr',sans-serif;">
      ระบบรับสมัครและคัดเลือกนักกีฬาอีสปอร์ต · มหาวิทยาลัยราชภัฏอุดรธานี<br>
      <span style="color:var(--cyan);" class="text-sm">ทดสอบปฏิกิริยาตอบสนองด้วย AI Hand Tracking</span>
    </p>

    <!-- CTA Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
      <?php if (isset($_SESSION['applicant_id'])): ?>
        <a href="game.html" id="cta-play" class="btn-green px-10 py-4 rounded-xl text-lg flex items-center gap-3 w-full sm:w-auto justify-center">
          <i data-lucide="gamepad-2" class="w-6 h-6"></i>เข้าสู่การทดสอบ
        </a>
      <?php else: ?>
        <a href="register.php" id="cta-register" class="btn-green px-10 py-4 rounded-xl text-lg flex items-center gap-3 w-full sm:w-auto justify-center">
          <i data-lucide="shield-plus" class="w-6 h-6"></i>สมัครคัดตัวเลย!
        </a>
        <a href="login.php" id="cta-login" class="btn-cyan px-10 py-4 rounded-xl text-lg flex items-center gap-3 w-full sm:w-auto justify-center">
          <i data-lucide="log-in" class="w-6 h-6"></i>เข้าสู่ระบบ
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============ STATS ============ -->
<section class="py-16 px-4">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <p class="text-xs tracking-widest mb-2" style="color:var(--cyan);font-family:'Space Grotesk',sans-serif;">// CLUB ACHIEVEMENTS</p>
      <h2 class="text-3xl font-bold text-white" style="font-family:'Mitr',sans-serif;">สถิติและผลงานชมรม</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <!-- Stat 1 -->
      <div class="stat-card rounded-xl p-6 text-center corner-tl relative">
        <div class="text-5xl font-bold mb-2 glow-cyan" style="font-family:'Space Grotesk',sans-serif;">3+</div>
        <div class="text-yellow-400 text-2xl mb-1">🥇</div>
        <div class="text-gray-300 text-sm" style="font-family:'Mitr',sans-serif;">เหรียญทองกีฬามหาวิทยาลัย</div>
      </div>
      <!-- Stat 2 -->
      <div class="stat-card rounded-xl p-6 text-center relative">
        <div class="text-5xl font-bold mb-2 glow-green" style="font-family:'Space Grotesk',sans-serif;"><?= $total_applicants ?>+</div>
        <div class="text-2xl mb-1">🎮</div>
        <div class="text-gray-300 text-sm" style="font-family:'Mitr',sans-serif;">ผู้สมัครในระบบ</div>
      </div>
      <!-- Stat 3 -->
      <div class="stat-card rounded-xl p-6 text-center relative">
        <div class="text-5xl font-bold mb-2 glow-cyan" style="font-family:'Space Grotesk',sans-serif;">150+</div>
        <div class="text-2xl mb-1">👥</div>
        <div class="text-gray-300 text-sm" style="font-family:'Mitr',sans-serif;">สมาชิกทั้งหมด</div>
      </div>
      <!-- Stat 4 -->
      <div class="stat-card rounded-xl p-6 text-center relative">
        <div class="text-5xl font-bold mb-2 glow-green" style="font-family:'Space Grotesk',sans-serif;"><?= number_format($top_score) ?></div>
        <div class="text-2xl mb-1">⚡</div>
        <div class="text-gray-300 text-sm" style="font-family:'Mitr',sans-serif;">คะแนนสูงสุด (<?= h($top_player) ?>)</div>
      </div>
    </div>
  </div>
</section>

<!-- ============ HOW IT WORKS ============ -->
<section class="py-16 px-4">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <p class="text-xs tracking-widest mb-2" style="color:var(--cyan);font-family:'Space Grotesk',sans-serif;">// HOW IT WORKS</p>
      <h2 class="text-3xl font-bold text-white" style="font-family:'Mitr',sans-serif;">ขั้นตอนการสมัคร</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      <?php $steps = [
        ['icon'=>'clipboard-list','num'=>'01','title'=>'ลงทะเบียน','desc'=>'กรอกข้อมูลรหัสนักศึกษา ชื่อจริง ชื่อในเกม และตำแหน่งที่ถนัด','color'=>'var(--cyan)'],
        ['icon'=>'camera','num'=>'02','title'=>'ทดสอบรีเฟล็กซ์','desc'=>'ใช้ฝ่ามือควบคุมผ่าน AI Hand Tracking จับโลโก้ RoV ให้ได้มากที่สุดใน 30 วินาที','color'=>'var(--green)'],
        ['icon'=>'trophy','num'=>'03','title'=>'บันทึกคะแนน','desc'=>'ระบบบันทึก High Score ลงฐานข้อมูลอัตโนมัติ พร้อม Leaderboard อัปเดตทันที','color'=>'#ff9f43'],
      ]; foreach($steps as $s): ?>
      <div class="neon-border rounded-xl p-8 text-center relative" style="background:var(--card);">
        <div class="text-6xl font-bold opacity-10 absolute top-4 right-6" style="font-family:'Space Grotesk',sans-serif;color:<?= $s['color'] ?>"><?= $s['num'] ?></div>
        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background:rgba(0,255,247,0.08);border:1px solid <?= $s['color'] ?>40;">
          <i data-lucide="<?= $s['icon'] ?>" class="w-8 h-8" style="color:<?= $s['color'] ?>"></i>
        </div>
        <h3 class="text-xl font-semibold mb-3" style="color:<?= $s['color'] ?>;font-family:'Mitr',sans-serif;"><?= $s['title'] ?></h3>
        <p class="text-gray-400 text-sm leading-relaxed" style="font-family:'Mitr',sans-serif;"><?= $s['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ LEADERBOARD ============ -->
<?php if (!empty($leaderboard)): ?>
<section class="py-16 px-4">
  <div class="max-w-4xl mx-auto">
    <div class="text-center mb-10">
      <p class="text-xs tracking-widest mb-2" style="color:var(--cyan);font-family:'Space Grotesk',sans-serif;">// LIVE LEADERBOARD</p>
      <h2 class="text-3xl font-bold text-white" style="font-family:'Mitr',sans-serif;">อันดับนักกีฬา</h2>
    </div>
    <div class="neon-border rounded-2xl overflow-hidden" style="background:var(--card);">
      <div class="grid grid-cols-12 px-6 py-3 text-xs tracking-widest text-gray-500 border-b border-gray-800" style="font-family:'Space Grotesk',sans-serif;">
        <div class="col-span-1">#</div>
        <div class="col-span-5">PLAYER</div>
        <div class="col-span-3">ROLE</div>
        <div class="col-span-3 text-right">SCORE</div>
      </div>
      <?php foreach($leaderboard as $i => $row):
        $medal = ['🥇','🥈','🥉','',''][$i] ?? '';
        $rcls  = $role_colors[$row['primary_role']] ?? 'text-gray-400';
      ?>
      <div class="lb-row grid grid-cols-12 px-6 py-4 items-center">
        <div class="col-span-1 text-lg"><?= $medal ?: ($i+1) ?></div>
        <div class="col-span-5 font-semibold text-white" style="font-family:'Space Grotesk',sans-serif;"><?= h($row['in_game_name']) ?></div>
        <div class="col-span-3 text-sm <?= $rcls ?>" style="font-family:'Mitr',sans-serif;"><?= h($row['primary_role']) ?></div>
        <div class="col-span-3 text-right font-bold glow-cyan text-lg" style="font-family:'Space Grotesk',sans-serif;"><?= number_format($row['game_score']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ FOOTER ============ -->
<footer class="border-t border-gray-800 py-10 px-4 text-center">
  <div class="max-w-4xl mx-auto">
    <div class="glow-cyan text-2xl font-bold mb-2" style="font-family:'Space Grotesk',sans-serif;">UDRU E-SPORTS</div>
    <p class="text-gray-500 text-sm" style="font-family:'Mitr',sans-serif;">มหาวิทยาลัยราชภัฏอุดรธานี · ชมรมอีสปอร์ต · Season 2025</p>
    <p class="text-gray-700 text-xs mt-4" style="font-family:'Space Grotesk',sans-serif;">Powered by MediaPipe AI · PHP · MySQL · Tailwind CSS</p>
  </div>
</footer>

<script>
// Init Lucide icons
lucide.createIcons();

// Generate floating particles
const container = document.getElementById('particles');
for (let i = 0; i < 12; i++) {
  const p = document.createElement('div');
  p.className = 'particle';
  p.style.cssText = `
    left: ${Math.random()*100}%;
    width: ${2+Math.random()*4}px;
    height: ${2+Math.random()*4}px;
    animation-duration: ${8+Math.random()*12}s;
    animation-delay: ${Math.random()*8}s;
    opacity: ${0.2+Math.random()*0.6};
    background: ${Math.random()>0.5?'#00fff7':'#39ff14'};
    box-shadow: 0 0 6px currentColor;
  `;
  container.appendChild(p);
}
</script>
</body>
</html>
