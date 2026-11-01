-- ============================================================================
-- PLN Monitor ULPLTD Poka — revisi daftar penyulang tabel arus
-- Dibuat 2026-10-28. Setara dengan migration 2026_10_28_000001_revise_feeder_list.
--
-- Urutan kolom baru: Wayame 2, Waiheru 1, Hitu, MVTIC 1, MVTIC 2, Rijali, Lateri 3,
-- Karpan 1, Lateri 2, Lateri 1, Galala 1, Galala 2, MCM.
-- Ditambah: Lateri 3, Galala 1, Galala 2. Dinonaktifkan: Tantui Atas, Karpan 2 (data arus tetap ada).
--
-- CARA PAKAI (phpMyAdmin):
--   1. BACKUP dulu: pilih database -> tab Export -> Go.
--   2. Klik database di panel kiri.
--   3. Tab Import -> pilih file ini -> Go.
--
-- Aman dijalankan ulang.
-- ============================================================================

SET NAMES utf8mb4;

INSERT INTO `feeders` (`name`, `code`, `is_active`, `sort_order`, `created_at`, `updated_at`)
SELECT 'Lateri 3', 'LTR-03', 1, 7, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `feeders` WHERE `name` = 'Lateri 3');

INSERT INTO `feeders` (`name`, `code`, `is_active`, `sort_order`, `created_at`, `updated_at`)
SELECT 'Galala 1', 'GLL-01', 1, 11, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `feeders` WHERE `name` = 'Galala 1');

INSERT INTO `feeders` (`name`, `code`, `is_active`, `sort_order`, `created_at`, `updated_at`)
SELECT 'Galala 2', 'GLL-02', 1, 12, NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `feeders` WHERE `name` = 'Galala 2');

UPDATE `feeders` SET `is_active` = 1, `updated_at` = NOW(), `sort_order` = CASE `name`
  WHEN 'Wayame 2' THEN 1  WHEN 'Waiheru 1' THEN 2  WHEN 'Hitu' THEN 3     WHEN 'MVTIC 1' THEN 4
  WHEN 'MVTIC 2' THEN 5   WHEN 'Rijali' THEN 6     WHEN 'Lateri 3' THEN 7 WHEN 'Karpan 1' THEN 8
  WHEN 'Lateri 2' THEN 9  WHEN 'Lateri 1' THEN 10  WHEN 'Galala 1' THEN 11 WHEN 'Galala 2' THEN 12
  WHEN 'MCM' THEN 13 END
WHERE `name` IN ('Wayame 2','Waiheru 1','Hitu','MVTIC 1','MVTIC 2','Rijali','Lateri 3','Karpan 1',
                 'Lateri 2','Lateri 1','Galala 1','Galala 2','MCM');

UPDATE `feeders` SET `is_active` = 0, `updated_at` = NOW() WHERE `name` IN ('Tantui Atas', 'Karpan 2');

SET @batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_28_000001_revise_feeder_list', @batch FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_28_000001_revise_feeder_list');

-- Cek hasil: SELECT `name`, `code`, `is_active`, `sort_order` FROM `feeders` ORDER BY `sort_order`;
