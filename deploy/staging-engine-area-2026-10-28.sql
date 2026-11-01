-- ============================================================================
-- PLN Monitor ULPLTD Poka — Engine Area: kolom Speed Engine & pilihan Pressure baru
-- Dibuat 2026-10-28. Setara dengan migration 2026_10_28_000003_add_speed_engine_to_engine_area_logs.
--
-- Menambah kolom speed_engine, press_oil dan press_air_inlet_restriction.
-- Kolom pressure lama (Exhaust Stack L, Turbo Exhaust R, Exhaust Stack R) dibiarkan, data aman.
--
-- CARA PAKAI (phpMyAdmin):
--   1. BACKUP dulu: pilih database -> tab Export -> Go.
--   2. Klik database di panel kiri.
--   3. Tab Import -> pilih file ini -> Go.
--
-- Jalankan SEKALI saja (setelah staging-upgrade-2026-10-12.sql). Menjalankan ulang akan error
-- "Duplicate column name" karena kolom sudah ada; itu tidak merusak data.
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE `engine_area_logs`
  ADD COLUMN `speed_engine` decimal(12, 2) NULL AFTER `engine`,
  ADD COLUMN `press_oil` decimal(8, 2) NULL AFTER `temp_exhaust_stack_r`,
  ADD COLUMN `press_air_inlet_restriction` decimal(8, 2) NULL AFTER `press_air_inlet_manifold`;

SET @batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_28_000003_add_speed_engine_to_engine_area_logs', @batch FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_28_000003_add_speed_engine_to_engine_area_logs');

-- Cek hasil: SHOW COLUMNS FROM `engine_area_logs` WHERE `Field` IN ('speed_engine','press_oil','press_air_inlet_restriction');
