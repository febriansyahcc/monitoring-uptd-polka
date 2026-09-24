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
| Fase 1 — Bug data & keamanan | 5 | 4 | ⚠️ Sebagian — BUG-05 bagian frontend (sembunyikan tombol aksi) menunggu Fase 2/3 |
| Fase 2 — Fondasi | 5 | 0 | ⬜ Belum |
| Fase 3 — Migrasi per halaman | 7 | 0 | ⬜ Belum |
| Fase 4 — Navigasi & polish | 3 | 0 | ⬜ Belum |
| **Total** | **21** | **5** | **24%** |

## Ringkasan status per bug

| ID | Judul | Prioritas | Status | Commit | Tanggal selesai |
|---|---|---|---|---|---|
| BUG-01 | Simpan satu baris menghapus input baris lain | 🔴 Kritis | ⬜ Belum | – | – |
| BUG-02 | Tanggal default form memakai UTC | 🔴 Kritis | ✅ Selesai | `6c6cf8c` | 2026-09-24 |
| BUG-03 | Modal menutup walau validasi gagal | 🔴 Kritis | ✅ Selesai | `6c6cf8c` | 2026-09-24 |
| BUG-04 | Edit BBM ubah tanggal → data duplikat | 🔴 Kritis | ✅ Selesai | `6c6cf8c` | 2026-09-24 |
| BUG-05 | PBAC tidak ditegakkan di server | 🔴 Kritis | ⚠️ Sebagian (server selesai; frontend di Fase 2/3) | `6c6cf8c` | – |
| BUG-06 | Bottom nav mobile tidak konsisten | 🟠 Tinggi | ⬜ Belum | – | – |
| BUG-07 | Layout re-mount tiap navigasi | 🟠 Tinggi | ⬜ Belum | – | – |
| BUG-08 | KPI Dashboard menyesatkan | 🟠 Tinggi | ⚠️ Sebagian (backend selesai; frontend di PAGE-07) | `6c6cf8c` | – |
| BUG-09 | Tidak ada judul halaman / title statis | 🟠 Tinggi | ⬜ Belum | – | – |
| BUG-10 | Flash sukses tidak terlihat | 🟠 Tinggi | ⬜ Belum | – | – |
| BUG-11 | Warna dark bocor ke light mode | 🟡 Sedang | ⬜ Belum | – | – |
| BUG-12 | Class `slate-850` tidak ada | 🟡 Sedang | ⬜ Belum | – | – |
| BUG-13 | Modal tidak konsisten & kurang aksesibel | 🟡 Sedang | ⬜ Belum | – | – |
| BUG-14 | Tabel input Arus berat & angka hardcoded | 🟡 Sedang | ⬜ Belum | – | – |
| BUG-15 | Target sentuh mobile terlalu kecil | 🟡 Sedang | ⬜ Belum | – | – |
| BUG-16 | User Management tanpa tampilan mobile | 🟡 Sedang | ⬜ Belum | – | – |
| BUG-17 | Kumpulan isu minor | 🟢 Rendah | ⬜ Belum | – | – |

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

- **Status:** ⚠️ Sebagian. Sisi server selesai dan lolos tes. Sub-item frontend (menyembunyikan tombol aksi lewat `usePermission()`) dijadwalkan di Fase 2 (FOUND-01) dan Fase 3 per halaman.
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
  - [ ] Frontend: tombol Tambah/Edit/Hapus dibungkus `v-if="can('<modul>.input')"` (cek dengan `grep -rn "can('" resources/js`) → hasil: – (belum; dikerjakan di Fase 2/3)
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

