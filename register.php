<?php
/**
 * register.php - หน้าลงทะเบียนผู้สมัครคัดตัว
 * UDRU E-Sports Club Portal
 */
require_once 'db.php';
session_start();

$error   = '';
$success = '';
$prefill = ['student_id'=>'','fullname'=>'','in_game_name'=>'','primary_role'=>''];

$roles = ['Carry', 'Fighter', 'Mage', 'Assassin', 'Support'];
$role_info = [
  'Carry'    => ['icon'=>'zap',          'desc'=>'ดีลเลอร์หลัก โจมตีสูง', 'color'=>'#ffcc00'],
  'Fighter'  => ['icon'=>'shield',       'desc'=>'สายบุกตั้งรับ ทนทาน',   'color'=>'#ff6b6b'],
  'Mage'     => ['icon'=>'flame',        'desc'=>'นักมนตร์ สกิลสูง',       'color'=>'#c084fc'],
  'Assassin' => ['icon'=>'crosshair',    'desc'=>'ลอบสังหาร เร็ว แม่น',   'color'=>'#f472b6'],
  'Support'  => ['icon'=>'heart-pulse',  'desc'=>'ซัพพอร์ต ช่วยทีม',     'color'=>'#00fff7'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id   = trim($_POST['student_id']   ?? '');
    $fullname     = trim($_POST['fullname']     ?? '');
    $in_game_name = trim($_POST['in_game_name'] ?? '');
    $primary_role = trim($_POST['primary_role'] ?? '');

    // Validation
    if (empty($student_id) || empty($fullname) || empty($in_game_name) || empty($primary_role)) {
        $error = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
    } elseif (!preg_match('/^\d{8,15}$/', $student_id)) {
        $error = 'รหัสนักศึกษาต้องเป็นตัวเลข 8-15 หลัก';
    } elseif (!in_array($primary_role, $roles)) {
        $error = 'กรุณาเลือกตำแหน่งที่ถูกต้อง';
    } else {
        // ตรวจสอบว่ามีรหัสนักศึกษานี้ในระบบแล้วหรือไม่
        $stmt = $pdo->prepare("SELECT * FROM rov_applicants WHERE student_id = ? LIMIT 1");
        $stmt->execute([$student_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            // มีอยู่แล้ว -> ดึงประวัติมาใช้ต่อ
            $_SESSION['applicant_id']  = $existing['id'];
            $_SESSION['student_id']    = $existing['student_id'];
            $_SESSION['fullname']      = $existing['fullname'];
            $_SESSION['in_game_name']  = $existing['in_game_name'];
            $_SESSION['primary_role']  = $existing['primary_role'];
            $_SESSION['game_score']    = $existing['game_score'];
            header('Location: game.html?returning=1');
            exit;
        } else {
            // ไม่มี -> INSERT แถวใหม่
            $ins = $pdo->prepare("INSERT INTO rov_applicants (student_id, fullname, in_game_name, primary_role, game_score) VALUES (?, ?, ?, ?, 0)");
            $ins->execute([$student_id, $fullname, $in_game_name, $primary_role]);
            $new_id = $pdo->lastInsertId('rov_applicants_id_seq');

            $_SESSION['applicant_id']  = $new_id;
            $_SESSION['student_id']    = $student_id;
            $_SESSION['fullname']      = $fullname;
            $_SESSION['in_game_name']  = $in_game_name;
            $_SESSION['primary_role']  = $primary_role;
            $_SESSION['game_score']    = 0;
            header('Location: game.html?new=1');
            exit;
        }
    }
    // Prefill on error
    $prefill = compact('student_id','fullname','in_game_name','primary_role');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="สมัครคัดตัวนักกีฬาอีสปอร์ต UDRU E-Sports Club - กรอกข้อมูลและทดสอบรีเฟล็กซ์">
<title>สมัครคัดตัว | UDRU E-Sports</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<style>
  :root { --cyan: #00fff7; --green: #39ff14; --dark: #050508; --card: #0d0d1a; }
  body { font-family: 'Mitr', 'Space Grotesk', sans-serif; background: var(--dark); color: #e0e0e0; min-height: 100vh;
    background-image: linear-gradient(rgba(0,255,247,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(0,255,247,0.04) 1px, transparent 1px);
    background-size: 40px 40px; }
  .scanline-overlay { position: fixed; top:0; left:0; right:0; bottom:0; background: repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,0.03) 2px,rgba(0,0,0,0.03) 4px); pointer-events:none; z-index:9999; }
  .glow-cyan { color:var(--cyan); text-shadow:0 0 10px var(--cyan),0 0 30px var(--cyan); }
  .neon-border { border:1px solid rgba(0,255,247,0.25); box-shadow:0 0 25px rgba(0,255,247,0.08),inset 0 0 25px rgba(0,255,247,0.03); }
  nav { background:rgba(5,5,8,0.85); backdrop-filter:blur(12px); border-bottom:1px solid rgba(0,255,247,0.15); }

  /* Form input */
  .cyber-input {
    background: rgba(0,255,247,0.03);
    border: 1px solid rgba(0,255,247,0.2);
    color: #fff;
    font-family: 'Mitr', sans-serif;
    transition: all 0.3s ease;
    outline: none;
  }
  .cyber-input:focus {
    border-color: var(--cyan);
    box-shadow: 0 0 15px rgba(0,255,247,0.2), inset 0 0 10px rgba(0,255,247,0.05);
    background: rgba(0,255,247,0.06);
  }
  .cyber-input::placeholder { color: rgba(255,255,255,0.2); }

  /* Role cards */
  .role-card {
    background: rgba(0,255,247,0.03);
    border: 1px solid rgba(0,255,247,0.15);
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
  }
  .role-card:hover { transform: translateY(-2px); }
  .role-card input[type=radio] { display: none; }
  .role-card.selected { border-color: var(--cyan); background: rgba(0,255,247,0.1); box-shadow: 0 0 15px rgba(0,255,247,0.2); }

  /* Submit btn */
  .btn-submit {
    background: linear-gradient(135deg, rgba(57,255,20,0.2), rgba(0,200,50,0.05));
    border: 1px solid var(--green);
    color: var(--green);
    box-shadow: 0 0 20px rgba(57,255,20,0.3);
    transition: all 0.3s ease;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 600;
    letter-spacing: 0.08em;
  }
  .btn-submit:hover {
    background: var(--green);
    color: #000;
    box-shadow: 0 0 40px rgba(57,255,20,0.7);
    transform: translateY(-2px);
  }

  .label-text { color: rgba(0,255,247,0.7); font-size: 0.78rem; letter-spacing: 0.1em; text-transform: uppercase; font-family: 'Space Grotesk', sans-serif; }
  .error-box { background: rgba(255,77,77,0.1); border: 1px solid rgba(255,77,77,0.4); }

  @keyframes slideIn { from { transform: translateY(30px); opacity:0; } to { transform: translateY(0); opacity:1; } }
  .form-card { animation: slideIn 0.5s ease forwards; }
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
    <a href="login.php" class="text-sm text-gray-400 hover:text-cyan-300 transition-colors flex items-center gap-2">
      <i data-lucide="log-in" class="w-4 h-4"></i>มีบัญชีแล้ว? เข้าสู่ระบบ
    </a>
  </div>
</nav>

<!-- MAIN -->
<main class="px-4 py-16">
  <div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-10">
      <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-6 neon-border" style="background:rgba(0,255,247,0.08);">
        <i data-lucide="shield-plus" class="w-10 h-10" style="color:var(--cyan)"></i>
      </div>
      <h1 class="text-4xl font-bold text-white mb-2" style="font-family:'Mitr',sans-serif;">สมัครคัดตัว</h1>
      <p class="text-gray-400 text-sm" style="font-family:'Space Grotesk',sans-serif;">UDRU E-SPORTS · RoV DIVISION · SEASON 2025</p>
    </div>

    <!-- Form Card -->
    <div class="neon-border rounded-2xl p-8 form-card" style="background:var(--card);">
      <?php if ($error): ?>
      <div class="error-box rounded-xl px-5 py-4 mb-6 flex items-center gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400 shrink-0"></i>
        <span class="text-red-300 text-sm" style="font-family:'Mitr',sans-serif;"><?= h($error) ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" id="regForm">
        <!-- Student ID -->
        <div class="mb-6">
          <label class="label-text block mb-2" for="student_id">
            <i data-lucide="hash" class="w-3 h-3 inline mr-1"></i>รหัสนักศึกษา
          </label>
          <input id="student_id" name="student_id" type="text" maxlength="15" required
            value="<?= h($prefill['student_id']) ?>"
            placeholder="เช่น 6440101001"
            class="cyber-input w-full rounded-xl px-5 py-3 text-lg">
        </div>

        <!-- Full Name -->
        <div class="mb-6">
          <label class="label-text block mb-2" for="fullname">
            <i data-lucide="user" class="w-3 h-3 inline mr-1"></i>ชื่อ-นามสกุลจริง
          </label>
          <input id="fullname" name="fullname" type="text" maxlength="100" required
            value="<?= h($prefill['fullname']) ?>"
            placeholder="เช่น ธนภัทร วงศ์สุวรรณ"
            class="cyber-input w-full rounded-xl px-5 py-3 text-lg">
        </div>

        <!-- IGN -->
        <div class="mb-6">
          <label class="label-text block mb-2" for="in_game_name">
            <i data-lucide="gamepad-2" class="w-3 h-3 inline mr-1"></i>ชื่อในเกม (In-Game Name)
          </label>
          <input id="in_game_name" name="in_game_name" type="text" maxlength="100" required
            value="<?= h($prefill['in_game_name']) ?>"
            placeholder="เช่น ThunderBolt"
            class="cyber-input w-full rounded-xl px-5 py-3 text-lg"
            style="font-family:'Space Grotesk',sans-serif;">
        </div>

        <!-- Primary Role -->
        <div class="mb-8">
          <label class="label-text block mb-3">
            <i data-lucide="swords" class="w-3 h-3 inline mr-1"></i>ตำแหน่งที่ถนัด (Primary Role)
          </label>
          <input type="hidden" name="primary_role" id="primary_role" value="<?= h($prefill['primary_role']) ?>">
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <?php foreach($roles as $role):
              $info = $role_info[$role];
              $sel  = ($prefill['primary_role'] === $role) ? 'selected' : '';
            ?>
            <label class="role-card rounded-xl p-4 text-center <?= $sel ?>" onclick="selectRole('<?= $role ?>', this)">
              <i data-lucide="<?= $info['icon'] ?>" class="w-7 h-7 mx-auto mb-2" style="color:<?= $info['color'] ?>"></i>
              <div class="font-bold text-sm" style="color:<?= $info['color'] ?>;font-family:'Space Grotesk',sans-serif;"><?= $role ?></div>
              <div class="text-xs text-gray-400 mt-1" style="font-family:'Mitr',sans-serif;"><?= $info['desc'] ?></div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Submit -->
        <button type="submit" id="submitBtn" class="btn-submit w-full py-4 rounded-xl text-lg flex items-center justify-center gap-3 uppercase tracking-wider">
          <i data-lucide="rocket" class="w-6 h-6"></i>
          <span>ยืนยันและเข้าสู่การทดสอบ</span>
        </button>
      </form>
    </div>

    <!-- Info note -->
    <p class="text-center text-gray-600 text-xs mt-6" style="font-family:'Mitr',sans-serif;">
      <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
      หากรหัสนักศึกษาของคุณมีอยู่ในระบบแล้ว จะโหลดประวัติเดิมให้อัตโนมัติ
    </p>
  </div>
</main>

<script>
lucide.createIcons();

function selectRole(role, el) {
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('primary_role').value = role;
}

document.getElementById('regForm').addEventListener('submit', function() {
  if (!document.getElementById('primary_role').value) {
    alert('กรุณาเลือกตำแหน่งที่ถนัด');
    return false;
  }
  const btn = document.getElementById('submitBtn');
  btn.innerHTML = '<svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> <span>กำลังประมวลผล...</span>';
  btn.disabled = true;
});
</script>
</body>
</html>
