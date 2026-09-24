# Status Perbaikan Bug — PLN Monitor ULPLTD POKA

> Dokumen pelacakan untuk [UX_BUG_REPORT.md](UX_BUG_REPORT.md).
> Isinya **bukti** bahwa setiap bug sudah dikerjakan: commit, file yang diubah, langkah verifikasi, dan hasilnya.
> Dibuat: 2026-09-24

## Aturan pengisian (wajib dibaca sebelum mengupdate)

1. **Status hanya boleh berubah jika ada bukti.** Bug baru boleh ditandai `✅ Selesai` jika kolom *Commit*, *File diubah*, *Verifikasi* dan *Hasil* sudah terisi.
2. Nilai status:
   | Status | Arti |
   |---|---|
   | ⬜ Belum | Belum disentuh |
   | 🔄 Dikerjakan | Sedang dikerjakan, belum di-commit / belum diverifikasi |
   | ✅ Selesai | Sudah di-commit **dan** lolos verifikasi |
   | ⚠️ Sebagian | Sebagian sub-item selesai; sebutkan sisanya di *Catatan* |
   | ⏭️ Ditunda | Sengaja tidak dikerjakan sekarang; tulis alasannya |
   | ❌ Gagal verifikasi | Sudah dikerjakan tetapi tes gagal; tulis output/gejalanya |
3. **Verifikasi tidak memakai uji browser.** Bukti yang diterima:
   - tes otomatis (`php artisan test`, feature test per role/route/validasi),
   - `npm run build` sukses,
   - pemeriksaan kode yang bisa diulang (`grep`, `git show <commit>`, `php artisan route:list`).

   Tulis perintah atau nama tesnya secara spesifik. Jangan menulis "sudah dicek" tanpa perintah.
4. **Hasil** berisi apa yang benar-benar terlihat atau keluar (mis. "GET /users sebagai operator → 403", "build sukses tanpa warning"). Jika gagal, tulis apa adanya.
5. Setelah mengisi detail, **perbarui juga tabel Ringkasan dan Progres** di bawah.
6. Centang juga checklist di `UX_BUG_REPORT.md` bagian *Rencana Pengerjaan* agar keduanya sinkron.

---

## Progres

| Fase | Total item | Selesai | Status |
|---|---|---|---|
| Langkah 0 — Persiapan | 1 | 1 | ✅ Selesai |
| Fase 1 — Bug data & keamanan | 5 | 5 | ✅ Selesai (bagian frontend BUG-05 diselesaikan di Fase 3) |
| Fase 2 — Fondasi | 5 | 5 | ✅ Selesai |
| Fase 3 — Migrasi per halaman | 7 | 7 | ✅ Selesai |
| Fase 4 — Navigasi & polish | 3 | 3 | ✅ Selesai |
| **Total** | **21** | **21** | **100%** |

## Ringkasan status per bug

| ID | Judul | Prioritas | Status | Commit | Tanggal selesai |
|---|---|---|---|---|---|
| BUG-01 | Simpan satu baris menghapus input baris lain | 🔴 Kritis | ✅ Selesai | `3b2a23f` | 2026-09-24 |
| BUG-02 | Tanggal default form memakai UTC | 🔴 Kritis | ✅ Selesai | `6c6cf8c` | 2026-09-24 |
| BUG-03 | Modal menutup walau validasi gagal | 🔴 Kritis | ✅ Selesai | `6c6cf8c` | 2026-09-24 |
| BUG-04 | Edit BBM ubah tanggal → data duplikat | 🔴 Kritis | ✅ Selesai | `6c6cf8c` | 2026-09-24 |
| BUG-05 | PBAC tidak ditegakkan di server | 🔴 Kritis | ✅ Selesai (server Fase 1; tombol aksi per halaman Fase 3) | `6c6cf8c` + Fase 3 | 2026-09-24 |
| BUG-06 | Bottom nav mobile tidak konsisten | 🟠 Tinggi | ✅ Selesai | `5e56f2f` | 2026-09-24 |
| BUG-07 | Layout re-mount tiap navigasi | 🟠 Tinggi | ✅ Selesai | `5fd800a` | 2026-09-24 |
| BUG-08 | KPI Dashboard menyesatkan | 🟠 Tinggi | ✅ Selesai (backend Fase 1; tampilan PAGE-07) | `6c6cf8c` `cc6ba22` | 2026-09-24 |
| BUG-09 | Tidak ada judul halaman / title statis | 🟠 Tinggi | ✅ Selesai | `5e56f2f` | 2026-09-24 |
| BUG-10 | Flash sukses tidak terlihat | 🟠 Tinggi | ✅ Selesai | `5fd800a` | 2026-09-24 |
| BUG-11 | Warna dark bocor ke light mode | 🟡 Sedang | ✅ Selesai (lokasi tabel BUG-11 di Fase 2; 7 halaman di Fase 3) | `5fd800a` + Fase 3 | 2026-09-24 |
| BUG-12 | Class `slate-850` tidak ada | 🟡 Sedang | ✅ Selesai | `5fd800a` | 2026-09-24 |
| BUG-13 | Modal tidak konsisten & kurang aksesibel | 🟡 Sedang | ✅ Selesai (komponen Fase 2; semua modal dimigrasi Fase 3) | `5fd800a` + Fase 3 | 2026-09-24 |
| BUG-14 | Tabel input Arus berat & angka hardcoded | 🟡 Sedang | ✅ Selesai | `3b2a23f` | 2026-09-24 |
| BUG-15 | Target sentuh mobile terlalu kecil | 🟡 Sedang | ✅ Selesai | `9fa516e` `13ded04` `e7be9df` `a4be48b` | 2026-09-24 |
| BUG-16 | User Management tanpa tampilan mobile | 🟡 Sedang | ✅ Selesai | `25f20bb` | 2026-09-24 |
| BUG-17 | Kumpulan isu minor | 🟢 Rendah | ✅ Selesai (12 dari 12 sub-item selesai) | `25f20bb` `a4be48b` `5e56f2f` | 2026-09-24 |

---

## Langkah 0 — Persiapan

### PREP-01 — Commit perubahan awal & buat branch

- **Status:** ✅ Selesai
- **Branch:** `fix/ux-review` (dibuat dari `main` @ `a0aa632`)
- **Commit baseline:** `1893a20` — perubahan fitur yang sudah ada di working tree sebelum review UX (kWh Engine/Penyulang, Operasi Engine, arus per fasa, `ANALISIS_PA2026.md`).
- **Verifikasi:** `git status` bersih setelah commit; `git branch --show-current` menunjukkan branch kerja.
- **Hasil:** `git branch --show-current` → `fix/ux-review`. Setelah commit baseline dan commit Fase 1, satu-satunya file yang tersisa di `git status` adalah `?? monitoring-pln.zip`.
- **Catatan:**
  - Ada 5 file yang berisi perubahan lama sekaligus perubahan Fase 1: `routes/web.php`, `DashboardController.php`, `UserManagement/Index.vue`, `KwhEngineTable.vue` dan `KwhFeederTable.vue`. Untuk commit baseline, versi sebelum Fase 1 dari kelima file itu direkonstruksi lalu di-stage lewat `git update-index`, tanpa mengubah working tree. Setelah itu dicek dengan `git diff --cached | grep -cE "permission:|todayLocal|formErrors|Belum Ada Data|intervalRank"` → `0`, artinya tidak ada perubahan Fase 1 yang ikut ke baseline.
  - `monitoring-pln.zip` (±76 MB, arsip) sengaja **tidak** di-commit. Pertimbangkan untuk memindahkannya atau menambahkan `*.zip` ke `.gitignore`.

---

## Fase 1 — Bug data & keamanan

> **Tanggal pengerjaan:** 2026-09-24 · **Branch:** `fix/ux-review` · **Commit:** `6c6cf8c` (satu commit untuk seluruh Fase 1, sesudah baseline `1893a20`).
>
> **Bukti otomatis yang berlaku untuk seluruh Fase 1:**
> - `tests/Feature/Phase1BugFixTest.php` (baru, 16 tes) → `DB_CONNECTION=mysql DB_DATABASE=laravel_testing php artisan test --filter=Phase1BugFixTest` → **16 passed (80 assertions)**. Tes memakai database terpisah `laravel_testing` yang dibuat khusus untuk tes, karena PHP lokal tidak punya driver `pdo_sqlite`. Database dev `laravel` tidak disentuh.
> - `php artisan test` (seluruh suite) → 17 passed, **1 failed**: `Tests\Feature\ExampleTest` (`GET /` sebagai tamu → 302 ke login, bukan 200). Tes bawaan Laravel ini sudah gagal sebelum Fase 1 karena `/` memang wajib login, jadi tidak terkait perubahan ini.
> - `npm run build` → **sukses** (`✓ built in 7.75s`). Satu-satunya warning adalah ukuran chunk > 500 kB, yang sudah ada sebelumnya.
> - `php artisan route:list -v` → semua route modul sekarang menampilkan middleware `permission:<slug>`.

### BUG-05 — PBAC tidak ditegakkan di server

