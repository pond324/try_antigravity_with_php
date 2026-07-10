<?php
/**
 * db.php - Database Connection (PDO)
 * UDRU E-Sports Club Portal
 * ระบบเชื่อมต่อฐานข้อมูล PostgreSQL ผ่าน PHP PDO
 */

// ============================================================
// ตั้งค่าการเชื่อมต่อ PostgreSQL (ดึงค่าจาก env หรือใช้ค่าเริ่มต้นสำหรับ Local)
// ============================================================
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_PORT = getenv('DB_PORT') ?: '5432';
$DB_NAME = getenv('DB_NAME') ?: 'udru_esports';
$DB_USER = getenv('DB_USER') ?: 'postgres';
$DB_PASS = getenv('DB_PASS') ?: '1234';

$pdo = null;
$connection_error = '';

// PostgreSQL DSN
$dsn = "pgsql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME}";

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    $connection_error = $e->getMessage();
    $pdo = null;
}

// หากไม่สามารถเชื่อมต่อได้ ให้แสดง Error แบบ Friendly
if ($pdo === null) {
    $err_html = <<<HTML
    <!DOCTYPE html>
    <html lang="th">
    <head>
    <meta charset="UTF-8">
    <title>Database Error - UDRU E-Sports</title>
    <style>
        body { background: #0a0a0f; color: #00fff7; font-family: monospace; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { border: 1px solid #00fff7; padding: 2rem; max-width: 640px; box-shadow: 0 0 30px #00fff740; border-radius: 12px; }
        h2 { color: #ff4d4d; margin-bottom: 1rem; } code { background: #111; padding: 2px 6px; border-radius: 3px; color: #ffcc00; }
        li { margin: 6px 0; } pre { background: #111; padding: 12px; border-radius: 6px; color: #ff9f43; font-size: 0.8rem; overflow-x: auto; white-space: pre-wrap; }
    </style>
    </head>
    <body>
    <div class="box">
        <h2>⚠️ ไม่สามารถเชื่อมต่อ PostgreSQL ได้</h2>
        <p>กรุณาตรวจสอบ:</p>
        <ul>
            <li>ตรวจสอบว่าได้สั่งรัน Docker Stack ด้วยคำสั่ง <code>docker compose up -d</code> แล้ว</li>
            <li>ตรวจสอบข้อมูลโฮสต์/พอร์ต/ผู้ใช้/รหัสผ่าน ในระบบ Docker หรือในไฟล์ <code>db.php</code></li>
            <li>หากเรียกใช้งาน PHP แบบ Local: ตรวจสอบการเปิดใช้งาน extension <code>pdo_pgsql</code> และ <code>pgsql</code> ในไฟล์ <code>php.ini</code></li>
            <li>สร้างฐานข้อมูลชื่อ <code>{$DB_NAME}</code> หรือตรวจสอบการนำเข้า schema ใน PostgreSQL แล้วหรือยัง</li>
        </ul>
        <pre>Error: {$connection_error}</pre>
    </div>
    </body></html>
HTML;
    echo $err_html;
    exit;
}

/**
 * ฟังก์ชัน helper สำหรับ sanitize output
 */
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

