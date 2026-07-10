<?php
/**
 * login.php - หน้าเข้าสู่ระบบสำหรับผู้ที่เคยสมัครแล้ว
 * UDRU E-Sports Club Portal
 */
require_once 'db.php';
session_start();

// ถ้า login แล้ว redirect ไปเกมเลย
if (isset($_SESSION['applicant_id'])) {
    header('Location: game.html');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');

    if (empty($student_id)) {
        $error = 'กรุณากรอกรหัสนักศึกษา';
    } elseif (!preg_match('/^\d{8,15}$/', $student_id)) {
        $error = 'รหัสนักศึกษาต้องเป็นตัวเลข 8-15 หลัก';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM rov_applicants WHERE student_id = ? LIMIT 1");
        $stmt->execute([$student_id]);
        $applicant = $stmt->fetch();

        if ($applicant) {
            $_SESSION['applicant_id']  = $applicant['id'];
            $_SESSION['student_id']    = $applicant['student_id'];
            $_SESSION['fullname']      = $applicant['fullname'];
            $_SESSION['in_game_name']  = $applicant['in_game_name'];
            $_SESSION['primary_role']  = $applicant['primary_role'];
            $_SESSION['game_score']    = $applicant['game_score'];
            header('Location: game.html');
            exit;
        } else {
            $error = 'ไม่พบรหัสนักศึกษานี้ในระบบ กรุณาสมัครใหม่';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="เข้าสู่ระบบ UDRU E-Sports Club Portal ด้วยรหัสนักศึกษา">
<title>เข้าสู่ระบบ | UDRU E-Sports</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<style>
  :root { --cyan: #00fff7; --green: #39ff14; --dark: #050508; --card: #0d0d1a; }
  body {
    font-family: 'Mitr', 'Space Grotesk', sans-serif;
    background: var(--dark); color: #e0e0e0; min-height: 100vh;
    display: flex; flex-direction: column;
    background-image: linear-gradient(rgba(0,255,247,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(0,255,247,0.04) 1px, transparent 1px);
    background-size: 40px 40px;
  }
  .scanline-overlay { position: fixed; top:0;left:0;right:0;bottom:0; background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,0.03) 2px,rgba(0,0,0,0.03) 4px); pointer-events:none; z-index:9999; }
  .glow-cyan { color:var(--cyan); text-shadow:0 0 10px var(--cyan),0 0 30px var(--cyan); }
  .neon-border { border:1px solid rgba(0,255,247,0.25); box-shadow:0 0 25px rgba(0,255,247,0.08),inset 0 0 25px rgba(0,255,247,0.03); }
  nav { background:rgba(5,5,8,0.85); backdrop-filter:blur(12px); border-bottom:1px solid rgba(0,255,247,0.15); }

  .cyber-input {
    background: rgba(0,255,247,0.04);
    border: 1px solid rgba(0,255,247,0.2);
    color: #fff;
    font-family: 'Space Grotesk', monospace;
    font-size: 1.4rem;
    letter-spacing: 0.15em;
    text-align: center;
    transition: all 0.3s ease;
    outline: none;
  }
  .cyber-input:focus {
    border-color: var(--cyan);
    box-shadow: 0 0 20px rgba(0,255,247,0.25), inset 0 0 15px rgba(0,255,247,0.06);
    background: rgba(0,255,247,0.08);
  }
  .cyber-input::placeholder { color: rgba(255,255,255,0.15); letter-spacing: 0.05em; font-size: 1rem; }

  .btn-login {
    background: linear-gradient(135deg, rgba(0,255,247,0.15), rgba(0,200,200,0.05));
    border: 1px solid var(--cyan);
    color: var(--cyan);
    box-shadow: 0 0 20px rgba(0,255,247,0.3);
    transition: all 0.3s ease;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    letter-spacing: 0.12em;
  }
  .btn-login:hover {
    background: var(--cyan);
    color: #000;
    box-shadow: 0 0 40px rgba(0,255,247,0.7), 0 0 80px rgba(0,255,247,0.3);
    transform: translateY(-2px);
  }
  .btn-reg {
    background: transparent;
    border: 1px solid rgba(57,255,20,0.4);
    color: rgba(57,255,20,0.8);
    transition: all 0.3s ease;
    font-family: 'Mitr', sans-serif;
  }
  .btn-reg:hover { border-color: var(--green); color: var(--green); box-shadow: 0 0 20px rgba(57,255,20,0.3); }

  .error-box { background: rgba(255,77,77,0.1); border: 1px solid rgba(255,77,77,0.4); }

  @keyframes slideIn { from { transform: translateY(40px); opacity:0; } to { transform: translateY(0); opacity:1; } }
  .card-animate { animation: slideIn 0.5s ease forwards; }

  /* Radar ring animation */
  @keyframes radar-ring {
    0% { transform: scale(0.8); opacity: 0.8; }
    100% { transform: scale(1.4); opacity: 0; }
  }
  .radar-ring {
    position: absolute; border-radius: 50%;
    border: 1px solid rgba(0,255,247,0.3);
    animation: radar-ring 2s ease-out infinite;
  }

  /* Glowing orb */
  .glowing-orb {
    background: radial-gradient(circle, rgba(0,255,247,0.15) 0%, transparent 70%);
    position: absolute; border-radius: 50%;
    pointer-events: none;
  }
</style>
</head>
<body>
<div class="scanline-overlay"></div>

<!-- NAVBAR -->
<nav class="sticky top-0 z-50 px-6 py-4">
  <div class="max-w-7xl mx-auto flex items-center justify-between">
    <a href="index.php" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
      <i data-lucide="zap" class="w-5 h-5" style="color:var(--cyan)"></i>
      <span class="font-bold tracking-widest text-sm" style="color:var(--cyan);font-family:'Space Grotesk',sans-serif;">UDRU E-SPORTS</span>
    </a>
    <a href="register.php" class="text-sm text-gray-400 hover:text-green-400 transition-colors flex items-center gap-2">
      <i data-lucide="user-plus" class="w-4 h-4"></i>สมัครใหม่
    </a>
  </div>
</nav>

<!-- MAIN -->
<main class="flex-1 flex items-center justify-center px-4 py-16">
  <div class="max-w-md w-full">

    <!-- Logo / Icon area with radar -->
    <div class="text-center mb-10 card-animate">
      <div class="relative inline-flex items-center justify-center">
        <div class="glowing-orb w-48 h-48" style="left:-50%;top:-50%;"></div>
        <div class="radar-ring w-32 h-32" style="animation-delay:0s;"></div>
        <div class="radar-ring w-32 h-32" style="animation-delay:0.7s;"></div>
        <div class="radar-ring w-32 h-32" style="animation-delay:1.4s;"></div>
        <div class="relative w-24 h-24 rounded-2xl neon-border flex items-center justify-center" style="background:rgba(0,255,247,0.08);">
          <i data-lucide="shield-check" class="w-12 h-12" style="color:var(--cyan)"></i>
        </div>
      </div>
      <h1 class="text-4xl font-bold text-white mt-6 mb-2" style="font-family:'Mitr',sans-serif;">เข้าสู่ระบบ</h1>
      <p class="text-gray-400 text-sm" style="font-family:'Space Grotesk',sans-serif;">UDRU E-SPORTS · PLAYER LOGIN</p>
    </div>

    <!-- Login Card -->
    <div class="neon-border rounded-2xl p-8 card-animate" style="background:var(--card);animation-delay:0.1s;">
      <?php if ($error): ?>
      <div class="error-box rounded-xl px-5 py-4 mb-6 flex items-center gap-3">
        <i data-lucide="alert-octagon" class="w-5 h-5 text-red-400 shrink-0"></i>
        <span class="text-red-300 text-sm" style="font-family:'Mitr',sans-serif;"><?= h($error) ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" id="loginForm">
        <div class="mb-6">
          <label class="block text-xs tracking-widest uppercase mb-3" style="color:rgba(0,255,247,0.7);font-family:'Space Grotesk',sans-serif;">
            <i data-lucide="hash" class="w-3 h-3 inline mr-1"></i>รหัสนักศึกษา
          </label>
          <input
            id="student_id" name="student_id" type="text" maxlength="15" required autofocus
            placeholder="_ _ _ _ _ _ _ _ _ _"
            class="cyber-input w-full rounded-xl px-5 py-5"
            value="<?= isset($_POST['student_id']) ? h($_POST['student_id']) : '' ?>">
          <p class="text-gray-600 text-xs mt-2 text-center" style="font-family:'Mitr',sans-serif;">กรอกรหัสนักศึกษาของคุณเพื่อดึงประวัติ</p>
        </div>

        <button type="submit" id="loginBtn" class="btn-login w-full py-4 rounded-xl text-lg uppercase tracking-widest flex items-center justify-center gap-3">
          <i data-lucide="log-in" class="w-6 h-6"></i>
          <span>เข้าสู่ระบบ</span>
        </button>
      </form>

      <!-- Divider -->
      <div class="flex items-center gap-4 my-6">
        <div class="flex-1 h-px bg-gray-800"></div>
        <span class="text-gray-600 text-xs" style="font-family:'Space Grotesk',sans-serif;">OR</span>
        <div class="flex-1 h-px bg-gray-800"></div>
      </div>

      <!-- Register link -->
      <a href="register.php" class="btn-reg w-full py-3 rounded-xl text-sm text-center block">
        <i data-lucide="user-plus" class="w-4 h-4 inline mr-2"></i>ยังไม่ได้สมัคร? ลงทะเบียนใหม่
      </a>
    </div>

    <!-- Back home -->
    <div class="text-center mt-6">
      <a href="index.php" class="text-gray-600 hover:text-gray-400 text-sm transition-colors flex items-center justify-center gap-2" style="font-family:'Mitr',sans-serif;">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>กลับหน้าหลัก
      </a>
    </div>
  </div>
</main>

<script>
lucide.createIcons();

// Only allow digits
document.getElementById('student_id').addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9]/g, '');
});

document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('loginBtn');
  btn.innerHTML = '<svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> <span>กำลังค้นหา...</span>';
  btn.disabled = true;
});
</script>
</body>
</html>