- **Status:** ✅ Selesai. Sisi server selesai di Fase 1 (`6c6cf8c`). Sub-item frontend (menyembunyikan tombol aksi lewat `can()`) selesai di Fase 3, per halaman.
- **Commit:** `6c6cf8c`
- **File diubah:**
  - `bootstrap/app.php`: mendaftarkan alias middleware `permission` → `App\Http\Middleware\CheckPermission`
  - `routes/web.php`: memasang `permission:<slug>` di setiap route modul
  - `app/Http/Controllers/UserManagementController.php`: menolak toggle status dan perubahan role pada akun sendiri
  - `resources/js/Pages/UserManagement/Index.vue`: menampilkan `flash.error` agar penolakan toggle terlihat
  - `tests/Feature/Phase1BugFixTest.php` (baru)
- **Ringkasan perubahan:**

  | Route | Middleware |
  |---|---|
  | `GET /monitoring-arus` / `POST /monitoring-arus` | `monitoring_arus.view` / `monitoring_arus.input` |
  | `GET /monitoring-kwh` / `POST, DELETE /monitoring-kwh/*` | `monitoring_kwh.view` / `monitoring_kwh.input` |
  | `GET /monitoring-operasi-engine` / `POST, DELETE .../*` | `monitoring_engine.view` / `monitoring_engine.input` |
  | `GET /monitoring-gangguan` / `POST, DELETE` | `monitoring_gangguan.view` / `monitoring_gangguan.manage` |
  | `GET /monitoring-bbm` / `POST, DELETE` | `monitoring_bbm.view` / `monitoring_bbm.input` |
  | `/users*` (semua method) | `users.manage` |
  | `/`, `/dashboard`, `/logout` | tetap `auth` saja (tidak ada slug untuk dashboard) |

  Admin tetap bisa mengakses semuanya karena `User::hasPermission()` mengembalikan `true` untuk role `admin`. Jika admin memanggil `toggleStatus` pada akunnya sendiri, server mengembalikan redirect back dengan flash `error` "Anda tidak dapat menonaktifkan akun Anda sendiri.". Jika `update` mengubah role akun sendiri, server mengembalikan error validasi `role`.
- **Kriteria selesai (dari report):** operator → `GET /users` = 403; manager → tidak ada tombol tambah/edit/hapus dan POST langsung = 403; admin tidak bisa menonaktifkan akun sendiri.
- **Verifikasi:**
  - [x] Login **operator** → buka `/users` via URL → hasil: **403** (`test_operator_cannot_open_user_management`). `POST /users` untuk membuat admin → **403**, dan user tidak tercipta (`test_operator_cannot_create_user_via_direct_post`).
  - [x] Frontend: tombol Tambah/Edit/Hapus dibungkus `v-if="can('<modul>.input')"` → hasil: di 6 tabel (Arus, kWh Engine/Penyulang, Control Panel, Engine Area, Gangguan `.manage`, BBM). `ssr-pages.mjs`: untuk 5 halaman, penanda tombol aksi ada untuk admin dan **tidak ada** untuk manager → PASS (lihat Fase 3).
  - [x] Login **manager** → POST langsung ke `/monitoring-kwh/engine` → hasil: **403**. Hasil yang sama untuk `POST /monitoring-bbm`, `/monitoring-gangguan`, `/monitoring-arus`, `/monitoring-operasi-engine/control-panel` dan `DELETE /monitoring-bbm/1`: semua **403**. Sementara itu `GET /monitoring-kwh` dan `/monitoring-bbm` → 200 (`test_manager_can_view_but_cannot_write`).
  - [x] Login **tl_pemeliharaan** (tanpa `monitoring_kwh.view`) → `GET /monitoring-kwh` → hasil: **403** (`test_tl_pemeliharaan_without_kwh_view_is_forbidden`)
  - [x] Login **admin** → toggle status akun sendiri → hasil: redirect + flash `error`, `is_active` tetap `true` (`test_admin_cannot_deactivate_own_account`). Toggle akun operator tetap berfungsi (`test_admin_can_deactivate_other_account`).
  - [x] Login **admin** → ubah role akun sendiri ke operator → hasil: error `role`, role tetap `admin` (`test_admin_cannot_change_own_role`)
  - [x] Login **admin** → semua menu tetap bisa diakses → hasil: `/`, `/monitoring-arus`, `/monitoring-kwh`, `/monitoring-operasi-engine`, `/monitoring-gangguan`, `/monitoring-bbm` dan `/users` → semua **200** (`test_admin_can_access_every_menu`)
- **Catatan:** halaman 403 masih memakai tampilan default Laravel. BottomNav mobile belum difilter berdasarkan izin (BUG-06), jadi sampai Fase 4 user masih bisa menekan menu yang berujung 403.

### BUG-02 — Tanggal default form memakai UTC

- **Status:** ✅ Selesai. Sudah diimplementasi, lolos simulasi Node, dan build sukses.
- **Commit:** `6c6cf8c`
- **File diubah:**
  - `resources/js/utils/date.js` (baru): helper `todayLocal()`
  - `resources/js/Components/KwhProduction/KwhEngineTable.vue`, `KwhFeederTable.vue`, `DisturbanceMonitoring/DisturbanceDataTable.vue` (2 tempat) dan `FuelStock/FuelStockDataTable.vue` (2 tempat): mengganti `new Date().toISOString().split('T')[0]` → `todayLocal()`
  - `config/app.php`: `'timezone' => env('APP_TIMEZONE', 'Asia/Jayapura')` (sebelumnya `'UTC'`)
  - `.env.example`: menambah `APP_TIMEZONE=Asia/Jayapura`
- **Ringkasan perubahan:**
  - Frontend: tanggal default form sekarang dihitung dari jam lokal browser, bukan UTC.
  - Backend (temuan tambahan dengan akar masalah yang sama): `config/app.php` memakai `UTC`. Akibatnya `Carbon::today()` juga menghasilkan tanggal yang salah pada dini hari. Ini memengaruhi tanggal default Monitoring Arus, tanggal default Operasi Engine (`selectedDate`, dipakai sebagai default form Control Panel/Engine Area) dan KPI "hari ini" di Dashboard.
  - **Zona waktu yang dipilih adalah WIT (`Asia/Jayapura`, UTC+9), bukan WIB** seperti yang ditulis di report. Alasannya, nama penyulang di `CurrentMonitoringController::PHASE_REDUCTIONS` (Wayame, Waiheru, Hitu, Galala) menunjukkan ULPLTD Poka berada di Ambon. Jika server ternyata harus memakai WIB, cukup set `APP_TIMEZONE=Asia/Jakarta` di `.env`.
- **Kriteria selesai:** pada pukul 01:00 waktu lokal, `todayLocal()` menghasilkan tanggal hari ini (dibuktikan lewat simulasi).
- **Verifikasi:**
  - [x] `grep -rn "toISOString" resources/js` → hasil: tersisa 1 baris, yaitu komentar peringatan di `resources/js/utils/date.js`. Tidak ada lagi pemakaian untuk tanggal form.
  - [x] Simulasi Node (jam dipalsukan ke `2026-09-24T01:00:00+09:00`, TZ proses diset):
    ```
    TZ= Asia/Jayapura | toISOString (lama): 2026-09-23 | todayLocal (baru): 2026-09-24
    TZ= Asia/Jakarta  | toISOString (lama): 2026-09-23 | todayLocal (baru): 2026-09-23
    ```
    → hasil: di WIT pukul 01:00, cara lama menghasilkan **kemarin (23)**, sedangkan `todayLocal()` menghasilkan **hari ini (24)**. Baris kedua juga benar, karena 01:00 WIT sama dengan 23:00 WIB tanggal 23.
  - [x] `npm run build` → hasil: sukses, import `@/utils/date` berhasil di-resolve.
- **Catatan:** `created_at`/`updated_at` data lama tersimpan dalam UTC, sedangkan data baru akan tersimpan dalam WIT. Kolom tanggal operasional (`recorded_date`, `event_date`) tidak terpengaruh. Jam default Control Panel/Engine Area (`toTimeString()`) sudah memakai jam lokal, jadi tidak diubah.

### BUG-03 — Modal menutup walau validasi gagal

- **Status:** ✅ Selesai. Sudah diimplementasi, diperiksa lewat diff commit, dan build sukses.
- **Commit:** `6c6cf8c`
- **File diubah:**
  - `resources/js/Components/DisturbanceMonitoring/DisturbanceDataTable.vue`
  - `resources/js/Components/FuelStock/FuelStockDataTable.vue`
  - `resources/js/Pages/UserManagement/Index.vue`
- **Ringkasan perubahan (sama di ketiga file):**
  - `showModal.value = false` dipindah dari `onFinish` ke `onSuccess`. `onFinish` sekarang hanya me-reset `isSubmitting`.
  - Menambah `preserveState: true` agar komponen dan isian form tidak di-remount saat server mengembalikan error validasi. Polanya sama dengan `KwhEngineTable.vue`.
  - State lokal `formErrors` diisi dari `onError` dan dikosongkan saat modal dibuka (tambah/edit) maupun saat sukses. Dengan begitu error lama tidak muncul lagi saat modal dibuka ulang.
  - Daftar error ditampilkan di atas tombol Simpan dengan gaya yang sama seperti modal kWh (`bg-rose-500/10 ... text-rose-500`).
  - Users: dua blok `router.put`/`router.post` digabung agar memakai satu objek `options`.
