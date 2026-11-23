-- ============================================================================
-- PLN Monitor ULPLTD Poka — INSTALASI BARU (database kosong) per 2026-11-02
-- Setara dengan: php artisan migrate --force  +  db:seed (FeederSeeder) + izin + 1 akun admin
--
-- Hasil akhir identik dengan database staging yang sudah dijalankan semua file
-- staging-*.sql. JANGAN jalankan file staging-*.sql di database ini — file itu hanya
-- tambalan untuk database lama dan akan error / tidak perlu di database baru.
--
-- SEBELUM IMPORT: ganti teks  __GANTI_HASH_BCRYPT__  di bagian akhir dengan hash password admin
-- (buat di PC:  php -r "echo password_hash('PasswordKuatAnda', PASSWORD_BCRYPT);"  )
--
-- CARA PAKAI (phpMyAdmin): pilih database kosong -> tab Import -> pilih file ini -> Go.
-- Hanya untuk database KOSONG. Menjalankan ulang pada database terisi akan error di CREATE TABLE.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Tabel bawaan Laravel
-- ---------------------------------------------------------------------------

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operator',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_nip_unique` (`nip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Tabel aplikasi
-- ---------------------------------------------------------------------------

CREATE TABLE `feeders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feeders_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `current_log_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recorded_date` date NOT NULL,
  `shift` enum('pagi','sore','malam') COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_interval` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feeder_id` bigint unsigned NOT NULL,
  `current_value` decimal(8,2) DEFAULT NULL,
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_current_log` (`recorded_date`,`shift`,`time_interval`,`feeder_id`),
  KEY `current_log_records_feeder_id_foreign` (`feeder_id`),
  CONSTRAINT `current_log_records_feeder_id_foreign` FOREIGN KEY (`feeder_id`) REFERENCES `feeders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel lama, tidak dipakai lagi aplikasi (dibuat agar sama dengan staging)
CREATE TABLE `kwh_production_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recorded_date` date NOT NULL,
  `kwh_ps` decimal(12,2) NOT NULL DEFAULT '0.00',
  `kwh_digital_1` decimal(12,2) NOT NULL DEFAULT '0.00',
  `kwh_digital_2` decimal(12,2) NOT NULL DEFAULT '0.00',
  `kwh_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kwh_production_logs_recorded_date_unique` (`recorded_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `operational_disturbances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_date` date NOT NULL,
  `event_time` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disturbance_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Dalam Penanganan',
  `description` text COLLATE utf8mb4_unicode_ci,
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fuel_stocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recorded_date` date NOT NULL,
  `daily_consumption` decimal(12,2) NOT NULL DEFAULT '0.00',
  `main_tank` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_gross` decimal(12,2) NOT NULL DEFAULT '0.00',
  `death_stock` decimal(12,2) NOT NULL DEFAULT '0.00',
  `unloading` decimal(12,2) NOT NULL DEFAULT '0.00',
  `netto_stock` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estimated_daily_consumption` decimal(12,2) NOT NULL DEFAULT '0.00',
  `days_of_supply` decimal(8,2) NOT NULL DEFAULT '0.00',
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fuel_stocks_recorded_date_unique` (`recorded_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `is_granted` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_permissions_user_id_permission_id_unique` (`user_id`,`permission_id`),
  KEY `user_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kwh_engine_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recorded_date` date NOT NULL,
  `engine` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stand_akhir` decimal(15,2) DEFAULT NULL,
  `stand_edmi_mk10` decimal(15,2) DEFAULT NULL,
  `stand_kwh_ps` decimal(15,2) DEFAULT NULL,
  `flowmeter_in` decimal(15,2) DEFAULT NULL,
  `flowmeter_out` decimal(15,2) DEFAULT NULL,
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kwh_engine_logs_recorded_date_engine_unique` (`recorded_date`,`engine`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kwh_feeder_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recorded_date` date NOT NULL,
  `feeder` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pm800_ex` decimal(15,2) DEFAULT NULL,
  `pm800_im` decimal(15,2) DEFAULT NULL,
  `edmi_mk10_ex` decimal(15,2) DEFAULT NULL,
  `edmi_mk10_im` decimal(15,2) DEFAULT NULL,
  `kwh_ps` decimal(15,2) DEFAULT NULL,
  `kwh_digital` decimal(15,2) DEFAULT NULL,
  `ps_total` decimal(15,2) DEFAULT NULL,
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kwh_feeder_logs_recorded_date_feeder_unique` (`recorded_date`,`feeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `engine_control_panel_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recorded_date` date NOT NULL,
  `recorded_time` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kw` decimal(12,2) DEFAULT NULL,
  `cos_q` decimal(5,3) DEFAULT NULL,
  `freq_hz` decimal(8,2) DEFAULT NULL,
  `amp_r` decimal(12,2) DEFAULT NULL,
  `amp_s` decimal(12,2) DEFAULT NULL,
  `amp_t` decimal(12,2) DEFAULT NULL,
  `volt_rs` decimal(12,2) DEFAULT NULL,
  `volt_st` decimal(12,2) DEFAULT NULL,
  `volt_tr` decimal(12,2) DEFAULT NULL,
  `bearing_temp_de` decimal(8,2) DEFAULT NULL,
  `bearing_temp_od` decimal(8,2) DEFAULT NULL,
  `exiter_v` decimal(12,2) DEFAULT NULL,
  `exiter_a` decimal(12,2) DEFAULT NULL,
  `winding_temp_r` decimal(8,2) DEFAULT NULL,
  `winding_temp_s` decimal(8,2) DEFAULT NULL,
  `winding_temp_t` decimal(8,2) DEFAULT NULL,
  `kwh_produksi_utama` decimal(15,2) DEFAULT NULL,
  `kwh_produksi_pembanding` decimal(15,2) DEFAULT NULL,
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_control_panel_log` (`recorded_date`,`recorded_time`,`engine`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `engine_area_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recorded_date` date NOT NULL,
  `recorded_time` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `engine` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `speed_engine` decimal(12,2) DEFAULT NULL,
  `turbo_speed_r` decimal(12,2) DEFAULT NULL,
  `turbo_speed_l` decimal(12,2) DEFAULT NULL,
  `air_inlet_restriction` decimal(12,2) DEFAULT NULL,
  `temp_oil` decimal(8,2) DEFAULT NULL,
  `temp_engine_coolant` decimal(8,2) DEFAULT NULL,
  `temp_manifold_air` decimal(8,2) DEFAULT NULL,
  `temp_turbo_exhaust_l` decimal(8,2) DEFAULT NULL,
  `temp_exhaust_stack_l` decimal(8,2) DEFAULT NULL,
  `temp_turbo_exhaust_r` decimal(8,2) DEFAULT NULL,
  `temp_exhaust_stack_r` decimal(8,2) DEFAULT NULL,
  `press_oil` decimal(8,2) DEFAULT NULL,
  `press_fuel` decimal(8,2) DEFAULT NULL,
  `press_oil_filter` decimal(8,2) DEFAULT NULL,
  `press_fuel_filter` decimal(8,2) DEFAULT NULL,
  `press_air_inlet_manifold` decimal(8,2) DEFAULT NULL,
  `press_air_inlet_restriction` decimal(8,2) DEFAULT NULL,
  `press_exhaust_stack_l` decimal(8,2) DEFAULT NULL,
  `press_turbo_exhaust_r` decimal(8,2) DEFAULT NULL,
  `press_exhaust_stack_r` decimal(8,2) DEFAULT NULL,
  `cyl_head_no1` decimal(8,2) DEFAULT NULL,
  `cyl_head_no2` decimal(8,2) DEFAULT NULL,
  `cyl_head_no3` decimal(8,2) DEFAULT NULL,
  `cyl_head_no4` decimal(8,2) DEFAULT NULL,
  `cyl_head_no5` decimal(8,2) DEFAULT NULL,
  `cyl_head_no6` decimal(8,2) DEFAULT NULL,
  `cyl_head_no7` decimal(8,2) DEFAULT NULL,
  `cyl_head_no8` decimal(8,2) DEFAULT NULL,
  `cyl_head_no9` decimal(8,2) DEFAULT NULL,
  `cyl_head_no10` decimal(8,2) DEFAULT NULL,
  `cyl_head_no11` decimal(8,2) DEFAULT NULL,
  `cyl_head_no12` decimal(8,2) DEFAULT NULL,
  `jacket_water_in` decimal(8,2) DEFAULT NULL,
  `jacket_water_out` decimal(8,2) DEFAULT NULL,
  `acoc_water_in` decimal(8,2) DEFAULT NULL,
  `acoc_water_out` decimal(8,2) DEFAULT NULL,
  `flowmeter_in` decimal(15,2) DEFAULT NULL,
  `flowmeter_out` decimal(15,2) DEFAULT NULL,
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_engine_area_log` (`recorded_date`,`recorded_time`,`engine`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `abc_control_panel_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recorded_date` date NOT NULL,
  `recorded_time` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stator_r` decimal(12,2) DEFAULT NULL,
  `stator_s` decimal(12,2) DEFAULT NULL,
  `stator_t` decimal(12,2) DEFAULT NULL,
  `cd_bearing` decimal(12,2) DEFAULT NULL,
  `amp_r` decimal(12,2) DEFAULT NULL,
  `amp_s` decimal(12,2) DEFAULT NULL,
  `amp_t` decimal(12,2) DEFAULT NULL,
  `volt_r` decimal(12,2) DEFAULT NULL,
  `volt_s` decimal(12,2) DEFAULT NULL,
  `volt_t` decimal(12,2) DEFAULT NULL,
  `kw` decimal(12,2) DEFAULT NULL,
  `cos_q` decimal(5,3) DEFAULT NULL,
  `freq_hz` decimal(8,2) DEFAULT NULL,
  `kva` decimal(12,2) DEFAULT NULL,
  `kvar` decimal(12,2) DEFAULT NULL,
  `kwh_produksi_utama` decimal(15,2) DEFAULT NULL,
  `kwh_produksi_pembanding` decimal(15,2) DEFAULT NULL,
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_abc_control_panel_log` (`recorded_date`,`recorded_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `abc_engine_area_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recorded_date` date NOT NULL,
  `recorded_time` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `oil_cooler_oil_temp_in` decimal(12,2) DEFAULT NULL,
  `oil_cooler_oil_tekanan_out` decimal(12,2) DEFAULT NULL,
  `oil_cooler_water_temp_in` decimal(12,2) DEFAULT NULL,
  `oil_cooler_water_tekanan_out` decimal(12,2) DEFAULT NULL,
  `fuel_rack_1_a` decimal(12,2) DEFAULT NULL,
  `fuel_rack_1_b` decimal(12,2) DEFAULT NULL,
  `fuel_rack_2_a` decimal(12,2) DEFAULT NULL,
  `fuel_rack_2_b` decimal(12,2) DEFAULT NULL,
  `fuel_rack_3_a` decimal(12,2) DEFAULT NULL,
  `fuel_rack_3_b` decimal(12,2) DEFAULT NULL,
  `fuel_rack_4_a` decimal(12,2) DEFAULT NULL,
  `fuel_rack_4_b` decimal(12,2) DEFAULT NULL,
  `fuel_rack_5_a` decimal(12,2) DEFAULT NULL,
  `fuel_rack_5_b` decimal(12,2) DEFAULT NULL,
  `fuel_rack_6_a` decimal(12,2) DEFAULT NULL,
  `fuel_rack_6_b` decimal(12,2) DEFAULT NULL,
  `rpm` decimal(12,2) DEFAULT NULL,
  `lub_oil_temp` decimal(12,2) DEFAULT NULL,
  `oil_temp_bef_cooler` decimal(12,2) DEFAULT NULL,
  `oil_press` decimal(12,2) DEFAULT NULL,
  `ht_temp` decimal(12,2) DEFAULT NULL,
  `water_temp_cac_cw_inlet` decimal(12,2) DEFAULT NULL,
  `water_temp_cac_cw_outlet` decimal(12,2) DEFAULT NULL,
  `water_temp_engine_cw_inlet` decimal(12,2) DEFAULT NULL,
  `water_temp_engine_cw_outlet` decimal(12,2) DEFAULT NULL,
  `water_temp_radiator_cw_inlet` decimal(12,2) DEFAULT NULL,
  `water_temp_radiator_cw_outlet` decimal(12,2) DEFAULT NULL,
  `turbo_press` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_1_a` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_1_b` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_2_a` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_2_b` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_3_a` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_3_b` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_4_a` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_4_b` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_5_a` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_5_b` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_6_a` decimal(12,2) DEFAULT NULL,
  `cw_cylinder_6_b` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_1_a` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_1_b` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_2_a` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_2_b` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_3_a` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_3_b` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_4_a` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_4_b` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_5_a` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_5_b` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_6_a` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_6_b` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_after_tc_a` decimal(12,2) DEFAULT NULL,
  `exhaust_gas_after_tc_b` decimal(12,2) DEFAULT NULL,
  `gov_load_limit` decimal(12,2) DEFAULT NULL,
  `fuel_rack_a` decimal(12,2) DEFAULT NULL,
  `fuel_rack_b` decimal(12,2) DEFAULT NULL,
  `flowmeter_hsd_in` decimal(15,2) DEFAULT NULL,
  `flowmeter_hsd_out` decimal(15,2) DEFAULT NULL,
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_modified_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_abc_engine_area_log` (`recorded_date`,`recorded_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- Data awal: penyulang (FeederSeeder), 13 aktif
-- ---------------------------------------------------------------------------

INSERT INTO `feeders` (`name`, `code`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
  ('Wayame 2',  'WYM-02', 1, 1,  NOW(), NOW()),
  ('Waiheru 1', 'WHR-01', 1, 2,  NOW(), NOW()),
  ('Hitu',      'HTU-01', 1, 3,  NOW(), NOW()),
  ('MVTIC 1',   'MVT-01', 1, 4,  NOW(), NOW()),
  ('MVTIC 2',   'MVT-02', 1, 5,  NOW(), NOW()),
  ('Rijali',    'RNJ-01', 1, 6,  NOW(), NOW()),
  ('Lateri 3',  'LTR-03', 1, 7,  NOW(), NOW()),
  ('Karpan 1',  'KRP-01', 1, 8,  NOW(), NOW()),
  ('Lateri 2',  'LTR-02', 1, 9,  NOW(), NOW()),
  ('Lateri 1',  'LTR-01', 1, 10, NOW(), NOW()),
  ('Galala 1',  'GLL-01', 1, 11, NOW(), NOW()),
  ('Galala 2',  'GLL-02', 1, 12, NOW(), NOW()),
  ('MCM',       'MCM-01', 1, 13, NOW(), NOW());

-- ---------------------------------------------------------------------------
-- Data awal: daftar izin (master permission, sama dengan UserSeeder)
-- ---------------------------------------------------------------------------

INSERT INTO `permissions` (`name`, `slug`, `category`, `description`, `created_at`, `updated_at`) VALUES
  ('Lihat Monitoring Arus', 'monitoring_arus.view', 'Monitoring Arus', 'Melihat data pencatatan arus feeder', NOW(), NOW()),
  ('Input/Edit Monitoring Arus', 'monitoring_arus.input', 'Monitoring Arus', 'Menginput dan memperbarui pencatatan arus', NOW(), NOW()),
  ('Lihat Monitoring kWh', 'monitoring_kwh.view', 'Monitoring kWh', 'Melihat data produksi kWh', NOW(), NOW()),
  ('Input/Edit Monitoring kWh', 'monitoring_kwh.input', 'Monitoring kWh', 'Menginput dan memperbarui data kWh produksi', NOW(), NOW()),
  ('Lihat Monitoring Gangguan', 'monitoring_gangguan.view', 'Monitoring Gangguan', 'Melihat riwayat gangguan operasional', NOW(), NOW()),
  ('Kelola Monitoring Gangguan', 'monitoring_gangguan.manage', 'Monitoring Gangguan', 'Menambah, mengedit, dan menghapus gangguan', NOW(), NOW()),
  ('Lihat Monitoring BBM', 'monitoring_bbm.view', 'Monitoring Stok BBM', 'Melihat stok dan pemakaian BBM', NOW(), NOW()),
  ('Input/Edit Monitoring BBM', 'monitoring_bbm.input', 'Monitoring Stok BBM', 'Menginput stok dan pemakaian BBM harian', NOW(), NOW()),
  ('Lihat Monitoring Operasi Engine', 'monitoring_engine.view', 'Monitoring Operasi Engine', 'Melihat data control panel & engine area', NOW(), NOW()),
  ('Input/Edit Monitoring Operasi Engine', 'monitoring_engine.input', 'Monitoring Operasi Engine', 'Menginput dan memperbarui data control panel & engine area', NOW(), NOW()),
  ('Verifikasi Data Operasional', 'data.verify', 'Verifikasi', 'Memverifikasi dan menyetujui log data operasional', NOW(), NOW()),
  ('Pengelolaan Pengguna (User Management)', 'users.manage', 'Sistem Admin', 'Mengelola akun pengguna dan izin PBAC', NOW(), NOW());

-- ---------------------------------------------------------------------------
-- Akun admin pertama. Akun lain dibuat dari menu Pengguna setelah login.
-- Admin otomatis punya semua izin (tidak perlu baris user_permissions).
-- ---------------------------------------------------------------------------

INSERT INTO `users` (`name`, `email`, `nip`, `role`, `is_active`, `password`, `created_at`, `updated_at`)
VALUES ('Administrator System', 'admin@pln.co.id', '9900112233', 'admin', 1, '__GANTI_HASH_BCRYPT__', NOW(), NOW());

-- ---------------------------------------------------------------------------
-- Catat semua migration supaya `php artisan migrate` tidak menjalankannya lagi
-- ---------------------------------------------------------------------------

INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('0001_01_01_000000_create_users_table', 1),
  ('0001_01_01_000001_create_cache_table', 1),
  ('0001_01_01_000002_create_jobs_table', 1),
  ('2026_08_04_000001_create_feeders_table', 1),
  ('2026_08_04_000002_create_current_log_records_table', 1),
  ('2026_08_04_000003_create_kwh_production_logs_table', 1),
  ('2026_08_04_000004_create_operational_disturbances_table', 1),
  ('2026_08_04_000005_create_fuel_stocks_table', 1),
  ('2026_08_04_000006_create_pbac_tables', 1),
  ('2026_09_24_000001_create_kwh_engine_logs_table', 1),
  ('2026_09_24_000002_create_kwh_feeder_logs_table', 1),
  ('2026_09_24_000003_create_engine_control_panel_logs_table', 1),
  ('2026_09_24_000004_create_engine_area_logs_table', 1),
  ('2026_09_24_000005_add_engine_operation_permissions', 1),
  ('2026_10_25_000001_merge_rinjali_feeder_into_rijali', 1),
  ('2026_10_28_000001_revise_feeder_list', 1),
  ('2026_10_28_000002_make_kwh_feeder_ps_nullable', 1),
  ('2026_10_28_000003_add_speed_engine_to_engine_area_logs', 1),
  ('2026_10_29_000001_rename_kwh_produksi_columns_on_control_panel_logs', 1),
  ('2026_10_31_000001_create_abc_control_panel_logs_table', 1),
  ('2026_10_31_000002_create_abc_engine_area_logs_table', 1);

-- Cek hasil: SHOW TABLES;  (harus 22 tabel)   SELECT COUNT(*) FROM migrations;  (21 baris)
