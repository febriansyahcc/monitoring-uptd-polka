# Laporan Analisis & Implementasi PA 2026
> Dokumen: `PA 2026 (2).pdf` | Diperbarui: 2026-09-24

---

## Checklist Pengerjaan

> Direvisi setelah membaca gambar tabel halaman 6 & 7 PDF: Arus Tiap Fasa ternyata rumus tetap (tanpa tabel baru), dan kWh ENGINE/PENYULANG punya kolom baru sehingga butuh tabel baru.

**kWh Produksi (ENGINE / PENYULANG)**
- [x] 1. Migration + model `kwh_engine_logs` & `kwh_feeder_logs`
- [x] 2. Controller kWh (store/destroy per tab) + routes
- [x] 3. Halaman kWh dengan tab ENGINE / PENYULANG
- [x] 4. Dashboard: KPI kWh membaca data ENGINE

**Arus Tiap Fasa**
- [x] 5. Konfigurasi pengurangan per fasa + perhitungan di controller
- [x] 6. Tab "Arus Tiap Fasa" di halaman Monitoring Arus

**Monitoring Operasi Engine**
- [x] 7. Migration `engine_control_panel_logs`
- [x] 8. Migration `engine_area_logs`
- [x] 9. Model `EngineControlPanelLog` & `EngineAreaLog`
- [x] 10. `EngineOperationController` + routes
- [x] 11. Permission + menu Sidebar / BottomNav
- [x] 12. Komponen `ControlPanelTable.vue`
- [x] 13. Komponen `EngineAreaTable.vue` (pilih choice → muncul kotak nilai)
- [x] 14. Halaman `Pages/EngineOperation/Index.vue`
- [x] 15. Verifikasi: migrate, build, uji simpan data

---

## Status Fitur vs PA 2026

| # | Fitur (PDF) | Status |
|---|-------------|--------|
| 1A | Monitoring Arus — Beban & Arus Penyulang | ✅ Sudah ada sebelumnya |
| 1B | Monitoring Arus — Arus Tiap Fasa | ✅ Dibuat |
| 2A | Monitoring kWh — ENGINE | ✅ Dibuat (tabel baru) |
| 2B | Monitoring kWh — PENYULANG | ✅ Dibuat (tabel baru) |
| 3  | Monitoring Operasi Engine — Control Panel & Engine Area | ✅ Dibuat (modul baru) |
| 4  | Monitoring Gangguan | ✅ Sudah ada sebelumnya |
| 5  | Monitoring BBM | ✅ Sudah ada sebelumnya |

---

## 1B — Arus Tiap Fasa

**Sumber:** PDF halaman 6. Nilai terisi otomatis dari tabel Beban & Arus Penyulang, dikurangi angka tetap per fasa (hasil ditampilkan apa adanya, bisa negatif). Tidak ada input manual dan tidak ada tabel baru.

| Penyulang | Kode feeder | R | S | T |
|-----------|-------------|---|---|---|
| Wayame 2 | WYM-02 | −2 | 0 | 0 |
| Waiheru 1 | WHR-01 | −2 | −5 | 0 |
| Hitu | HTU-01 | −4 | −4 | 0 |
| Galala 1 (MVTIC 2) | MVT-02 | −2 | 0 | 0 |
| Galala 2 (MVTIC 1) | MVT-01 | −2 | −3 | 0 |

**File:**
- `app/Http/Controllers/CurrentMonitoringController.php` berisi konstanta `PHASE_REDUCTIONS` dan perhitungan `phaseMatrix`. Kalau angka pengurangan berubah, cukup ubah konstanta ini.
- `resources/js/Components/CurrentMonitoring/PhaseCurrentTable.vue` berisi tabel read-only. Header fasa menampilkan angka pengurangannya.
- `resources/js/Pages/CurrentMonitoring/Index.vue` berisi tab "Beban & Arus Penyulang" / "Arus Tiap Fasa".

---

## 2 — kWh Produksi: ENGINE / PENYULANG

**Sumber:** PDF halaman 7. Kolomnya berbeda dari tabel lama (`kwh_ps`, `kwh_digital_1/2`), jadi dibuat 2 tabel baru.