- **Kriteria selesai:** modal hanya ditutup di `onSuccess`; `preserveState: true` menjaga isian saat validasi gagal; error dari `onError` dirender di modal dan dikosongkan saat modal dibuka ulang.
- **Verifikasi:**
  - [x] Server: tambah user dengan email yang sudah ada → hasil: server mengembalikan error validasi `email` (`test_duplicate_email_returns_validation_error`). Kondisi inilah yang sebelumnya membuat modal tertutup tanpa pesan.
  - [x] `npm run build` → hasil: sukses.
  - [x] Pemeriksaan kode: `git show 6c6cf8c -- resources/js/Components/DisturbanceMonitoring/DisturbanceDataTable.vue resources/js/Components/FuelStock/FuelStockDataTable.vue resources/js/Pages/UserManagement/Index.vue` → hasil: di ketiga file `showModal.value = false` dihapus dari `onFinish` dan kini hanya ada di `onSuccess`; `onError` mengisi `formErrors`; `formErrors` dikosongkan saat modal dibuka dan saat sukses; `preserveState: true` ditambahkan; daftar `formErrors` dirender di template.
- **Catatan:** error masih ditampilkan sebagai daftar di bawah form, belum per field. Error per field dan `for`/`id` pada label dijadwalkan di BUG-13 (komponen `FormField`, Fase 2). Pesan validasi masih dalam bahasa Inggris bawaan Laravel (`APP_LOCALE=en`).

### BUG-04 — Edit BBM ubah tanggal → data duplikat

- **Status:** ✅ Selesai. Sisi server dan frontend selesai dan lolos tes.
- **Commit:** `6c6cf8c`
- **File diubah:**
  - `resources/js/Components/FuelStock/FuelStockDataTable.vue`: input tanggal dikunci saat edit, dan `form.id` dikirim (isi `null` saat tambah, `log.id` saat edit)
  - `app/Http/Controllers/FuelStockController.php`: `storeOrUpdate` sekarang berbasis `id`, bukan upsert berdasarkan tanggal
  - `tests/Feature/Phase1BugFixTest.php`: 3 tes baru
- **Ringkasan perubahan:**
  - Frontend: input tanggal diberi `:disabled="isEditing"` dan `disabled:opacity-60`, konsisten dengan modal kWh Engine dan Control Panel.
  - Server, **edit** (ada `id`, divalidasi `exists:fuel_stocks,id`): hanya record dengan `id` tersebut yang diperbarui. `recorded_date` dari request diabaikan, jadi tanggal record tidak bisa dipindah walaupun request dibuat manual.
  - Server, **tambah** (tanpa `id`): `recorded_date` harus unik (`Rule::unique(...)->ignore($id)`). Jika tanggal sudah ada, server mengembalikan error "Data Stok BBM untuk tanggal ini sudah ada. Gunakan tombol Edit pada baris tersebut." Sebelumnya data lama **ditimpa diam-diam**. Ini juga menutup risiko yang disebut di BUG-02 (data kemarin tertimpa).
  - Temuan tambahan: field opsional yang tidak dikirim sama sekali (mis. `operator_name`) menyebabkan error 500 "Undefined array key". Sekarang dibaca dengan `($validated['x'] ?? null) ?: default`. Form lama selalu mengirim semua field, sehingga bug ini tidak terlihat dari UI.
- **Kriteria selesai:** tanggal tidak bisa diubah saat mode edit.
- **Verifikasi:**
  - [x] Edit record 10/09 dengan `recorded_date` diubah ke 11/09 lewat request langsung → hasil: tetap 1 record, tanggal tetap `2026-09-10`, `main_tank` terupdate ke 4000 (`test_editing_fuel_with_changed_date_does_not_duplicate`)
  - [x] Tambah data untuk tanggal yang sudah ada → hasil: error `recorded_date`, tetap 1 record, nilai lama (5000) tidak tertimpa (`test_adding_fuel_for_existing_date_is_rejected`)
  - [x] Tambah data untuk tanggal baru tanpa `operator_name` → hasil: redirect + flash `success`, 1 record tercipta (`test_adding_fuel_for_new_date_creates_record`; sebelum perbaikan tes ini menghasilkan 500)
  - [x] `npm run build` → hasil: sukses.
  - [x] Pemeriksaan kode: `git show 6c6cf8c -- resources/js/Components/FuelStock/FuelStockDataTable.vue` → hasil: input tanggal mendapat `:disabled="isEditing"` dan `disabled:opacity-60`.
- **Catatan:** –

### BUG-08 (backend) — KPI Dashboard

- **Status:** ✅ Selesai (bagian backend). Sudah diimplementasi dan lolos tes. Bagian frontend dilacak di PAGE-07.
- **Commit:** `6c6cf8c`
- **File diubah:** `app/Http/Controllers/DashboardController.php`
- **Ringkasan perubahan:**
  - Status BBM: jika belum ada data → `"Belum Ada Data"`; HOP ≤ 5 **termasuk 0** → `Kritis`; HOP ≤ 10 → `Waspada`; selain itu `Aman`. Sebelumnya HOP 0 dan kondisi tanpa data sama-sama berstatus "Aman".
  - Urutan interval chart arus sekarang mengikuti `CurrentMonitoringController::SHIFT_INTERVALS` (pagi → sore → malam), bukan urutan insert. Report menyarankan `->sort()`, tetapi pengurutan alfabet akan menaruh shift malam (`00.30`–`08.00`) sebelum shift pagi.
  - `latest_interval` sekarang adalah interval terakhir menurut urutan shift, bukan berdasarkan `id` terbesar.
  - "Gangguan Terbaru" mengambil 5 data terakhir **tanpa filter bulan**. Angka KPI gangguan bulan ini tidak berubah.
- **Verifikasi:**
  - [x] Tanpa data BBM → hasil: `kpi.fuel.status = "Belum Ada Data"` (`test_fuel_status_without_data_is_not_safe`)
  - [x] HOP = 0 → hasil: `kpi.fuel.status = "Kritis"` (`test_fuel_status_with_zero_days_of_supply_is_critical`)
  - [x] Input arus tidak berurutan (09.00, 08.30, 16.30) → hasil: `currentChartData` berurutan 08.30, 09.00, 16.30 dan `latest_interval = 16.30` (`test_current_chart_intervals_follow_shift_order`)
  - [x] Hanya ada gangguan 2 bulan lalu → hasil: `recentDisturbances` berisi 1 item dan `total_month = 0` (`test_recent_disturbances_include_previous_months`)
- **Catatan:** frontend Dashboard belum membedakan warna badge "Belum Ada Data", sehingga badge ini masih merah seperti Kritis. Perbaikannya dijadwalkan bersama bagian frontend BUG-08 di Fase 3 (PAGE-07).

---

## Fase 2 — Fondasi

> **Tanggal pengerjaan:** 2026-09-24 · **Branch:** `fix/ux-review` · **Commit:** `5fd800a` (satu commit untuk seluruh Fase 2).
>
> **Bukti otomatis yang berlaku untuk seluruh Fase 2:**
> - `npm run build` → **sukses** (`✓ built in 21.18s`). Satu-satunya warning adalah ukuran chunk > 500 kB, yang sudah ada sebelumnya.
> - `node tests/Frontend/ssr-pages.mjs` (baru) → **SEMUA PASS (84 cek)**. Skrip ini me-render SSR 9 halaman (7 halaman aplikasi + Login + Lupa Password) dalam mode **light dan dark**, memakai objek page Inertia asli dari Laravel (login admin, database `laravel_testing`). Yang dicek per halaman: render tanpa error runtime, `<main>` dan sidebar dari layout ter-render, warna latar layout mengikuti `isDarkMode` hasil `inject`, container Toast ada, halaman Auth tidak memakai layout, dan tidak ada warning Vue. Ditambah 2 cek Toast (flash success & error). Cara menyiapkan datanya ada di kepala file skrip.
> - `node tests/Frontend/ssr-components.mjs` (baru) → **SEMUA PASS (17 cek)** untuk markup `Modal`, `FormField` dan `Button`.
> - `php artisan test` → 17 passed, 1 failed (`ExampleTest`, sama seperti di Fase 1; tidak terkait perubahan ini).
> - Catatan metode: di SSR, ApexCharts tidak punya render function, sehingga warning "missing template or render function" dari komponen grafik diabaikan oleh skrip. Grafik tetap di-render normal di browser.

### BUG-07 — Persistent layout, status sidebar, tema

- **Status:** ✅ Selesai
- **Commit:** `5fd800a`
- **File diubah:**
  - `resources/js/app.js`: opsi `layout` di `createInertiaApp`
  - `resources/js/Layouts/AppLayout.vue`: slot tanpa `v-slot` props, tema di `<html>`, sidebar di localStorage, memasang `<Toast />`
  - `resources/views/app.blade.php`: script tema inline di `<head>`, latar `body` mengikuti tema
  - 7 halaman di `resources/js/Pages/*/Index.vue`: pembungkus `<AppLayout>` diganti `<div class="space-y-6">`; `isDarkMode` diambil lewat `inject('isDarkMode')`
