<?php
/**
 * update_score.php - API Endpoint รับคะแนนและบันทึก High Score
 * UDRU E-Sports Club Portal
 * Method: POST | Content-Type: application/json
 * Response: JSON
 */
session_start();
require_once 'db.php';

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// ============================================================
// ตรวจสอบ Session (ต้อง login ก่อน)
// ============================================================
if (!isset($_SESSION['applicant_id']) || !isset($_SESSION['student_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: กรุณาเข้าสู่ระบบก่อน',
        'redirect' => 'login.php'
    ]);
    exit;
}

// ============================================================
// รับ JSON body
// ============================================================
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

// ดึงคะแนนจาก payload
$new_score = isset($data['score']) ? (int)$data['score'] : -1;

if ($new_score < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid score field']);
    exit;
}

// ============================================================
// ดึงคะแนนสูงสุดเดิมจาก DB
// ============================================================
$applicant_id = (int)$_SESSION['applicant_id'];

$stmt = $pdo->prepare("SELECT game_score FROM rov_applicants WHERE id = ? LIMIT 1");
$stmt->execute([$applicant_id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลผู้สมัคร']);
    exit;
}

$old_score = (int)$row['game_score'];

// ============================================================
// High Score Update — อัปเดตเฉพาะกรณีคะแนนใหม่สูงกว่าเดิม
// ============================================================
$is_new_record = false;

if ($new_score > $old_score) {
    $update = $pdo->prepare("UPDATE rov_applicants SET game_score = ? WHERE id = ?");
    $update->execute([$new_score, $applicant_id]);
    $is_new_record = true;

    // อัปเดต Session ด้วย
    $_SESSION['game_score'] = $new_score;
}

// ============================================================
// ตอบกลับ JSON
// ============================================================
echo json_encode([
    'success'        => true,
    'is_new_record'  => $is_new_record,
    'new_score'      => $new_score,
    'best_score'     => $is_new_record ? $new_score : $old_score,
    'previous_best'  => $old_score,
    'in_game_name'   => $_SESSION['in_game_name'] ?? 'Player',
    'primary_role'   => $_SESSION['primary_role'] ?? '',
    'message'        => $is_new_record
        ? "🏆 สถิติใหม่! {$new_score} คะแนน (เดิม {$old_score})"
        : "คะแนน {$new_score} · สถิติสูงสุด {$old_score} ยังอยู่"
]);
