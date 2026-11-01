-- ============================================================================
-- PLN Monitor ULPLTD Poka — Control Panel: "kWh Produksi Utama" kedua menjadi "kWh Produksi Pembanding"
-- Dibuat 2026-10-29. Setara dengan migration 2026_10_29_000001_rename_kwh_produksi_columns_on_control_panel_logs.
--
-- Hanya ganti nama kolom; angka yang sudah diinput tetap utuh.
--   kwh_produksi_utama_1 -> kwh_produksi_utama
--   kwh_produksi_utama_2 -> kwh_produksi_pembanding
--
-- CARA PAKAI (phpMyAdmin):
--   1. BACKUP dulu: pilih database -> tab Export -> Go.
--   2. Klik database di panel kiri.
--   3. Tab Import -> pilih file ini -> Go.
--
-- Jalankan SEKALI saja (setelah staging-upgrade-2026-10-12.sql). Menjalankan ulang akan error
-- karena kolom lama sudah tidak ada; itu tidak merusak data.
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE `engine_control_panel_logs`
  CHANGE COLUMN `kwh_produksi_utama_1` `kwh_produksi_utama` decimal(15, 2) NULL,
  CHANGE COLUMN `kwh_produksi_utama_2` `kwh_produksi_pembanding` decimal(15, 2) NULL;

SET @batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_29_000001_rename_kwh_produksi_columns_on_control_panel_logs', @batch FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_29_000001_rename_kwh_produksi_columns_on_control_panel_logs');

-- Cek hasil: SHOW COLUMNS FROM `engine_control_panel_logs` LIKE 'kwh_produksi%';