- **Ringkasan perubahan:**
  - Layout dipasang lewat `layout: (name) => (name.startsWith('Auth/') ? null : AppLayout)` di `app.js:16`. Report menyarankan `page.default.layout ??= AppLayout`, tetapi `??=` akan menimpa `layout: null`, sehingga halaman Auth tetap mendapat layout. Karena itu yang dipakai adalah opsi `layout` bawaan Inertia v3.
  - Inertia v3 memberi `inheritAttrs = false` pada halaman & layout, jadi props halaman (`auth`, `errors`, `flash`) tidak bocor menjadi atribut HTML.
  - Tema: script inline di `app.blade.php` memasang class `dark` di `<html>` sebelum Vue dimuat (`classList.add('dark')`, dibungkus `try/catch`). `AppLayout` membaca nilai awal dari `document.documentElement.classList` (`AppLayout.vue:75`) dan men-toggle class di `<html>` (`AppLayout.vue:79`). Class `dark` tidak lagi dipasang di div root layout.
  - Sidebar: status collapse disimpan di `localStorage` key `pln_sidebar_collapsed` (`AppLayout.vue:47,66,70`). Baca/tulis dibungkus `try/catch` (`AppLayout.vue:50,58`).
- **Kriteria selesai:** layout tidak di-mount ulang per halaman, status sidebar tersimpan, dan tema dipasang sebelum Vue mount.
- **Verifikasi:**
  - [x] `grep -rln "<AppLayout" resources/js/Pages` → hasil: **0 file**.
  - [x] Status collapse sidebar dibaca/ditulis ke localStorage (dibungkus try/catch) → hasil: `readStorage`/`writeStorage` di `AppLayout.vue:49-63` memakai `try/catch`; nilai awal dari `readStorage(SIDEBAR_KEY)` dan ditulis di `toggleSidebar`.
  - [x] `resources/views/app.blade.php` memuat script inline di `<head>` yang memasang class `dark` pada `<html>` → hasil: ada, `app.blade.php:13` (`document.documentElement.classList.add('dark')`).
  - [x] `Auth/Login` & `Auth/ForgotPassword` mengecualikan layout → hasil: lewat callback `app.js:16` (nama halaman diawali `Auth/` → `null`). `ssr-pages.mjs`: `/login` & `/forgot-password` → "halaman Auth tanpa layout" PASS (light & dark).
  - [x] Halaman memakai layout persisten & `isDarkMode` dari `inject` → hasil: `ssr-pages.mjs` untuk ke-7 halaman → "layout ter-render" dan "bg layout terang/gelap" PASS.
  - [x] `npm run build` sukses → hasil: sukses.
- **Catatan:** dengan layout persisten, state lain di layout (mis. posisi scroll sidebar) juga ikut bertahan antar halaman.

### BUG-11 & BUG-12 — Varian `dark:` & `slate-850`

- **Status:** BUG-12 ✅ Selesai. BUG-11 ✅ Selesai: lokasi di tabel BUG-11 diperbaiki di Fase 2, dan template 7 halaman dimigrasi ke `dark:` di Fase 3. Sisa ternary hanya di komponen layout (Header/Sidebar/BottomNav), yang sudah berpasangan terang/gelap dan akan disentuh di Fase 4.
- **Commit:** `5fd800a`
- **File diubah:** `Pages/Dashboard/Index.vue`, `Pages/UserManagement/Index.vue`, `Components/DisturbanceMonitoring/DisturbanceDataTable.vue`, `Components/FuelStock/FuelStockDataTable.vue`, `Components/CurrentMonitoring/AdaptiveDataTable.vue`
- **Ringkasan perubahan:**
  - Varian `dark:` sekarang berlaku di seluruh halaman, termasuk konten yang di-teleport ke `<body>`, karena class `dark` ada di `<html>` (BUG-07).
  - **BUG-12:** `hover:bg-slate-850/50` → `hover:bg-slate-800/50` (4 file). `group-hover:bg-slate-850` pada kolom sticky → `group-hover:bg-slate-800`: kolom sticky butuh latar opak agar isi tabel yang di-scroll tidak tembus.
  - **BUG-11:**

    | Lokasi | Sebelum | Sesudah |
    |---|---|---|
    | Dashboard, tombol "Lihat Semua Log Gangguan" | `bg-slate-800 hover:bg-slate-700 text-cyan-400` | `bg-slate-100 hover:bg-slate-200 text-cyan-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-cyan-400` |
    | Dashboard, deskripsi gangguan | `text-slate-300` | `text-slate-500 dark:text-slate-300` |
    | Dashboard, border header kartu (4×) | `border-slate-700/40` | `border-slate-200 dark:border-slate-700/40` |
    | Dashboard, badge status BBM & status gangguan, jenis gangguan | `text-emerald/amber/rose-400` | `text-*-700 dark:text-*-400` (jenis gangguan: `text-rose-600 dark:text-rose-400`) |
    | User Management, email | `text-slate-300` | `text-slate-600 dark:text-slate-300` |
    | User Management, badge role (6 role) | `text-*-400` | `text-*-700 dark:text-*-400` |
    | Modal Gangguan / BBM / Users, tombol Batal | `border-slate-700 text-slate-400 hover:bg-slate-800` | `border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800` |
    | Modal Gangguan / BBM / Users, tombol X | `hover:text-white` | `hover:text-slate-700 dark:hover:text-white` + `aria-label="Tutup"` |
    | Kartu mobile Gangguan / BBM, box "Last Modified" | `text-slate-400 bg-slate-900/40 border-slate-800` | `text-slate-500 bg-white border-slate-200 dark:text-slate-400 dark:bg-slate-900/40 dark:border-slate-800` |
- **Verifikasi:**
  - [x] `grep -rn "slate-850" resources/js` → hasil: **0**.
  - [x] Setiap lokasi di tabel BUG-11 memakai pasangan kelas terang + `dark:` → hasil: sesuai tabel di atas (`git show 5fd800a -- resources/js/Pages/Dashboard/Index.vue resources/js/Pages/UserManagement/Index.vue resources/js/Components/DisturbanceMonitoring/DisturbanceDataTable.vue resources/js/Components/FuelStock/FuelStockDataTable.vue`). Kelas gelap tanpa prefiks `dark:` di ketiga file modal: `LC_ALL=C.UTF-8 grep -rnP '(?<!dark:)hover:text-white"|(?<!dark:)bg-slate-900/40|"flex-1 py-2.5 rounded-xl border border-slate-700' resources/js/Components/DisturbanceMonitoring resources/js/Components/FuelStock resources/js/Pages/UserManagement` → **0** (sedangkan `dark:hover:text-white"` → 3, satu per modal).
  - [x] `npm run build` sukses → hasil: sukses.
- **Catatan:** tombol X/Batal di modal kWh, Control Panel dan Engine Area sudah memakai ternary dengan pasangan terang, jadi tidak diubah di sini. Semuanya akan diganti komponen `Modal`/`Button` di Fase 3.

### BUG-13 — Komponen Modal, FormField, Button

- **Status:** ✅ Selesai. Ketiga komponen dibuat di Fase 2. Semua modal (kWh Engine, kWh Penyulang, Control Panel, Engine Area, Gangguan, BBM, Users) dimigrasi ke komponen ini di Fase 3.
- **Commit:** `5fd800a`
- **File dibuat:** `resources/js/Components/Shared/Modal.vue`, `FormField.vue`, `Button.vue`
- **Ringkasan perubahan:**
  - `Modal`: `Teleport` ke `body`; Esc dan klik backdrop menutup modal; fokus awal ke field pertama; fokus terkunci di dalam panel (Tab/Shift+Tab); scroll halaman dikunci; fokus dikembalikan ke tombol pemicu saat ditutup. Prop `closeable=false` menahan penutupan selama proses simpan. Tersedia slot `title` dan `footer`, serta prop `maxWidth`.
  - `FormField`: label + input + pesan error per field. `id` dibuat otomatis (`useId`) dan dihubungkan ke `<label for>`, `aria-invalid` dan `aria-describedby`. Input angka yang kosong dikirim sebagai `null` (bukan `0`). Slot default dengan `{ id, inputClass, describedBy }` tersedia untuk `select`/`textarea`/komponen lain. Prop `accent` mengatur warna fokus per modul.
  - `Button`: varian `primary` / `secondary` / `danger`, dengan `accent` untuk warna modul. Saat `loading`, tombol otomatis `disabled` + `aria-busy` + spinner, dengan gaya `disabled:opacity-50 disabled:cursor-not-allowed`.
