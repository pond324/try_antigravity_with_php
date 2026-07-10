-- ============================================================
-- UDRU E-Sports Club Portal - Database Schema (PostgreSQL)
-- ============================================================
-- วิธีใช้: รันใน pgAdmin หรือผ่าน docker-entrypoint-initdb.d
-- ============================================================

-- สร้างตาราง rov_applicants (PostgreSQL syntax)
CREATE TABLE IF NOT EXISTS rov_applicants (
  id           SERIAL PRIMARY KEY,
  student_id   VARCHAR(15)   NOT NULL UNIQUE,
  fullname     VARCHAR(100)  NOT NULL,
  in_game_name VARCHAR(100)  NOT NULL,
  primary_role VARCHAR(100)  NOT NULL,
  game_score   INT           NOT NULL DEFAULT 0,
  created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ใส่ข้อมูลตัวอย่าง (Demo Data)
-- ใน PostgreSQL ใช้ ON CONFLICT DO NOTHING แทน INSERT IGNORE
INSERT INTO rov_applicants (student_id, fullname, in_game_name, primary_role, game_score)
VALUES
  ('6440101001', 'ธนภัทร วงศ์สุวรรณ', 'ThunderBolt',  'Carry',    2150),
  ('6440101002', 'ปิยะนาถ เพ็งพันธ์',   'NightOwl',    'Support',  1890),
  ('6440101003', 'กิตติพงศ์ แสนสุข',    'ShadowBlade', 'Assassin', 2480),
  ('6440101004', 'วาริณี ศรีวิชัย',      'CrystalMage', 'Mage',     1760),
  ('6440101005', 'สรายุทธ บุญเลิศ',     'IronWall',    'Fighter',  2030)
ON CONFLICT (student_id) DO NOTHING;
