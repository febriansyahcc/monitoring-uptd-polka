# Laporan Bug & UX — PLN Monitor ULPLTD POKA

> Hasil review UI/UX berbasis pembacaan kode (belum diuji langsung di browser).
> Tanggal review: 2026-09-24
> Stack: Laravel + Inertia (Vue 3, `<script setup>`) + Tailwind v4 (dark mode class-based via `@variant dark (&:where(.dark, .dark *))` di `resources/css/app.css`) + ApexCharts.

## Cara memakai dokumen ini

- Kerjakan **berurutan per fase** (lihat [Rencana Pengerjaan](#rencana-pengerjaan)). Fase 1 tidak bergantung pada refactor apa pun.
- Setiap bug punya ID (`BUG-xx`), lokasi file, cara reproduksi, saran perbaikan, dan kriteria selesai.
- Sebelum mulai: commit perubahan yang belum ter-commit dan buat branch baru (mis. `fix/ux-review`).
- Satu fase / satu halaman = satu commit, agar mudah di-review dan di-rollback.
- Centang checklist di bagian bawah setiap selesai.
- **Catat bukti pengerjaan** (commit, file diubah, langkah verifikasi, hasil) di [UX_BUG_SOLVED.md](UX_BUG_SOLVED.md). Status bug baru boleh ✅ jika buktinya sudah terisi.

## Ringkasan

| ID | Judul | Prioritas | Fase |
|---|---|---|---|
| BUG-01 | Simpan satu baris menghapus input baris lain (Monitoring Arus) | 🔴 Kritis | 3 |
| BUG-02 | Tanggal default form memakai UTC → salah hari saat shift malam | 🔴 Kritis | 1 |
| BUG-03 | Modal menutup walau validasi gagal, error tidak tampil | 🔴 Kritis | 1 |
| BUG-04 | Edit BBM dengan ubah tanggal menghasilkan data duplikat | 🔴 Kritis | 1 |
| BUG-05 | PBAC hanya di UI, tidak ditegakkan di server | 🔴 Kritis | 1 |
| BUG-06 | Bottom nav mobile tidak difilter izin & tanpa menu Users | 🟠 Tinggi | 4 |
| BUG-07 | Layout re-mount tiap navigasi (sidebar reset, flicker tema) | 🟠 Tinggi | 2 |
| BUG-08 | KPI Dashboard menyesatkan | 🟠 Tinggi | 1 & 3 |
| BUG-09 | Tidak ada judul halaman / `<title>` statis, jam header tak terpakai | 🟠 Tinggi | 4 |
| BUG-10 | Flash sukses tidak terlihat saat simpan di bawah halaman | 🟠 Tinggi | 2 |
| BUG-11 | Warna khusus dark mode bocor ke light mode (kontras rendah) | 🟡 Sedang | 2 & 3 |
| BUG-12 | Class `slate-850` tidak ada di Tailwind | 🟡 Sedang | 2 |
| BUG-13 | Modal tidak konsisten & kurang aksesibel | 🟡 Sedang | 2 |
| BUG-14 | Tabel input Monitoring Arus berat & angka hardcoded | 🟡 Sedang | 3 |
| BUG-15 | Target sentuh tombol mobile terlalu kecil | 🟡 Sedang | 3 |
| BUG-16 | User Management tanpa tampilan mobile | 🟡 Sedang | 3 |
| BUG-17 | Kumpulan isu minor (polish) | 🟢 Rendah | 4 |

---

## 🔴 Kritis

### BUG-01 — Simpan satu baris menghapus input baris lain (Monitoring Arus)

- **File:** `resources/js/Components/CurrentMonitoring/AdaptiveDataTable.vue` (watch di ±baris 373, `initMatrixData`, `saveRow`)
- **Penyebab:** setelah `router.post` sukses, prop `matrix` berubah → `watch(() => props.matrix, initMatrixData, { deep: true })` membangun ulang **semua** baris `matrixData`, sehingga input yang belum disimpan di baris lain hilang.
- **Reproduksi:**
  1. Buka `/monitoring-arus`.
  2. Isi nilai di baris jam 08.30 dan 09.00.
  3. Klik **Simpan** hanya di baris 08.30.
  4. Isian baris 09.00 hilang.
- **Masalah terkait:** ganti tanggal/shift (`Pages/CurrentMonitoring/Index.vue` → `applyFilter`, `selectShift`) juga membuang input yang belum disimpan tanpa konfirmasi.
- **Saran perbaikan:**
  - Saat re-init, pertahankan baris yang *dirty* (bandingkan dengan nilai server), hanya perbarui baris yang baru disimpan.
  - Tambah penanda visual baris belum disimpan (mis. border amber + titik).
  - Tambah tombol **"Simpan Semua"** untuk baris dirty.
  - Konfirmasi sebelum ganti tanggal/shift bila ada baris dirty.
  - Tampilkan error validasi per baris (`onError`).
- **Kriteria selesai:** menyimpan satu baris tidak mengubah isian baris lain; berpindah shift dengan data belum disimpan memunculkan konfirmasi.

### BUG-02 — Tanggal default form memakai UTC

- **File:**
  - `resources/js/Components/KwhProduction/KwhEngineTable.vue` (±309)
  - `resources/js/Components/KwhProduction/KwhFeederTable.vue` (±300)
  - `resources/js/Components/DisturbanceMonitoring/DisturbanceDataTable.vue` (±370, ±400)
  - `resources/js/Components/FuelStock/FuelStockDataTable.vue` (±430, ±468)
- **Penyebab:** `new Date().toISOString().split('T')[0]` menghasilkan tanggal UTC. Di WIB (UTC+7), pukul 00:00–06:59 hasilnya **tanggal kemarin**.
- **Dampak:** operator shift malam menyimpan ke tanggal salah; karena kWh & BBM memakai `updateOrCreate` berdasarkan tanggal, data kemarin bisa **tertimpa diam-diam**.
- **Saran perbaikan:** buat helper di `resources/js/utils/date.js`:
  ```js
  export const todayLocal = () => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  };
  ```
  lalu ganti semua pemakaian `toISOString().split('T')[0]`. (Cek juga `grep -rn "toISOString" resources/js`.)
- **Kriteria selesai:** dengan jam sistem 01:00 WIT (zona waktu lokasi ULPLTD Poka, Ambon), form terisi tanggal hari ini.

### BUG-03 — Modal menutup walau validasi gagal, error tidak tampil

- **File:**
  - `resources/js/Components/DisturbanceMonitoring/DisturbanceDataTable.vue` → `submitForm` (±431)
  - `resources/js/Components/FuelStock/FuelStockDataTable.vue` → `submitForm` (±498)
  - `resources/js/Pages/UserManagement/Index.vue` → `submitForm` (±567, ±579)
- **Penyebab:** `showModal.value = false` diletakkan di `onFinish` (dipanggil baik sukses maupun gagal). Ketiga file ini juga tidak menampilkan `errors` sama sekali.
- **Reproduksi:** buka modal tambah user, isi email yang sudah terdaftar → Simpan → modal tertutup, tidak ada pesan, data tidak tersimpan.
- **Saran perbaikan:**
  - Pindahkan penutupan modal ke `onSuccess`; `onFinish` hanya reset `isSubmitting`.
  - Tampilkan error (pola yang sudah ada di `KwhEngineTable.vue`: `usePage().props.errors`), idealnya per field.
  - Lebih baik: migrasi ke `useForm()` Inertia (`form.errors`, `form.processing`, `form.clearErrors()`).
- **Kriteria selesai:** input tidak valid → modal tetap terbuka, isian utuh, pesan error terlihat di field terkait.

### BUG-04 — Edit BBM dengan ubah tanggal membuat data duplikat

- **File:** `resources/js/Components/FuelStock/FuelStockDataTable.vue` (`openEditModal` ±475, input tanggal di modal); `app/Http/Controllers/FuelStockController.php` (`updateOrCreate` by `recorded_date` ±82)
- **Penyebab:** saat edit, input tanggal tidak dikunci; server melakukan upsert berdasarkan `recorded_date` tanpa `id`.
- **Reproduksi:** edit record 10/09 → ubah tanggal ke 11/09 → Simpan → record 10/09 tetap ada, muncul record baru 11/09.
- **Saran perbaikan:** tambahkan `:disabled="isEditing"` pada input tanggal (konsisten dengan tabel kWh).
- **Kriteria selesai:** tanggal tidak bisa diubah saat mode edit.

### BUG-05 — PBAC hanya di UI, tidak ditegakkan di server

- **File:** `routes/web.php`, `app/Http/Middleware/CheckPermission.php`, `bootstrap/app.php`, `app/Http/Controllers/UserManagementController.php` (`toggleStatus` ±147)
- **Penyebab:** middleware `CheckPermission` sudah ada tetapi **tidak dipakai di route mana pun**; semua route hanya `auth`. Sidebar menyembunyikan menu, tapi URL tetap bisa diakses.
- **Dampak:**
  - Operator dapat membuka `/users` via URL, membuat admin, menonaktifkan akun lain.
  - Tombol Tambah/Edit/Hapus/Simpan tampil untuk semua role (Manager yang seharusnya view-only bisa input).
  - Admin bisa menonaktifkan akunnya sendiri.
- **Saran perbaikan:**
  1. Daftarkan alias middleware (mis. `permission`) di `bootstrap/app.php` bila belum.
  2. Di `routes/web.php`: GET memakai `permission:<modul>.view`; POST/DELETE memakai `permission:<modul>.input` (gangguan: `monitoring_gangguan.manage`); `/users*` memakai `permission:users.manage`.
     Slug yang ada: `monitoring_arus.*`, `monitoring_kwh.*`, `monitoring_engine.*`, `monitoring_gangguan.view|manage`, `monitoring_bbm.*`, `users.manage`, `data.verify`. Pastikan admin selalu lolos (lihat `User::hasPermission`).
  3. Di `toggleStatus` & `update`: tolak menonaktifkan / menurunkan role akun sendiri.
  4. Di frontend: buat composable `usePermission()` → `can(slug)`; sembunyikan tombol aksi bila tidak punya izin `.input`/`.manage`.
- **Kriteria selesai:** login sebagai operator → `GET /users` = 403; login manager → tidak ada tombol tambah/edit/hapus dan POST langsung = 403.

---

## 🟠 Tinggi

### BUG-06 — Bottom nav mobile tidak konsisten dengan desktop

- **File:** `resources/js/Components/Mobile/BottomNav.vue`
- **Masalah:**
  - Menu tidak difilter izin (Sidebar memakai `hasPerm`, BottomNav tidak) → user menekan menu lalu kena 403.
  - Tidak ada menu **Pengelolaan Pengguna** → admin tidak bisa kelola user dari HP.
  - 6 item dengan label `text-[9px]` → sulit dibaca, target sentuh sempit.
- **Saran perbaikan:** gunakan `usePermission()`; tampilkan maks. 4 item utama + tombol **"Lainnya"** yang membuka bottom sheet (berisi menu sisa + Users + logout + toggle tema). Label minimal 10–11px.
- **Kriteria selesai:** menu mobile = menu desktop untuk setiap role; admin bisa buka Users dari HP.

### BUG-07 — Layout re-mount di setiap navigasi

- **File:** `resources/js/Layouts/AppLayout.vue`, `resources/js/app.js`, semua `Pages/*/Index.vue`, `resources/views/app.blade.php`
- **Penyebab:** tiap halaman membungkus `<AppLayout>` di template sendiri (bukan persistent layout), sehingga state layout dibuat ulang saat pindah halaman.
- **Dampak:**
  - Sidebar yang diciutkan kembali terbuka setiap pindah menu (`isSidebarCollapsed = ref(false)`, tidak disimpan).
  - Tema dibaca dari localStorage di `onMounted` → potensi kedip tema terang sesaat.
- **Saran perbaikan:**
  - Set layout default di `app.js` (`resolve` → `page.default.layout ??= AppLayout`), hapus pembungkus `<AppLayout>` di halaman. Catatan: halaman memakai `v-slot="{ isDarkMode }"` — ganti dengan `inject('isDarkMode')` atau (lebih baik) varian `dark:` (lihat BUG-11). Halaman Auth tidak memakai layout.
  - Simpan status collapse sidebar ke localStorage (dibungkus try/catch).
  - Tambah script inline di `<head>` blade untuk menerapkan class `dark` ke `<html>` sebelum render, dan pindahkan class `dark` dari div root layout ke `<html>`.
- **Kriteria selesai:** sidebar tetap tercutkan saat pindah menu & setelah reload; tidak ada kedip tema.

### BUG-08 — KPI Dashboard menyesatkan

- **File:** `resources/js/Pages/Dashboard/Index.vue`, `app/Http/Controllers/DashboardController.php`
- **Masalah & perbaikan:**
  | Masalah | Lokasi | Perbaikan |
  |---|---|---|
  | Status BBM **"Aman" saat HOP = 0** (tidak ada data / stok habis) | Controller, blok `$bmmStatus` | HOP ≤ 5 (termasuk 0) = Kritis; bila belum ada data → status "Belum Ada Data" |
  | Sub-teks gangguan hanya "Penanganan • Selesai", Investigasi tidak ditampilkan → angka tak cocok total | View KPI 3 | Tampilkan ketiga status |
  | Label "Shift {{ latest_interval }}" padahal isinya jam (mis. "Shift 09.30") | View KPI 1 | Ubah jadi "Update terakhir 09.30" |
  | Titik status feeder selalu hijau (hardcoded `bg-emerald-400`) | View grid feeder | Warna berdasarkan data (abu = belum ada data, hijau = ada) |
  | Kartu KPI tetap link ke halaman tanpa izin | View | Sembunyikan/nonaktifkan link dengan `can()` |
  | Prop `todayDateFormatted` dikirim tapi tidak ditampilkan | View | Tampilkan di header dashboard |
  | Chart arus: x-axis urut berdasarkan insert, bisa acak | Controller `$uniqueIntervals` | `->sort()->values()` |
  | "Gangguan Terbaru" hanya bulan ini → kosong di awal bulan | Controller | Ambil 5 terakhir tanpa filter bulan |

### BUG-09 — Tidak ada judul halaman, `<title>` statis, jam header mati

- **File:** `resources/views/app.blade.php` (±baris 6), `resources/js/Components/Desktop/Header.vue`, semua Pages
- **Masalah:**
  - `<title>` hardcoded "PLN Monitoring - Monitoring Arus" dan tidak ada `<Head>` di halaman mana pun → semua tab browser berjudul sama.
  - Header tidak menampilkan judul halaman/breadcrumb (terutama di mobile, user tidak tahu posisi).
  - `Header.vue` menjalankan `setInterval` tiap detik untuk `currentTime` yang **tidak pernah ditampilkan**; import `Menu`, `Clock` dan emit `openMobileSidebar` tidak dipakai.
- **Saran perbaikan:** tambah `<Head title="..." />` di tiap halaman; hapus `<title>` statis di blade; tampilkan jam+tanggal di header (berguna untuk operator shift) atau hapus timer-nya.

### BUG-10 — Flash sukses tidak terlihat

- **File:** flash diduplikasi di `Pages/CurrentMonitoring`, `KwhProduction`, `EngineOperation`, `DisturbanceMonitoring`, `FuelStock`, `UserManagement`
- **Masalah:** flash hanya di atas halaman, sedangkan simpan memakai `preserveScroll` → operator yang menyimpan di baris bawah tidak melihat konfirmasi. Flash juga tidak bisa ditutup dan tidak hilang otomatis.
- **Saran perbaikan:** buat `Components/Shared/Toast.vue` di AppLayout (posisi fixed, auto-dismiss ±4 dtk, tombol tutup, juga untuk error); hapus blok flash per halaman.

---

## 🟡 Sedang

### BUG-11 — Warna dark mode bocor ke light mode

| Lokasi | Masalah |
|---|---|
| `Pages/Dashboard/Index.vue` tombol "Lihat Semua Log Gangguan" (±336) | Selalu `bg-slate-800` |
| `Pages/Dashboard/Index.vue` deskripsi gangguan (±323) | `text-slate-300` di latar terang, nyaris tak terbaca |
| `Pages/Dashboard/Index.vue` border header kartu | `border-slate-700/40` gelap di mode terang |
| `Pages/Dashboard/Index.vue` badge status BBM | `text-emerald-400` / `text-amber-400` di latar putih |
| `Pages/UserManagement/Index.vue` | Email `text-slate-300`; badge role `text-*-400` |
| Modal Gangguan / BBM / Users | Tombol Batal `border-slate-700 hover:bg-slate-800`; tombol X `hover:text-white` hilang di latar putih |
| Kartu mobile Gangguan / BBM | Box "Last Modified" hardcoded `bg-slate-900/40 border-slate-800` |

- **Akar masalah:** styling tema ditulis manual via ternary `isDarkMode ? '...' : '...'` di setiap file; sebagian lupa.
- **Saran perbaikan:** gunakan varian `dark:` Tailwind (sudah dikonfigurasi) dan komponen bersama (BUG-13), bukan ternary.

### BUG-12 — Class `slate-850` tidak ada

- **File:** `AdaptiveDataTable.vue`, `DisturbanceDataTable.vue`, `FuelStockDataTable.vue`, `Pages/UserManagement/Index.vue` (`grep -rn "slate-850" resources/js`)
- **Dampak:** efek hover baris tabel di dark mode tidak jalan.
- **Perbaikan:** ganti ke `slate-800/50` (atau definisikan warna di `@theme`).

### BUG-13 — Modal tidak konsisten & kurang aksesibel

- **File:** semua modal di `KwhEngineTable`, `KwhFeederTable`, `ControlPanelTable`, `EngineAreaTable`, `DisturbanceDataTable`, `FuelStockDataTable`, `UserManagement/Index`
- **Masalah:**
  - Tidak bisa ditutup dengan Esc / klik backdrop; tidak ada focus awal / focus trap; tidak ada `role="dialog"`.
  - Modal Gangguan & BBM tanpa `max-h-[90vh] overflow-y-auto` → di HP saat keyboard muncul, tombol Simpan terpotong.
  - Error lama (`page.props.errors`) masih tampil saat modal dibuka ulang.
  - Tombol submit disabled tanpa gaya disabled (bisa double-submit secara visual).
  - `<label>` tidak terhubung ke input (tidak ada `for`/`id`).
- **Saran perbaikan:** buat komponen bersama di `Components/Shared/`:
  - `Modal.vue` (Esc, klik backdrop, focus, scroll, slot header/footer, Teleport ke body)
  - `FormField.vue` (label + input + pesan error, id otomatis)
  - `Button.vue` (primary / secondary / danger, state loading & disabled)
  lalu migrasikan semua modal.

### BUG-14 — Tabel input Monitoring Arus berat & angka hardcoded

- **File:** `AdaptiveDataTable.vue`, `InteractiveLineChart.vue`, `Desktop/Sidebar.vue`
- **Masalah:**
  - Kolom **Operator diketik manual di setiap baris** (16 baris/shift). Form kWh sudah memakai fallback nama akun — terapkan hal yang sama (default = nama user login, di server juga).
  - Teks hardcoded: "12 Feeder" (Sidebar badge), "16 Interval", "Input nilai Ampere untuk 12 jalur", "Semua Feeder (12)" → gunakan `feeders.length` / `matrix.length`.
  - Label sumbu Y grafik `${val} A` tanpa pembulatan → desimal panjang. Gunakan `toLocaleString('id-ID', { maximumFractionDigits: 1 })`.
  - Tombol Simpan mobile disabled tanpa gaya disabled.

### BUG-15 — Target sentuh mobile terlalu kecil

- **File:** kartu mobile di `KwhEngineTable`, `KwhFeederTable`, `ControlPanelTable`, `EngineAreaTable`, `DisturbanceDataTable`, `FuelStockDataTable`
- **Masalah:** tombol Edit/Hapus `p-1` + ikon 16px (≈24px), di bawah standar 44px; Hapus bersebelahan langsung dengan Edit → rawan salah tekan.
- **Perbaikan:** minimal `p-2.5` (≈40–44px), beri jarak, atau pindahkan Hapus ke dalam modal edit / menu aksi.

### BUG-16 — User Management tanpa tampilan mobile

- **File:** `resources/js/Pages/UserManagement/Index.vue`
- **Masalah:** hanya tabel 6 kolom dengan `overflow-x-auto`; kolom Aksi keluar layar di HP.
- **Perbaikan:** tambah tampilan kartu `md:hidden` seperti halaman lain.

---

## 🟢 Rendah

### BUG-17 — Kumpulan isu minor

- [ ] `app.blade.php`: `maximum-scale=1.0` memblokir zoom (aksesibilitas) → hapus.
- [ ] `Pages/Auth/Login.vue`: tombol "Uji Coba Cepat 5 Peran" mengisi password `password` → tampilkan hanya di environment lokal (kirim flag dari server via shared props).
- [ ] `Pages/Auth/Login.vue`: tombol submit disabled tanpa gaya disabled; error ditampilkan di atas, bukan di field.
- [x] `Pages/UserManagement/Index.vue` (±509): user baru diisi password `password` secara diam-diam (ter-mask) → kosongkan, wajib diisi.
- [x] `Pages/UserManagement/Index.vue`: pencarian mengirim request tiap ketikan → debounce ±300ms.
- [x] `Pages/UserManagement/Index.vue`: toggle Aktif/Nonaktif tanpa konfirmasi.
- [ ] `Desktop/Sidebar.vue`: semua ikon `text-cyan-500` padahal state aktif berwarna per modul → samakan warna ikon dengan modul (atau netral).
- [ ] `KwhProduction/KwhBarLineChart.vue`: tidak ada empty state saat data kosong (Dashboard punya).
- [x] Form BBM & Gangguan diawali angka `0` → pakai `null` + placeholder.
- [ ] `ControlPanelTable.vue` / `EngineAreaTable.vue`: jam default memakai menit saat ini (mis. 08:07) → bulatkan ke jam/setengah jam terdekat.
- [ ] Setelah menyimpan data untuk tanggal/bulan di luar filter aktif, data "menghilang" → pindahkan filter ke tanggal tersebut atau tampilkan info.
- [ ] `Shared/ChoiceValueInput.vue` (kWh Penyulang): pola pilih-lalu-isi menyembunyikan field; bila pilihan sedikit, tampilkan semua field langsung.

---

## Rencana Pengerjaan

### Langkah 0 — Persiapan
- [x] Commit semua perubahan yang ada, buat branch `fix/ux-review`.

### Fase 1 — Bug data & keamanan (tanpa perubahan tampilan)
- [x] BUG-05 Pasang middleware permission di route + cegah self-deactivate *(server `6c6cf8c`; tombol aksi per halaman di Fase 3)*
- [x] BUG-02 Helper `todayLocal()` dan ganti semua `toISOString()`
- [x] BUG-03 Modal tutup di `onSuccess` + tampilkan error
- [x] BUG-04 Kunci tanggal saat edit BBM
- [x] BUG-08 (backend) Status BBM HOP=0, urutan interval chart, gangguan terbaru
- [x] **Tes otomatis:** feature test akses per role, validasi form, simpan BBM berbasis `id`, KPI dashboard (`tests/Feature/Phase1BugFixTest.php`) + `npm run build`

### Fase 2 — Fondasi
- [x] BUG-07 Persistent layout + simpan status sidebar + tema di `<html>`
- [x] BUG-11/12 Beralih ke varian `dark:`; hapus `slate-850`
- [x] BUG-13 Komponen `Modal`, `FormField`, `Button`
- [x] BUG-10 `Toast` global, hapus flash per halaman
- [x] Composable `usePermission()` (dipakai Sidebar, BottomNav, tombol aksi)

### Fase 3 — Migrasi per halaman (1 halaman = 1 commit, verifikasi lewat build, tes otomatis & pemeriksaan kode)
- [x] Monitoring Arus — BUG-01, BUG-14
- [x] kWh Produksi — BUG-13, BUG-15
- [x] Operasi Engine — BUG-13, BUG-15
- [x] Gangguan — BUG-11, BUG-13, BUG-15
- [x] BBM — BUG-11, BUG-13, BUG-15
- [x] User Management — BUG-11, BUG-16, sebagian BUG-17
- [x] Dashboard — BUG-08 (frontend), BUG-11

### Fase 4 — Navigasi & polish
- [ ] BUG-06 Bottom nav 4 item + "Lainnya", filter izin
- [ ] BUG-09 `<Head>` per halaman, judul di header, bersihkan Header.vue
- [ ] BUG-17 Sisa isu minor