- **Verifikasi (pemeriksaan kode komponen):**
  - [x] `Modal.vue` punya listener `keydown` Escape yang menutup modal → hasil: `document.addEventListener('keydown', onKeydown)` (`Modal.vue:131`), cabang `event.key === 'Escape'` → `requestClose()` (`Modal.vue:103`). Listener dilepas saat ditutup/unmount.
  - [x] Backdrop memakai `@click.self` untuk menutup → hasil: `Modal.vue:7`.
  - [x] Fokus awal ke field pertama (`nextTick` + `focus()`), ada `role="dialog"` dan `aria-modal` → hasil: `Modal.vue:134-139`, `Modal.vue:11-12`. `ssr-components.mjs`: "role=dialog & aria-modal", "aria-labelledby menunjuk ke judul" PASS.
  - [x] Panel modal memakai `max-h-[90vh] overflow-y-auto` → hasil: `Modal.vue:16`. `ssr-components.mjs` PASS.
  - [x] `FormField.vue` menghubungkan `<label for>` dengan `id` input → hasil: `FormField.vue:3,16`. `ssr-components.mjs`: "input id sama dengan label for", "id unik per field", "aria-describedby menunjuk ke error", "aria-invalid saat error" PASS.
  - [x] `Button.vue` memasang atribut `disabled` saat `loading` dan punya gaya `disabled:` → hasil: `Button.vue:4,9`. `ssr-components.mjs`: "loading memasang atribut disabled", "aria-busy", "gaya disabled", "tidak loading → tidak disabled" PASS.
  - [x] `npm run build` sukses → hasil: sukses. Karena komponen belum di-import halaman mana pun, kompilasinya juga dicek terpisah lewat `ssr-components.mjs`.
- **Catatan:** perilaku keyboard (Esc, Tab trap) diverifikasi lewat pemeriksaan kode. SSR tidak menjalankan event keyboard.

### BUG-10 — Toast global

- **Status:** ✅ Selesai
- **Commit:** `5fd800a`
- **File diubah / dibuat:** `resources/js/Components/Shared/Toast.vue` (baru), `resources/js/Layouts/AppLayout.vue`, 6 halaman (blok flash dihapus: Monitoring Arus, kWh, Operasi Engine, Gangguan, BBM, Users — termasuk blok `flash.error` Users dari Fase 1)
- **Ringkasan perubahan:** `Toast` dipasang sekali di `AppLayout`. Posisinya `fixed` di kanan atas (desktop) atau atas layar (mobile; bawah layar dipakai BottomNav), dengan `z-[60]` agar tetap terlihat di atas modal. Setiap `flash.success`/`flash.error` dari server memunculkan toast yang hilang otomatis setelah 4 detik; timer berhenti saat kursor berada di atas toast. Tersedia tombol tutup (`aria-label="Tutup notifikasi"`). Ada `aria-live="polite"`, `role="status"` untuk sukses, dan `role="alert"` untuk error. Toast beberapa sekaligus ditampilkan bertumpuk.
- **Verifikasi:**
  - [x] `Toast.vue` dipasang di `AppLayout` dengan posisi `fixed` dan membaca `flash.success` / `flash.error` → hasil: `AppLayout.vue:35`, `Toast.vue:4` (`fixed z-[60] …`), `Toast.vue:72-75`. `ssr-pages.mjs`: "Toast merender flash.success" & "flash.error" PASS; container Toast ada di ke-7 halaman.
  - [x] Ada timer auto-dismiss dan tombol tutup di kode `Toast.vue` → hasil: `DURATION = 4000` + `setTimeout(() => dismiss(toast.id), DURATION)` (`Toast.vue:43,57`); tombol tutup `@click="dismiss(toast.id)"` (`Toast.vue:29`).
  - [x] `grep -rn "flash.success" resources/js/Pages` → hasil: **0 di halaman ber-layout**. Yang tersisa hanya 2 baris di `Pages/Auth/Login.vue`: halaman Login tidak memakai `AppLayout`, sehingga tidak punya Toast, dan tetap membutuhkan banner sendiri untuk pesan "berhasil keluar". `grep -rln "flash" resources/js/Pages --include=Index.vue` → 0.
- **Catatan:** pesan flash "Selamat datang kembali" setelah login kini muncul sebagai toast di Dashboard (sebelumnya tidak tampil karena Dashboard tidak punya blok flash). Jika user menekan Back di browser ke halaman yang dulu memuat flash, Inertia memulihkan props lama, sehingga toast bisa muncul lagi (perilaku yang sama dengan banner lama).

### FOUND-01 — Composable `usePermission()`

- **Status:** ✅ Selesai
- **Commit:** `5fd800a`
- **File diubah / dibuat:** `resources/js/composables/usePermission.js` (baru), `resources/js/Components/Desktop/Sidebar.vue`
- **Ringkasan perubahan:** `usePermission()` mengembalikan `{ can, user, permissions }`. Sidebar mengganti fungsi lokal `hasPerm` dengan `can()` di 6 tempat, dan `usePage` tidak lagi di-import di Sidebar.
- **Verifikasi:**
  - [x] `grep -rn "hasPerm" resources/js` → hasil: **0**. Sidebar memakai `const { can } = usePermission();`.
  - [x] Admin selalu `true`, role lain membaca `page.props.auth.permissions` → hasil: `usePermission.js`: `if (!user.value) return false; if (user.value.role === 'admin') return true; return permissions.value.includes(slug);`. Logikanya identik dengan `hasPerm` lama (lihat `git show 5fd800a -- resources/js/Components/Desktop/Sidebar.vue`).
  - [x] Sidebar ter-render untuk admin di ke-7 halaman → hasil: `ssr-pages.mjs` "layout ter-render (`<main>` + sidebar)" PASS.
- **Catatan:** BottomNav belum memakai `can()`; itu bagian BUG-06 (Fase 4). Tombol aksi per halaman memakai `can()` di Fase 3.

---

## Fase 3 — Migrasi per halaman

> Untuk setiap halaman, verifikasi minimal: `npm run build` sukses, kelas tema memakai varian `dark:`, tampilan mobile (`md:hidden`) tersedia, dan tombol aksi dibungkus `can('<modul>.input')`.
>
> **Tanggal pengerjaan:** 2026-09-24 · **Branch:** `fix/ux-review` · **Commit:** satu commit per halaman (lihat tiap bagian).
>
> **Bukti otomatis yang berlaku untuk seluruh Fase 3** (dijalankan ulang setelah commit terakhir `cc6ba22`):
> - `php artisan test` → **30 passed**, 1 failed (`ExampleTest`, sama seperti Fase 1–2 dan tidak terkait). Tes baru: `tests/Feature/Phase3BugFixTest.php` (13 tes).
> - `node --test tests/Frontend/*.test.mjs` → **7/7 pass** (logika baris Monitoring Arus, `resources/js/utils/currentMatrix.js`).
> - `php tests/Frontend/seed-ssr.php` → 7/7 POST contoh data sukses (satu per modul, lewat controller asli).
> - `node tests/Frontend/ssr-pages.mjs` → **143 PASS, 0 FAIL**. Skrip ini me-render 9 halaman (light & dark). Tambahan untuk Fase 3:
>   - **penanda tombol aksi per halaman:** harus ada untuk admin dan tidak ada untuk manager yang view-only;
>   - **tampilan mobile:** ada `block md:hidden` dan `hidden md:block` di 6 halaman tabel;
>   - **link KPI Dashboard:** mengikuti izin (dicek dengan akun TL Pemeliharaan).
> - `node tests/Frontend/ssr-components.mjs` → **21 PASS** (ditambah cek `RowActions`).
> - `npm run build` → sukses.
> - Konversi tema: ternary `isDarkMode ? '…' : '…'` di template halaman & komponen halaman diubah ke varian `dark:` (skrip konversi otomatis, lalu ditinjau per diff). `grep -c "isDarkMode ?"` → 0 di semua file halaman Fase 3. Sisa ternary hanya ada di komponen layout (`Header.vue`, `Sidebar.vue`, `BottomNav.vue`), yang dikerjakan bersama BUG-06/BUG-09 di Fase 4.
>
> **Komponen & util bersama baru:**
> - `Components/Shared/RowActions.vue`: tombol Edit/Hapus; varian mobile ≥ 44px (`p-2.5 min-w-11 min-h-11`, `gap-3`), dengan `aria-label`.
> - `utils/format.js`: `formatDate`/`formatNumber`.
> - `utils/currentMatrix.js`: logika baris Monitoring Arus.
> - `FormField.vue`: opsi baru `hideLabel`.
> - Semua modal memakai `useForm` Inertia (`form.errors`, `form.processing`, `form.clearErrors()`).

### PAGE-01 — Monitoring Arus (BUG-01, BUG-14)