### BUG-07 — Persistent layout, status sidebar, tema

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Ringkasan perubahan:** –
- **Kriteria selesai:** layout tidak di-mount ulang per halaman, status sidebar tersimpan, dan tema dipasang sebelum Vue mount.
- **Verifikasi:**
  - [ ] `grep -rln "<AppLayout" resources/js/Pages` → 0 hasil (layout di-set sebagai default di `app.js`) → hasil: –
  - [ ] Status collapse sidebar dibaca/ditulis ke localStorage (dibungkus try/catch) → cek kode `AppLayout.vue` → hasil: –
  - [ ] `resources/views/app.blade.php` memuat script inline di `<head>` yang memasang class `dark` pada `<html>` → hasil: –
  - [ ] `Auth/Login` & `Auth/ForgotPassword` mengecualikan layout (mis. `layout: null`) → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

### BUG-11 & BUG-12 — Varian `dark:` & `slate-850`

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Ringkasan perubahan:** –
- **Verifikasi:**
  - [ ] `grep -rn "slate-850" resources/js` → 0 hasil → hasil: –
  - [ ] Setiap lokasi di tabel BUG-11 memakai pasangan kelas terang + `dark:` (tidak ada kelas gelap tanpa pasangan terang) → cek dengan `grep`/diff per file → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** migrasi penuh ke `dark:` bisa berlanjut di Fase 3 per halaman.

### BUG-13 — Komponen Modal, FormField, Button

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah / dibuat:** –
- **Ringkasan perubahan:** –
- **Verifikasi (pemeriksaan kode komponen):**
  - [ ] `Modal.vue` punya listener `keydown` Escape yang menutup modal → hasil: –
  - [ ] Backdrop memakai `@click.self` untuk menutup → hasil: –
  - [ ] Fokus awal diarahkan ke field pertama (`nextTick` + `focus()`), ada `role="dialog"` dan `aria-modal` → hasil: –
  - [ ] Panel modal memakai `max-h-[90vh] overflow-y-auto` → hasil: –
  - [ ] `FormField.vue` menghubungkan `<label for>` dengan `id` input → hasil: –
  - [ ] `Button.vue` memasang atribut `disabled` saat `loading` dan punya gaya `disabled:` → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

### BUG-10 — Toast global

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Ringkasan perubahan:** –
- **Verifikasi:**
  - [ ] `Toast.vue` dipasang di `AppLayout` dengan posisi `fixed` dan membaca `flash.success` / `flash.error` → hasil: –
  - [ ] Ada timer auto-dismiss dan tombol tutup di kode `Toast.vue` → hasil: –
  - [ ] `grep -rn "flash.success" resources/js/Pages` → 0 hasil (blok flash per halaman sudah dihapus) → hasil: –
- **Catatan:** –

### FOUND-01 — Composable `usePermission()`

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah / dibuat:** –
- **Verifikasi:**
  - [ ] `grep -rn "hasPerm" resources/js` → 0 hasil; Sidebar memakai `can()` dari composable → hasil: –
  - [ ] Admin selalu `true`, role lain membaca `page.props.auth.permissions` (logika sama dengan `hasPerm` lama) → hasil: –
- **Catatan:** dipakai oleh BUG-05 (frontend), BUG-06, BUG-08.

---

## Fase 3 — Migrasi per halaman

> Untuk setiap halaman, verifikasi minimal: `npm run build` sukses, kelas tema memakai varian `dark:`, tampilan mobile (`md:hidden`) tersedia, dan tombol aksi dibungkus `can('<modul>.input')`.

### PAGE-01 — Monitoring Arus (BUG-01, BUG-14)

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Ringkasan perubahan:** –
- **Verifikasi:**
  - [ ] BUG-01: re-init matriks mempertahankan baris *dirty* dan hanya menimpa baris yang tidak diubah → cek kode `AdaptiveDataTable.vue` → hasil: –
  - [ ] BUG-01: `applyFilter` / `selectShift` memanggil `confirm()` bila ada baris dirty → cek kode → hasil: –
  - [ ] BUG-01: tombol "Simpan Semua" mengirim semua baris dirty → cek kode → hasil: –
  - [ ] BUG-01: `saveRow` punya `onError` yang menyimpan error per baris → cek kode → hasil: –
  - [ ] BUG-14: feature test — POST `/monitoring-arus` tanpa `operator_name` → tersimpan dengan nama akun → hasil: –
  - [ ] BUG-14: `grep -rn "12 Feeder\|16 Interval\|(12)" resources/js` → 0 hasil → hasil: –
  - [ ] BUG-14: formatter sumbu Y memakai `maximumFractionDigits` → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

