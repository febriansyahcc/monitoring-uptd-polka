-- ============================================================================
-- PLN Monitor ULPLTD Poka — hapus kolom penyulang ganda "Rinjali" (yang benar "Rijali")
-- Dibuat 2026-10-25. Setara dengan migration 2026_10_25_000001_merge_rinjali_feeder_into_rijali.
--
-- Data arus Rinjali dipindah ke Rijali. Bila jam yang sama terisi di kedua kolom, yang
-- dipertahankan adalah yang terakhir diubah. Setelah itu baris Rinjali dihapus.
--
-- CARA PAKAI (phpMyAdmin):
--   1. BACKUP dulu: pilih database -> tab Export -> Go.
--   2. Klik database di panel kiri.
--   3. Tab Import -> pilih file ini -> Go.
--
-- Aman dijalankan ulang: bila Rinjali sudah tidak ada, tidak ada yang berubah.
-- ============================================================================

SET NAMES utf8mb4;

SET @old := (SELECT `id` FROM `feeders` WHERE `name` = 'Rinjali' LIMIT 1);
SET @new := (SELECT `id` FROM `feeders` WHERE `name` = 'Rijali' LIMIT 1);

-- 1) Jam yang terisi di kedua kolom: buang yang lebih lama
DELETE o FROM `current_log_records` o
JOIN `current_log_records` n
  ON n.`feeder_id` = @new AND n.`recorded_date` = o.`recorded_date`
 AND n.`shift` = o.`shift` AND n.`time_interval` = o.`time_interval`
WHERE o.`feeder_id` = @old
  AND COALESCE(n.`updated_at`, '1970-01-01') >= COALESCE(o.`updated_at`, '1970-01-01');

DELETE n FROM `current_log_records` n
JOIN `current_log_records` o
  ON o.`feeder_id` = @old AND o.`recorded_date` = n.`recorded_date`
 AND o.`shift` = n.`shift` AND o.`time_interval` = n.`time_interval`
WHERE n.`feeder_id` = @new
  AND COALESCE(o.`updated_at`, '1970-01-01') > COALESCE(n.`updated_at`, '1970-01-01');

-- 2) Pindahkan sisa data Rinjali ke Rijali
UPDATE `current_log_records` SET `feeder_id` = @new WHERE `feeder_id` = @old AND @new IS NOT NULL;

-- 3) Hapus Rinjali (atau ganti namanya bila Rijali belum ada)
UPDATE `feeders` SET `name` = 'Rijali', `updated_at` = NOW() WHERE `id` = @old AND @new IS NULL;
DELETE FROM `feeders` WHERE `id` = @old AND @new IS NOT NULL;

-- 4) Catat migration agar `php artisan migrate` tidak menjalankannya lagi
SET @batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_25_000001_merge_rinjali_feeder_into_rijali', @batch FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_25_000001_merge_rinjali_feeder_into_rijali');

-- Selesai. Cek hasil: SELECT `id`, `name`, `code` FROM `feeders` ORDER BY `sort_order`;