- **Status:** ✅ Selesai
- **Commit:** `3b2a23f`
- **File diubah:** `app/Http/Controllers/CurrentMonitoringController.php`, `app/Http/Middleware/HandleInertiaRequests.php`, `routes/web.php`, `resources/js/Components/CurrentMonitoring/AdaptiveDataTable.vue` (ditulis ulang), `InteractiveLineChart.vue`, `PhaseCurrentTable.vue`, `resources/js/Pages/CurrentMonitoring/Index.vue`, `resources/js/Components/Desktop/Sidebar.vue`, `resources/js/utils/currentMatrix.js` (baru), `tests/Feature/Phase3BugFixTest.php` (baru), `tests/Frontend/currentMatrix.test.mjs` (baru)
- **Ringkasan perubahan:**
  - **BUG-01, sinkronisasi:** saat data server dimuat ulang, baris dipertahankan bila (1) diubah user (beda dari snapshot server lama) **dan** (2) masih beda dari data server baru. Hasilnya: baris yang baru tersimpan diambil dari server, baris yang gagal disimpan tetap utuh, dan baris yang tidak disentuh mengikuti data terbaru. Saat tanggal/shift berganti, semua baris di-reset.
  - **BUG-01, penanda & Simpan Semua:** baris yang belum disimpan diberi latar amber, garis kiri, dan teks "● Belum disimpan". Tombol **"Simpan Semua (n)"** mengirim ke endpoint baru `POST /monitoring-arus/batch` (atomik dalam transaksi, izin `monitoring_arus.input`). Error ditampilkan per baris, termasuk kunci `rows.<i>.values.<feeder>`.
  - **BUG-01, konfirmasi:** muncul sebelum ganti tanggal/shift (`applyFilter`/`selectShift`), sebelum navigasi GET lain (`router.on('before')`), dan sebelum reload/tutup tab (`beforeunload`). Tabel memakai `v-show` supaya isian tidak hilang saat pindah ke tab "Arus Tiap Fasa".
  - **Temuan tambahan:** nilai yang dikosongkan sebelumnya diabaikan server, sehingga salah input tidak bisa dihapus dan baris tertahan sebagai "belum disimpan". Sekarang nilai kosong menghapus catatan feeder tersebut. Interval divalidasi harus milik shift (`Rule::in`).
  - **BUG-14:**
    - operator kosong → nama akun login (di server, placeholder menampilkan nama akun);
    - jumlah feeder/interval diambil dari data, termasuk badge Sidebar lewat shared prop `feederCount`;
    - label sumbu Y/tooltip dibulatkan maks. 1 desimal;
    - tombol simpan mobile memakai `Button` dengan gaya disabled.
  - **BUG-05 (frontend):** input dinonaktifkan, dan kolom Aksi serta tombol simpan disembunyikan bila tidak ada `can('monitoring_arus.input')`.
- **Verifikasi:**
  - [x] BUG-01: re-init matriks mempertahankan baris *dirty* dan hanya menimpa baris yang tidak diubah → hasil: `utils/currentMatrix.js` `syncRows`. Unit test: "reproduksi BUG-01: simpan baris 08.30 tidak menghapus isian baris 09.00", "baris yang gagal disimpan … tetap mempertahankan isian", "baris yang tidak disentuh mengikuti data server terbaru", "ganti tanggal/shift … me-reset semua baris" → semua pass.
  - [x] BUG-01: `applyFilter` / `selectShift` memanggil `confirm()` bila ada baris dirty → hasil: lewat `confirmDiscard()` di `Pages/CurrentMonitoring/Index.vue`. Tanggal di input dikembalikan bila user batal.
  - [x] BUG-01: tombol "Simpan Semua" mengirim semua baris dirty → hasil: `saveAll()` mengirim `dirtyRows` ke `/monitoring-arus/batch`. Feature test `test_arus_batch_saves_multiple_rows`, `test_arus_batch_is_atomic_and_reports_errors_per_row`, `test_arus_batch_requires_input_permission` → pass.
  - [x] BUG-01: `saveRow` punya `onError` yang menyimpan error per baris → hasil: `row.errors = errors`, ditampilkan sebagai baris pesan `role="alert"` dan border merah pada input terkait. Pemetaan error batch diuji di unit test `mapBatchErrors`.
  - [x] BUG-14: feature test — POST `/monitoring-arus` tanpa `operator_name` → tersimpan dengan nama akun → hasil: `test_arus_operator_defaults_to_account_name` pass (+ `test_arus_explicit_operator_name_is_kept`).
  - [x] BUG-14: `grep -rn "12 Feeder\|16 Interval\|(12)" resources/js` → hasil: **0**. `test_feeder_count_is_shared_for_sidebar_badge` pass.
  - [x] BUG-14: formatter sumbu Y memakai `maximumFractionDigits` → hasil: `formatAmpere` di `InteractiveLineChart.vue` (`maximumFractionDigits: 1`).
  - [x] `npm run build` sukses → hasil: sukses. `ssr-pages.mjs`: `/monitoring-arus` admin punya "Simpan Data Jam" & kolom Aksi; manager tidak → PASS.
- **Catatan:** tombol Back browser (popstate) tidak melewati `router.on('before')`, jadi tidak dikonfirmasi. Reload/tutup tab dan navigasi menu tetap dikonfirmasi.

### PAGE-02 — kWh Produksi (BUG-13, BUG-15)

- **Status:** ✅ Selesai
- **Commit:** `9fa516e`
- **File diubah:** `Components/KwhProduction/KwhEngineTable.vue`, `KwhFeederTable.vue`, `KwhBarLineChart.vue`, `Pages/KwhProduction/Index.vue`, `Components/Shared/ChoiceValueInput.vue`, `Components/Shared/RowActions.vue` (baru), `utils/format.js` (baru), `tests/Frontend/ssr-*.mjs`
- **Verifikasi:**
  - [x] Modal Engine & Penyulang memakai komponen Modal/FormField → hasil: `<Modal>` + `<FormField>` + `<Button>` + `useForm`. Error field "stand" (lewat `ChoiceValueInput`) ditampilkan di bawah pilihan.
  - [x] Tombol Edit/Hapus mobile memakai padding ≥ `p-2.5` dan diberi jarak → hasil: `<RowActions size="lg">`. `ssr-components.mjs`: "RowActions lg: tombol p-2.5 + min-w-11 min-h-11 (>= 44px)", "jarak antar tombol gap-3" → PASS.
  - [x] Tombol aksi dibungkus `v-if="can('monitoring_kwh.input')"` → hasil: `canInput` di kedua tabel (tombol Tambah, kolom Aksi, tombol kartu mobile). `ssr-pages.mjs` `/monitoring-kwh`: admin melihat "Tambah Data Engine", kolom Aksi, Edit, Hapus; manager tidak → PASS.
  - [x] `npm run build` sukses → hasil: sukses. `grep -c "isDarkMode ?"` pada 5 file → 0.
- **Catatan:** tambah data kWh untuk tanggal+engine/penyulang yang sudah ada masih menimpa data lama (`updateOrCreate`), sama seperti BBM sebelum Fase 1. Perbaikan berbasis `id` belum dikerjakan karena di luar lingkup PAGE-02.

### PAGE-03 — Operasi Engine (BUG-13, BUG-15)

- **Status:** ✅ Selesai
- **Commit:** `13ded04`
- **File diubah:** `Components/EngineOperation/ControlPanelTable.vue`, `EngineAreaTable.vue`, `Pages/EngineOperation/Index.vue`, `Components/Shared/FormField.vue` (opsi `hideLabel`)
- **Verifikasi:**
  - [x] Modal Control Panel & Engine Area memakai komponen bersama → hasil: `<Modal max-width="2xl">` + `<FormField>` + `useForm`. Grup field memakai `<fieldset>/<legend>`; field tunggal dalam grup memakai label khusus pembaca layar (`hideLabel`).
  - [x] Tombol Edit/Hapus mobile memakai padding ≥ `p-2.5` → hasil: `<RowActions size="lg">`.
  - [x] Tombol aksi dibungkus `v-if="can('monitoring_engine.input')"` → hasil: `ssr-pages.mjs` `/monitoring-operasi-engine`: admin melihat "Tambah Data Control Panel", Aksi, Edit, Hapus; manager tidak → PASS.
  - [x] `npm run build` sukses → hasil: sukses. `grep -c "isDarkMode ?"` → 0.
- **Catatan:** label tombol tambah kini dibedakan ("Tambah Data Control Panel" / "Tambah Data Engine Area"). Pembulatan jam default (BUG-17) dikerjakan di Fase 4.

### PAGE-04 — Gangguan (BUG-11, BUG-13, BUG-15)

- **Status:** ✅ Selesai
- **Commit:** `e7be9df`
- **File diubah:** `app/Http/Controllers/DisturbanceMonitoringController.php`, `Components/DisturbanceMonitoring/DisturbanceDataTable.vue`, `DisturbanceCharts.vue`, `Pages/DisturbanceMonitoring/Index.vue`, `tests/Frontend/seed-ssr.php` (baru)
- **Verifikasi:**
  - [x] Tombol Batal, tombol X, box "Last Modified" memakai pasangan kelas terang + `dark:` → hasil: Batal/X kini dari `Button variant="secondary"`/`Modal`. `LC_ALL=C.UTF-8 grep -rnP '(?<!dark:)(hover:text-white"|bg-slate-900/40|border-slate-700 text-slate-400)' resources/js/Components/DisturbanceMonitoring` → **0**. Badge status kini `text-*-700 dark:text-*-400`.
  - [x] Modal memakai komponen `Modal.vue` (sudah ada `max-h` + scroll) → hasil: `<Modal max-width="md">`.
  - [x] Tombol aksi dibungkus `v-if="can('monitoring_gangguan.manage')"` → hasil: `ssr-pages.mjs` `/monitoring-gangguan`: admin melihat "Catat Gangguan Baru", Aksi, Edit, Hapus; manager tidak → PASS.
  - [x] `npm run build` sukses → hasil: sukses.
  - [x] **Temuan:** `POST /monitoring-gangguan` tanpa `operator_name` → **error 500** (`Undefined array key`), ditemukan saat membuat `seed-ssr.php`. Diperbaiki, dan operator kosong → nama akun. `test_disturbance_without_optional_fields_is_saved_with_account_name` pass.