### ENGINE (`kwh_engine_logs`, unik per tanggal + engine)
| Kolom PDF | Field |
|-----------|-------|
| Engine (choice: GMT #3, CAT #4, CAT #5, ABC #6) | `engine` |
| Stand Akhir kWh Produksi — Akhir / EDMI MK10 | `stand_akhir`, `stand_edmi_mk10` |
| Stand Akhir kWh PS | `stand_kwh_ps` |
| Flowmeter In / Out | `flowmeter_in`, `flowmeter_out` |

**Produksi harian** = `stand_akhir` hari itu − `stand_akhir` pencatatan sebelumnya untuk engine yang sama (`KwhEngineLog::withProduction`). Angka ini dipakai grafik kWh dan KPI dashboard.

### PENYULANG (`kwh_feeder_logs`, unik per tanggal + penyulang)
| Kolom PDF | Field |
|-----------|-------|
| Penyulang (choice: Galala 1 (MVTIC #2), Galala 2 (MVTIC #1), Waiheru 1, Waiheru 3, Wayame 1, Wayame 2, Hitu) | `feeder` |
| Stand Akhir: PM 800 (EX/IM), EDMI MK10 (EX/IM) + Nilai | `pm800_ex`, `pm800_im`, `edmi_mk10_ex`, `edmi_mk10_im` |
| kWh PS, kWh Digital | `kwh_ps`, `kwh_digital` |
| PS Total | `ps_total` = kWh PS + kWh Digital (otomatis) |

Stand akhir memakai pola "pilih choice → muncul kotak nilai" (`Components/Shared/ChoiceValueInput.vue`).

**Routes:** `POST/DELETE /monitoring-kwh/engine[/{id}]`, `POST/DELETE /monitoring-kwh/penyulang[/{id}]`

---

## 3 — Monitoring Operasi Engine (modul baru)

**Sumber:** PDF halaman 8 + pesan WA *"habis milih 1 nanti langsung muncul kotak buat isi nilainya"*.
Engine: **CAT #4**, **ABC #6**. Satu baris data = tanggal + jam + engine. Filter halaman per tanggal.

### Control Panel (`engine_control_panel_logs`)
KW · Cos φ · Freq (Hz) · Generator Ampere R/S/T · Generator Voltage RS/ST/TR · Generator Bearing Temp DE/OD (°C) · Exiter V/A · Generator Winding Temp R/S/T (°C) · kWh Produksi Utama 1/2

### Engine Area (`engine_area_logs`)
- **Diisi langsung:** Turbo Speed R/L, Air Inlet Restriction (RH)
- **Dibuat choice (pilih → isi nilai):**

| Grup | Pilihan |
|------|---------|
| Temperature (°C) | Oil, Engine Coolant, Manifold Air, Turbo Exhaust L, Exhaust Stack L, Turbo Exhaust R, Exhaust Stack R |
| Pressure (PSI) | Fuel, Oil Filter, Fuel Filter, Air Inlet Manifold, Exhaust Stack L, Turbo Exhaust R, Exhaust Stack R |
| Cylinder Head Temp (°C) | No 1 – No 12 |
| Jacket Water Rad (°C) | In Temperature (Eng Out), Out Temperature (Eng In) |
| AC/OC Water Rad (°C) | In Temperature (Eng Out), Out Temperature (Eng In) |
| Flowmeter | In, Out |

Daftar field dan pilihan didefinisikan sekali di model (`EngineControlPanelLog::FIELD_GROUPS`, `EngineAreaLog::FIXED_FIELDS` / `CHOICE_GROUPS`). Validasi backend dan tampilan frontend sama-sama membaca daftar ini, jadi untuk menambah pilihan cukup menambah 1 kolom di migration dan 1 baris di model.

**Routes:** `GET /monitoring-operasi-engine`, `POST/DELETE /monitoring-operasi-engine/control-panel[/{id}]`, `POST/DELETE /monitoring-operasi-engine/engine-area[/{id}]`

**Permission baru:** `monitoring_engine.view`, `monitoring_engine.input`. Keduanya ditambahkan lewat migration data `2026_09_24_000005_...` untuk user yang sudah ada, dan juga di `UserSeeder` untuk instalasi baru.

| Role | Izin |
|------|------|
| Admin | semua |
| Manager, TL Pemeliharaan | view |
| TL Operasi, Operator | view + input |

---

## Hasil Verifikasi

Uji dijalankan di database dev dalam satu transaksi yang di-rollback, sehingga tidak ada data yang tertinggal.

| Skenario | Hasil |
|----------|-------|
| Produksi engine: stand 1000 → 1250,5 | 250,5 ✅ |
| PS Total: 12,5 + 7,5 | 20 ✅ |
| Dashboard kWh hari ini | 250,5 ✅ |
| Arus Waiheru 1 = 100 → fasa R/S/T | 98 / 95 / 100 ✅ |
| Simpan Control Panel & Engine Area, update baris yang sama | ✅ |
| Validasi engine tidak dikenal, Cos φ > 1 | ditolak ✅ |
| Halaman kWh / Arus / Operasi Engine / Dashboard | HTTP 200 ✅ |
| `npm run build` | sukses ✅ |

Tampilan UI di browser belum dicek manual karena butuh login.

---

## Pertanyaan untuk Pemilik Dokumen

1. **Control Panel:** di PDF ada dua kolom "KWH PRODUKSI UTAMA" dengan judul sama. Untuk sementara dibuat "Utama 1" dan "Utama 2". Apakah salah satunya seharusnya kolom lain (misalnya kWh PS)?
2. **Daftar engine berbeda:** kWh ENGINE memakai 4 engine (GMT #3, CAT #4, CAT #5, ABC #6), sedangkan Operasi Engine hanya 2 (CAT #4, ABC #6). Implementasi mengikuti PDF. Apakah memang begitu?
3. **PS Total** diasumsikan = kWh PS + kWh Digital. Benar?
4. **Produksi engine harian** diasumsikan = selisih stand akhir. Benar?
5. ~~Nilai fasa bisa negatif?~~ **Diputuskan:** ditampilkan apa adanya tanpa batas minimal (misalnya Hitu 3 A → R = −1, S = −1, T = 3).

---

## Catatan

- Tabel lama `kwh_production_logs` tidak dihapus dan masih berisi 1 baris (03/08/2026). Model dan tampilannya sudah dilepas.
- Kalau `operator_name` dikosongkan pada form baru, yang tercatat adalah nama akun yang login.

## Tech Debt (belum dikerjakan, di luar lingkup PA 2026)

| # | Masalah | Dampak | File |
|---|---------|--------|------|
| 1 | Middleware PBAC tidak dipasang di route | Semua user login bisa akses semua endpoint | `routes/web.php` |
| 2 | `is_active` tidak dicek per request | User nonaktif tetap bisa memakai sesi aktif | `bootstrap/app.php` |
| 3 | `operator_name` di modul lama diambil dari input form | Audit trail bisa diisi nama orang lain | controller lama |
| 4 | `$validated['operator_name'] ?: ...` error jika key tidak dikirim | Warning / HTTP 500 | `CurrentMonitoringController`, `FuelStockController` |
| 5 | N+1 query `hasPermission()` / `getPermissionSlugs()` | Lambat di `/users` | `User.php` |
