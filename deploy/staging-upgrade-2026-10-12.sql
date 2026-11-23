-- ============================================================================
-- PLN Monitor ULPLTD Poka — upgrade database staging ke versi `main` / PR #3
-- Dibuat 2026-10-12 dari output `php artisan migrate --pretend` (skema identik dengan migration).
--
-- Setara dengan menjalankan 5 migration yang belum pernah dijalankan di staging:
--   2026_09_24_000001_create_kwh_engine_logs_table
--   2026_09_24_000002_create_kwh_feeder_logs_table
--   2026_09_24_000003_create_engine_control_panel_logs_table
--   2026_09_24_000004_create_engine_area_logs_table
--   2026_09_24_000005_add_engine_operation_permissions
-- lalu mencatatnya di tabel `migrations`, supaya `php artisan migrate` di kemudian hari
-- tidak menjalankannya lagi.
--
-- CARA PAKAI (phpMyAdmin):
--   1. BACKUP dulu: pilih database staging -> tab Export -> Go.
--   2. Klik database `feby7274_monitoring_pln_staging` di panel kiri.
--   3. Tab Import -> pilih file ini -> Go.
--
-- Aman dijalankan ulang: tabel hanya dibuat bila belum ada, izin & catatan migration tidak
-- diduplikasi. Tidak ada tabel atau data lama yang dihapus. Tabel lama `kwh_production_logs`
-- dibiarkan (tidak dipakai lagi oleh aplikasi).
-- ============================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- 1) Tabel baru (migration 000001–000004)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `kwh_engine_logs` (`id` bigint unsigned not null auto_increment primary key, `recorded_date` date not null, `engine` varchar(20) not null, `stand_akhir` decimal(15, 2) null, `stand_edmi_mk10` decimal(15, 2) null, `stand_kwh_ps` decimal(15, 2) null, `flowmeter_in` decimal(15, 2) null, `flowmeter_out` decimal(15, 2) null, `operator_name` varchar(255) null, `last_modified_time` varchar(10) null, `last_modified_date` date null, `created_at` timestamp null, `updated_at` timestamp null, UNIQUE KEY `kwh_engine_logs_recorded_date_engine_unique` (`recorded_date`, `engine`)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS `kwh_feeder_logs` (`id` bigint unsigned not null auto_increment primary key, `recorded_date` date not null, `feeder` varchar(30) not null, `pm800_ex` decimal(15, 2) null, `pm800_im` decimal(15, 2) null, `edmi_mk10_ex` decimal(15, 2) null, `edmi_mk10_im` decimal(15, 2) null, `kwh_ps` decimal(15, 2) not null default '0', `kwh_digital` decimal(15, 2) not null default '0', `ps_total` decimal(15, 2) not null default '0', `operator_name` varchar(255) null, `last_modified_time` varchar(10) null, `last_modified_date` date null, `created_at` timestamp null, `updated_at` timestamp null, UNIQUE KEY `kwh_feeder_logs_recorded_date_feeder_unique` (`recorded_date`, `feeder`)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS `engine_control_panel_logs` (`id` bigint unsigned not null auto_increment primary key, `recorded_date` date not null, `recorded_time` varchar(5) not null, `engine` varchar(20) not null, `kw` decimal(12, 2) null, `cos_q` decimal(5, 3) null, `freq_hz` decimal(8, 2) null, `amp_r` decimal(12, 2) null, `amp_s` decimal(12, 2) null, `amp_t` decimal(12, 2) null, `volt_rs` decimal(12, 2) null, `volt_st` decimal(12, 2) null, `volt_tr` decimal(12, 2) null, `bearing_temp_de` decimal(8, 2) null, `bearing_temp_od` decimal(8, 2) null, `exiter_v` decimal(12, 2) null, `exiter_a` decimal(12, 2) null, `winding_temp_r` decimal(8, 2) null, `winding_temp_s` decimal(8, 2) null, `winding_temp_t` decimal(8, 2) null, `kwh_produksi_utama_1` decimal(15, 2) null, `kwh_produksi_utama_2` decimal(15, 2) null, `operator_name` varchar(255) null, `last_modified_time` varchar(10) null, `last_modified_date` date null, `created_at` timestamp null, `updated_at` timestamp null, UNIQUE KEY `unique_control_panel_log` (`recorded_date`, `recorded_time`, `engine`)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS `engine_area_logs` (`id` bigint unsigned not null auto_increment primary key, `recorded_date` date not null, `recorded_time` varchar(5) not null, `engine` varchar(20) not null, `turbo_speed_r` decimal(12, 2) null, `turbo_speed_l` decimal(12, 2) null, `air_inlet_restriction` decimal(12, 2) null, `temp_oil` decimal(8, 2) null, `temp_engine_coolant` decimal(8, 2) null, `temp_manifold_air` decimal(8, 2) null, `temp_turbo_exhaust_l` decimal(8, 2) null, `temp_exhaust_stack_l` decimal(8, 2) null, `temp_turbo_exhaust_r` decimal(8, 2) null, `temp_exhaust_stack_r` decimal(8, 2) null, `press_fuel` decimal(8, 2) null, `press_oil_filter` decimal(8, 2) null, `press_fuel_filter` decimal(8, 2) null, `press_air_inlet_manifold` decimal(8, 2) null, `press_exhaust_stack_l` decimal(8, 2) null, `press_turbo_exhaust_r` decimal(8, 2) null, `press_exhaust_stack_r` decimal(8, 2) null, `cyl_head_no1` decimal(8, 2) null, `cyl_head_no2` decimal(8, 2) null, `cyl_head_no3` decimal(8, 2) null, `cyl_head_no4` decimal(8, 2) null, `cyl_head_no5` decimal(8, 2) null, `cyl_head_no6` decimal(8, 2) null, `cyl_head_no7` decimal(8, 2) null, `cyl_head_no8` decimal(8, 2) null, `cyl_head_no9` decimal(8, 2) null, `cyl_head_no10` decimal(8, 2) null, `cyl_head_no11` decimal(8, 2) null, `cyl_head_no12` decimal(8, 2) null, `jacket_water_in` decimal(8, 2) null, `jacket_water_out` decimal(8, 2) null, `acoc_water_in` decimal(8, 2) null, `acoc_water_out` decimal(8, 2) null, `flowmeter_in` decimal(15, 2) null, `flowmeter_out` decimal(15, 2) null, `operator_name` varchar(255) null, `last_modified_time` varchar(10) null, `last_modified_date` date null, `created_at` timestamp null, `updated_at` timestamp null, UNIQUE KEY `unique_engine_area_log` (`recorded_date`, `recorded_time`, `engine`)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci';

-- ---------------------------------------------------------------------------
-- 2) Izin Monitoring Operasi Engine (migration 000005)
-- ---------------------------------------------------------------------------

INSERT INTO `permissions` (`name`, `slug`, `category`, `description`, `created_at`, `updated_at`) VALUES
  ('Lihat Monitoring Operasi Engine', 'monitoring_engine.view', 'Monitoring Operasi Engine', 'Melihat data control panel & engine area', NOW(), NOW()),
  ('Input/Edit Monitoring Operasi Engine', 'monitoring_engine.input', 'Monitoring Operasi Engine', 'Menginput dan memperbarui data control panel & engine area', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`), `category` = VALUES(`category`), `description` = VALUES(`description`), `updated_at` = NOW();

-- Hak akses default untuk user yang sudah ada (admin selalu punya semua izin, tidak perlu baris)
INSERT INTO `user_permissions` (`user_id`, `permission_id`, `is_granted`, `created_at`, `updated_at`)
SELECT u.`id`, p.`id`, 1, NOW(), NOW()
FROM `users` u
JOIN `permissions` p ON p.`slug` IN ('monitoring_engine.view', 'monitoring_engine.input')
WHERE (u.`role` = 'manager'         AND p.`slug` = 'monitoring_engine.view')
   OR (u.`role` = 'tl_operasi'      AND p.`slug` IN ('monitoring_engine.view', 'monitoring_engine.input'))
   OR (u.`role` = 'tl_pemeliharaan' AND p.`slug` = 'monitoring_engine.view')
   OR (u.`role` = 'operator'        AND p.`slug` IN ('monitoring_engine.view', 'monitoring_engine.input'))
ON DUPLICATE KEY UPDATE `is_granted` = 1, `updated_at` = NOW();

-- ---------------------------------------------------------------------------
-- 3) Koreksi nama feeder (FeederSeeder: "Rinjali" -> "Rijali")
-- ---------------------------------------------------------------------------

UPDATE `feeders` SET `name` = 'Rijali', `updated_at` = NOW() WHERE `code` = 'RNJ-01' AND `name` = 'Rinjali';

-- ---------------------------------------------------------------------------
-- 4) Catat migration agar `php artisan migrate` tidak menjalankannya lagi
-- ---------------------------------------------------------------------------

SET @batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.`migration`, @batch
FROM (
  SELECT '2026_09_24_000001_create_kwh_engine_logs_table' AS `migration`
  UNION ALL SELECT '2026_09_24_000002_create_kwh_feeder_logs_table'
  UNION ALL SELECT '2026_09_24_000003_create_engine_control_panel_logs_table'
  UNION ALL SELECT '2026_09_24_000004_create_engine_area_logs_table'
  UNION ALL SELECT '2026_09_24_000005_add_engine_operation_permissions'
) m
WHERE NOT EXISTS (SELECT 1 FROM `migrations` x WHERE x.`migration` = m.`migration`);

-- Selesai. Cek hasil: SELECT `migration`, `batch` FROM `migrations` ORDER BY `id` DESC LIMIT 5;