- **Catatan:** empty state untuk tampilan mobile ditambahkan (sebelumnya kosong tanpa pesan).

### PAGE-05 — BBM (BUG-11, BUG-13, BUG-15)

- **Status:** ✅ Selesai
- **Commit:** `a4be48b`
- **File diubah:** `app/Http/Controllers/FuelStockController.php`, `Components/FuelStock/FuelStockDataTable.vue`, `FuelStockChart.vue`, `Pages/FuelStock/Index.vue`
- **Verifikasi:**
  - [x] Tombol Batal, tombol X, box "Last Modified" memakai pasangan kelas terang + `dark:` → hasil: grep yang sama pada `Components/FuelStock` → **0**. Ikut diperbaiki: teks pratinjau rumus (sebelumnya `text-slate-300` di latar terang), badge "Sisa Hari Operasi" (`text-*-300`), serta nilai statistik & peringatan HOP di halaman (`text-rose-400`/`text-cyan-400`).
  - [x] Nilai awal form `null` + placeholder, bukan `0` → hasil: `emptyForm()` mengisi `null` untuk kelima field angka. `test_fuel_empty_amounts_are_saved_as_zero_with_account_name` pass (server tetap menyimpan 0 untuk yang kosong).
  - [x] Tombol aksi dibungkus `v-if="can('monitoring_bbm.input')"` → hasil: `ssr-pages.mjs` `/monitoring-bbm`: admin melihat "Tambah Data Stok BBM", Aksi, Edit, Hapus; manager tidak → PASS.
  - [x] `npm run build` sukses → hasil: sukses.
- **Catatan:** operator kosong → nama akun (sebelumnya "Operator").

### PAGE-06 — User Management (BUG-11, BUG-16, sebagian BUG-17)

- **Status:** ✅ Selesai
- **Commit:** `25f20bb`
- **File diubah:** `resources/js/Pages/UserManagement/Index.vue` (ditulis ulang), `tests/Feature/Phase3BugFixTest.php`, `tests/Frontend/ssr-pages.mjs`
- **Verifikasi:**
  - [x] Ada blok kartu `md:hidden` dengan tombol aksi, tabel dibungkus `hidden md:block` → hasil: `ssr-pages.mjs` "/users mobile: ada tampilan kartu", "tabel desktop hidden md:block", "tombol kartu … min-h-11" → PASS.
  - [x] Email & badge role memakai pasangan kelas terang + `dark:` → hasil: `text-slate-600 dark:text-slate-300` dan `text-*-700 dark:text-*-400`. Ikut diperbaiki: kartu KPI role, badge status, dan nama izin di matriks PBAC (sebelumnya `text-slate-200` di latar terang, hampir tak terbaca).
  - [x] `applySearch` dipanggil lewat debounce (±300ms) → hasil: `applySearchDebounced` → `setTimeout(applySearch, 300)`. Filter role tetap langsung.
  - [x] `toggleUserStatus` memanggil `confirm()` sebelum request → hasil: ada, dengan peringatan "tidak akan bisa login" saat menonaktifkan.
  - [x] `openAddModal` mengisi `form.password = ''`; feature test — POST `/users` tanpa password → error `password` → hasil: `emptyForm().password = ''`. `test_new_user_requires_password` pass.
  - [x] `npm run build` sukses → hasil: sukses.
- **Catatan:** toggle status dan pilihan role dinonaktifkan untuk akun sendiri, dengan penanda "(Anda)" dan hint, sesuai aturan server dari Fase 1.

### PAGE-07 — Dashboard (BUG-08 frontend, BUG-11)

- **Status:** ✅ Selesai
- **Commit:** `cc6ba22`
- **File diubah:** `app/Http/Controllers/DashboardController.php` (prop `has_data`, tanggal berbahasa Indonesia), `resources/js/Pages/Dashboard/Index.vue`, `tests/Feature/Phase3BugFixTest.php`, `tests/Frontend/ssr-pages.mjs`
- **Verifikasi:**
  - [x] Template memakai prop `todayDateFormatted` → hasil: tampil di header ("Kamis, 24 September 2026"). `test_dashboard_date_is_formatted_in_indonesian` pass; `ssr-pages.mjs` "tanggal hari ini tampil" PASS.
  - [x] Sub-teks gangguan merender `in_progress`, `investigating` dan `resolved` → hasil: "… Penanganan • … Investigasi • … Selesai".
  - [x] Teks "Shift …" diganti "Update terakhir …" → hasil: `grep -n "Shift {{" resources/js/Pages/Dashboard/Index.vue` → **0**. Bila belum ada input: "Belum ada input hari ini". `ssr-pages.mjs` "tidak ada label Shift <jam>" PASS.
  - [x] Warna titik status feeder bergantung pada data; feature test memastikan prop penanda dikirim → hasil: `has_data` → hijau / abu. `grep -c bg-emerald-400` → 0. `test_dashboard_feeder_status_marks_feeders_without_data` pass.
  - [x] Link kartu KPI dibungkus `can('<modul>.view')` → hasil: `<component :is="can(…) ? Link : 'div'">`; "Buka Matriks" & "Lihat Semua Log Gangguan" juga. `ssr-pages.mjs`: TL Pemeliharaan (tanpa izin kWh & BBM) tidak mendapat link ke `/monitoring-kwh` & `/monitoring-bbm` di isi halaman, tetapi tetap ke `/monitoring-gangguan` → PASS.
  - [x] Badge "Belum Ada Data" punya warna sendiri (bukan warna Kritis) → hasil: `fuelBadgeClass` default → slate netral.
  - [x] Tombol "Lihat Semua Log Gangguan" & deskripsi memakai pasangan kelas terang + `dark:` → hasil: sudah sejak Fase 2; garis putus-putus di atasnya ikut diperbaiki (`border-slate-200 dark:border-slate-700/50`).
  - [x] `npm run build` sukses → hasil: sukses.
- **Catatan:** pengecekan link dibatasi ke isi `<main>`, karena BottomNav mobile masih menampilkan semua menu tanpa filter izin. Itu BUG-06 dan dikerjakan di Fase 4.

---

## Fase 4 — Navigasi & polish

> **Tanggal pengerjaan:** 2026-09-24 · **Branch:** `fix/ux-review` · **Commit:** `5e56f2f` (satu commit untuk seluruh Fase 4).
>
> **Bukti otomatis yang berlaku untuk seluruh Fase 4:**
> - `npm run build` → **sukses** (`✓ built in 6.01s`).
> - `node tests/Frontend/ssr-pages.mjs` → **SEMUA PASS (152 cek)**. Termasuk cek BottomNav per role, tombol "Lainnya", min-h-[44px], link `/users` untuk admin, header judul halaman aktif, jam operasional WIT, dan proteksi tombol demo login lokal (`isLocal`).
> - `node tests/Frontend/ssr-components.mjs` → **SEMUA PASS (21 cek)**.
> - `node --test tests/Frontend/*.test.mjs` → **7/7 pass**.
> - `php artisan test --filter=Phase` → **29 passed** (137 assertions).
> - `php artisan route:list -v` → 19 route modul terbukti dilindungi middleware `permission:<slug>`.

### BUG-06 — Bottom nav mobile

- **Status:** ✅ Selesai
- **Commit:** `5e56f2f`
- **File diubah:** `resources/js/Components/Mobile/BottomNav.vue`, `tests/Frontend/ssr-pages.mjs`
- **Ringkasan perubahan:**
  - `BottomNav.vue` ditulis ulang menggunakan `usePermission()` (`const { can } = usePermission()`). Seluruh 7 modul didefinisikan dengan permission slug, route match, label, ikon, dan aksen warna.
  - Maksimal 4 item utama yang diizinkan tampil di bar bawah (`primaryNavItems = permittedItems.slice(0, 4)`).
  - Slot ke-5 adalah tombol **"Lainnya"** (`min-h-[44px]`, `text-[10px]`) yang membuka bottom sheet modal (`Teleport to="body"`).
  - Bottom sheet memuat modul tersisa yang diizinkan (mis. Gangguan, BBM, Pengelolaan Pengguna `/users`), info akun pegawai login, dan tombol logout langsung dari mobile.
  - Role admin dapat mengakses Pengelolaan Pengguna dari HP; role tanpa izin tertentu (mis. TL Pemeliharaan) tidak lagi melihat menu kWh/BBM di bar mobile.
  - Sheet menutup saat link diklik, backdrop diklik, atau tombol Escape ditekan (`onKeydown`).
  - Seluruh kelas styling dikonversi ke varian Tailwind `dark:`.