### PAGE-02 — kWh Produksi (BUG-13, BUG-15)

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Verifikasi:**
  - [ ] Modal Engine & Penyulang memakai komponen Modal/FormField → hasil: –
  - [ ] Tombol Edit/Hapus mobile memakai padding ≥ `p-2.5` dan diberi jarak (cek kelas) → hasil: –
  - [ ] Tombol aksi dibungkus `v-if="can('monitoring_kwh.input')"` → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

### PAGE-03 — Operasi Engine (BUG-13, BUG-15)

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Verifikasi:**
  - [ ] Modal Control Panel & Engine Area memakai komponen bersama → hasil: –
  - [ ] Tombol Edit/Hapus mobile memakai padding ≥ `p-2.5` (cek kelas) → hasil: –
  - [ ] Tombol aksi dibungkus `v-if="can('monitoring_engine.input')"` → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

### PAGE-04 — Gangguan (BUG-11, BUG-13, BUG-15)

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Verifikasi:**
  - [ ] Tombol Batal, tombol X, box "Last Modified" memakai pasangan kelas terang + `dark:` (tidak ada lagi `border-slate-700`/`hover:text-white`/`bg-slate-900/40` tanpa `dark:`) → hasil: –
  - [ ] Modal memakai komponen `Modal.vue` (sudah ada `max-h` + scroll) → hasil: –
  - [ ] Tombol aksi dibungkus `v-if="can('monitoring_gangguan.manage')"` → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

### PAGE-05 — BBM (BUG-11, BUG-13, BUG-15)

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Verifikasi:**
  - [ ] Tombol Batal, tombol X, box "Last Modified" memakai pasangan kelas terang + `dark:` (tidak ada lagi `border-slate-700`/`hover:text-white`/`bg-slate-900/40` tanpa `dark:`) → hasil: –
  - [ ] Nilai awal form `null` + placeholder, bukan `0` → cek `openAddModal` → hasil: –
  - [ ] Tombol aksi dibungkus `v-if="can('monitoring_bbm.input')"` → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

### PAGE-06 — User Management (BUG-11, BUG-16, sebagian BUG-17)

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Verifikasi:**
  - [ ] Ada blok kartu `md:hidden` dengan tombol aksi, tabel dibungkus `hidden md:block` → hasil: –
  - [ ] Email & badge role memakai pasangan kelas terang + `dark:` → hasil: –
  - [ ] `applySearch` dipanggil lewat debounce (±300ms) → cek kode → hasil: –
  - [ ] `toggleUserStatus` memanggil `confirm()` sebelum request → hasil: –
  - [ ] `openAddModal` mengisi `form.password = ''`; feature test — POST `/users` tanpa password → error `password` → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

### PAGE-07 — Dashboard (BUG-08 frontend, BUG-11)

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Verifikasi:**
  - [ ] Template memakai prop `todayDateFormatted` → hasil: –
  - [ ] Sub-teks gangguan merender `in_progress`, `investigating` dan `resolved` → hasil: –
  - [ ] Teks "Shift {{ kpi.current.latest_interval }}" diganti "Update terakhir …" (`grep -n "Shift {{" resources/js/Pages/Dashboard/Index.vue` → 0 hasil) → hasil: –
  - [ ] Warna titik status feeder bergantung pada data (tidak lagi hardcoded `bg-emerald-400`); feature test memastikan prop penanda "belum ada data" dikirim → hasil: –
  - [ ] Link kartu KPI dibungkus `can('<modul>.view')` → hasil: –
  - [ ] Badge "Belum Ada Data" punya warna sendiri (bukan warna Kritis) → hasil: –
  - [ ] Tombol "Lihat Semua Log Gangguan" & deskripsi memakai pasangan kelas terang + `dark:` → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

