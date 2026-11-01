-- ============================================================================
-- PLN Monitor ULPLTD Poka — Operasi Engine: tabel Control Panel & Engine Area untuk ABC #6
-- Dibuat 2026-10-31. Setara dengan migration:
--   2026_10_31_000001_create_abc_control_panel_logs_table
--   2026_10_31_000002_create_abc_engine_area_logs_table
--
-- Membuat dua tabel baru (CREATE TABLE IF NOT EXISTS, tidak menyentuh tabel lain).
--
-- CARA PAKAI (phpMyAdmin):
--   1. BACKUP dulu: pilih database -> tab Export -> Go.
--   2. Klik database di panel kiri.
--   3. Tab Import -> pilih file ini -> Go.
--
-- Aman dijalankan ulang.
-- ============================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `abc_control_panel_logs` (
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

CREATE TABLE IF NOT EXISTS `abc_engine_area_logs` (
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


SET @batch := (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_31_000001_create_abc_control_panel_logs_table', @batch FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_31_000001_create_abc_control_panel_logs_table');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_31_000002_create_abc_engine_area_logs_table', @batch FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_31_000002_create_abc_engine_area_logs_table');

-- Cek hasil: SHOW TABLES LIKE 'abc_%';