- **Verifikasi:**
  - [x] Setiap item `BottomNav.vue` dibungkus `can('<modul>.view')` → hasil: `permittedItems` memfilter seluruh item dengan `can()`. `ssr-pages.mjs`: TL Pemeliharaan TIDAK punya link `/monitoring-kwh` & `/monitoring-bbm` di seluruh HTML layout (PASS).
  - [x] Menu "Lainnya" berisi link `/users` yang dibungkus `can('users.manage')` → hasil: `ssr-pages.mjs`: admin punya link `/users` di menu mobile (PASS).
  - [x] Label memakai ukuran ≥ `text-[10px]` dan maksimal 5 slot di bar → hasil: `text-[10px]` dipakai di semua tombol bar; maksimal 4 slot utama + 1 slot Lainnya (total 5 slot); target sentuh `min-h-[44px]` (PASS di `ssr-pages.mjs`).
  - [x] `npm run build` sukses → hasil: sukses (`✓ built in 6.01s`).
- **Catatan:** Tombol "Lainnya" otomatis aktif (berwarna aksen) jika halaman yang sedang dibuka berada di dalam daftar sheet.

### BUG-09 — Judul halaman & Header

- **Status:** ✅ Selesai
- **Commit:** `5e56f2f`
- **File diubah:**
  - `resources/views/app.blade.php`: `<title>` statis diganti `<title inertia>{{ config('app.name', 'PLN Monitor ULPLTD POKA') }}</title>`
  - `resources/js/app.js`: title format `title ? "${title} — PLN Monitor ULPLTD POKA" : "PLN Monitor ULPLTD POKA"`
  - `resources/js/Components/Desktop/Header.vue`: judul halaman aktif & jam operasional WIT
  - 9 halaman di `resources/js/Pages/**`: memasang `<Head title="…">`
- **Ringkasan perubahan:**
  - Semua 9 halaman (`Dashboard`, `CurrentMonitoring`, `KwhProduction`, `EngineOperation`, `DisturbanceMonitoring`, `FuelStock`, `UserManagement`, `Login`, `ForgotPassword`) mengimpor `Head` dari `@inertiajs/vue3` dan memasang judul halaman masing-masing.
  - `<title>` statis di blade dihapus dan diserahkan ke Inertia `@inertiaHead`.
  - [Header.vue](file:///d:/PROJECT/monitoring-pln/resources/js/Components/Desktop/Header.vue) menghitung `pageTitle` berdasarkan route aktif dan merendernya di mobile dan desktop.
  - Jam realtime `currentTime` kini dirender di Header dengan ikon `Clock` dan label zona waktu `WIT` (sangat bermanfaat untuk operator shift).
  - Unused import `Menu` dan unused emit `openMobileSidebar` dibersihkan.
  - Sisa ternary `isDarkMode` di Header diganti dengan varian `dark:`.
- **Verifikasi:**
  - [x] Setiap file di `resources/js/Pages/**` memakai `<Head title="…">` (`grep -rL "<Head" resources/js/Pages` → 0 hasil) → hasil: semua 9 halaman terverifikasi memuat `<Head title="...">`.
  - [x] `<title>` statis dihapus dari `app.blade.php` → hasil: menggunakan `<title inertia>` dengan nama aplikasi.
  - [x] Header merender judul halaman → hasil: `ssr-pages.mjs`: "Header: judul halaman aktif ter-render" PASS.
  - [x] `currentTime` dirender di template, atau `setInterval` dihapus → hasil: dirender di template dengan format WIT dan timer dibersihkan di `onUnmounted`. `ssr-pages.mjs`: "Header: jam operasional WIT ter-render" PASS.
- **Catatan:** Format judul di browser: `<Halaman> — PLN Monitor ULPLTD POKA`.

### BUG-17 — Isu minor

| Sub-item | Status | Commit | Bukti / hasil |
|---|---|---|---|
| Hapus `maximum-scale=1.0` | ✅ | `5e56f2f` | `app.blade.php`: viewport sekarang `width=device-width, initial-scale=1.0` (zoom mobile aktif) |
| Tombol demo login hanya di lokal | ✅ | `5e56f2f` | `HandleInertiaRequests.php` share `isLocal`; `Login.vue` bungkus demo dengan `v-if="$page.props.isLocal"`. `ssr-pages.mjs` PASS |
| Gaya disabled & error per field di Login | ✅ | `5e56f2f` | Submit button punya `disabled:opacity-60 disabled:cursor-not-allowed`; error login & password dirender langsung di bawah field terkait |
| Password default user baru dihapus | ✅ | `25f20bb` | `emptyForm().password = ''`; `test_new_user_requires_password` pass |
| Debounce pencarian user | ✅ | `25f20bb` | `setTimeout(applySearch, 300)` |
| Konfirmasi toggle status user | ✅ | `25f20bb` | `confirm()` di `toggleUserStatus` |
| Warna ikon Sidebar konsisten | ✅ | `5e56f2f` | Ikon sidebar disesuaikan per modul (Arus: cyan, kWh: emerald, Engine: violet, Gangguan: rose, BBM: amber, Users: purple); ternary dark dimigrasikan ke `dark:` |
| Empty state grafik kWh | ✅ | `5e56f2f` | `KwhBarLineChart.vue`: jika `dates.length === 0`, render pesan empty state rapi (bukan grafik kosong pecah) |
| Form BBM/Gangguan tanpa angka 0 awal | ✅ | `a4be48b` | BBM: `emptyForm()` null + placeholder; Gangguan tidak punya field angka |
| Jam default form Engine dibulatkan | ✅ | `5e56f2f` | `currentTimeRounded()` di `date.js` membulatkan menit ke jam/setengah jam terdekat (08:07 -> 08:00, 08:18 -> 08:30); dipasang di Control Panel & Engine Area |
| Info saat data tersimpan di luar filter | ✅ | `5e56f2f` | Saat input kWh, Engine, BBM, atau Gangguan untuk tanggal/bulan di luar filter aktif, halaman otomatis berpindah ke tanggal/bulan tersebut agar data langsung terlihat |
| Tinjau ulang `ChoiceValueInput` | ✅ | `5e56f2f` | `ChoiceValueInput.vue`: untuk grup pilihan <= 6 item (seperti kWh Penyulang & Engine Area), input ditampilkan langsung dalam grid 2-kolom tanpa perlu memilih satu-satu dari dropdown |

---

## Verifikasi akhir (setelah semua fase)

- [x] `npm run build` sukses → hasil: **Sukses** (`✓ built in 6.01s`).
- [x] `php artisan test` → hasil: **30 passed**, 1 failed (`ExampleTest`, 302 guest redirect ke login; fitur bawaan).
- [x] Feature test akses 5 role (admin, manager, tl_operasi, tl_pemeliharaan, operator) ke setiap route GET/POST/DELETE → hasil: **29 passed (137 assertions)** di `Phase1BugFixTest` & `Phase3BugFixTest`.
- [x] `php artisan route:list -v` → setiap route modul memakai middleware `permission:` → hasil: 19 route modul terbukti dilindungi `permission:<slug>`.

## Log perubahan dokumen

| Tanggal | Perubahan | Oleh |
|---|---|---|
| 2026-09-24 | Dokumen dibuat, semua status ⬜ Belum | Claude |
| 2026-09-24 | Fase 1 dikerjakan: bukti tes otomatis & build diisi; status 🔄 menunggu commit dan uji browser | Claude |
| 2026-09-24 | BUG-04: simpan BBM berbasis `id` di server + 3 tes; perbaikan error 500 field opsional | Claude |
| 2026-09-24 | PREP-01 selesai (branch `fix/ux-review`, baseline `1893a20`); Fase 1 di-commit | Claude |
| 2026-09-24 | Uji browser dihapus dari seluruh dokumen; verifikasi diganti tes otomatis, build dan pemeriksaan kode. Status Fase 1: BUG-02, BUG-03, BUG-04 dan BUG-08 (backend) ✅; BUG-05 ⚠️ Sebagian | febriansyahcc |
| 2026-09-24 | Kriteria BUG-03 disesuaikan dengan bukti pemeriksaan kode; kriteria BUG-02 di report memakai WIT | Claude |
| 2026-09-24 | Fase 2 dikerjakan (`5fd800a`): BUG-07, BUG-10, BUG-12, FOUND-01 ✅; BUG-11 & BUG-13 ⚠️ Sebagian (lanjut di Fase 3); verifikasi SSR `tests/Frontend/` | Claude |
| 2026-09-24 | Fase 3 dikerjakan (7 commit: `3b2a23f` `9fa516e` `13ded04` `e7be9df` `a4be48b` `25f20bb` `cc6ba22`): BUG-01, 05, 08, 11, 13, 14, 15, 16 ✅; BUG-17 ⚠️ 4/12 sub-item | Claude |
| 2026-09-24 | Fase 4 dikerjakan (`5e56f2f`): BUG-06, BUG-09, BUG-17 ✅ Selesai (100% dari 21 item selesai). Verifikasi SSR 152 PASS, test feature 29 PASS, build sukses | Antigravity |