---

## Fase 4 — Navigasi & polish

### BUG-06 — Bottom nav mobile

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Verifikasi:**
  - [ ] Setiap item `BottomNav.vue` dibungkus `can('<modul>.view')` → hasil: –
  - [ ] Menu "Lainnya" berisi link `/users` yang dibungkus `can('users.manage')` → hasil: –
  - [ ] Label memakai ukuran ≥ `text-[10px]` dan maksimal 5 slot di bar → hasil: –
  - [ ] `npm run build` sukses → hasil: –
- **Catatan:** –

### BUG-09 — Judul halaman & Header

- **Status:** ⬜ Belum
- **Commit:** –
- **File diubah:** –
- **Verifikasi:**
  - [ ] Setiap file di `resources/js/Pages/**` memakai `<Head title="…">` (`grep -rL "<Head" resources/js/Pages` → 0 hasil) → hasil: –
  - [ ] `<title>` statis dihapus dari `app.blade.php` → hasil: –
  - [ ] Header merender judul halaman → cek kode `Header.vue` → hasil: –
  - [ ] `currentTime` dirender di template, atau `setInterval` dihapus → hasil: –
- **Catatan:** –

### BUG-17 — Isu minor

| Sub-item | Status | Commit | Bukti / hasil |
|---|---|---|---|
| Hapus `maximum-scale=1.0` | ⬜ | – | – |
| Tombol demo login hanya di lokal | ⬜ | – | – |
| Gaya disabled & error per field di Login | ⬜ | – | – |
| Password default user baru dihapus | ⬜ | – | – |
| Debounce pencarian user | ⬜ | – | – |
| Konfirmasi toggle status user | ⬜ | – | – |
| Warna ikon Sidebar konsisten | ⬜ | – | – |
| Empty state grafik kWh | ⬜ | – | – |
| Form BBM/Gangguan tanpa angka 0 awal | ⬜ | – | – |
| Jam default form Engine dibulatkan | ⬜ | – | – |
| Info saat data tersimpan di luar filter | ⬜ | – | – |
| Tinjau ulang `ChoiceValueInput` | ⬜ | – | – |

---

## Verifikasi akhir (setelah semua fase)

- [ ] `npm run build` sukses → hasil: –
- [ ] `php artisan test` → hasil: –
- [ ] Feature test akses 5 role (admin, manager, tl_operasi, tl_pemeliharaan, operator) ke setiap route GET/POST/DELETE → hasil: –
- [ ] `php artisan route:list -v` → setiap route modul memakai middleware `permission:` → hasil: –

## Log perubahan dokumen

| Tanggal | Perubahan | Oleh |
|---|---|---|
| 2026-09-24 | Dokumen dibuat, semua status ⬜ Belum | Claude |
| 2026-09-24 | Fase 1 dikerjakan: bukti tes otomatis & build diisi; status 🔄 menunggu commit dan uji browser | Claude |
| 2026-09-24 | BUG-04: simpan BBM berbasis `id` di server + 3 tes; perbaikan error 500 field opsional | Claude |
| 2026-09-24 | PREP-01 selesai (branch `fix/ux-review`, baseline `1893a20`); Fase 1 di-commit | Claude |
| 2026-09-24 | Uji browser dihapus dari seluruh dokumen; verifikasi diganti tes otomatis, build dan pemeriksaan kode. Status Fase 1: BUG-02, BUG-03, BUG-04 dan BUG-08 (backend) ✅; BUG-05 ⚠️ Sebagian | febriansyahcc |
| 2026-09-24 | Kriteria BUG-03 disesuaikan dengan bukti pemeriksaan kode; kriteria BUG-02 di report memakai WIT | Claude |
