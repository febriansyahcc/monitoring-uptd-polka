-- ============================================================================
-- PLN Monitor ULPLTD Poka — kWh Produksi: Penyulang & PS Total dipisah
-- Dibuat 2026-10-28. Setara dengan migration 2026_10_28_000002_make_kwh_feeder_ps_nullable.
--
-- Kolom kWh PS / kWh Digital / PS Total dibuat boleh kosong (NULL) supaya "belum diisi"
-- berbeda dari 0. Baris lama yang ketiganya 0 dianggap belum diisi (tidak tampil di tab PS Total).
--
-- CARA PAKAI (phpMyAdmin):
--   1. BACKUP dulu: pilih database -> tab Export -> Go.
--   2. Klik database di panel kiri.
--   3. Tab Import -> pilih file ini -> Go.
--
-- Aman dijalankan ulang.
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE `kwh_feeder_logs`
  MODIFY `kwh_ps` decimal(15, 2) NULL DEFAULT NULL,
  MODIFY `kwh_digital` decimal(15, 2) NULL DEFAULT NULL,
  MODIFY `ps_total` decimal(15, 2) NULL DEFAULT NULL;

UPDATE `kwh_feeder_logs`
SET `kwh_ps` = NULL, `kwh_digital` = NULL, `ps_total` = NULL
WHERE `kwh_ps` = 0 AND `kwh_digital` = 0 AND `ps_total` = 0;

SET @batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_28_000002_make_kwh_feeder_ps_nullable', @batch FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_28_000002_make_kwh_feeder_ps_nullable');

-- Cek hasil: SHOW COLUMNS FROM `kwh_feeder_logs` LIKE 'kwh_%';
