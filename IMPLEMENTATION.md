# Implementation Plan — Sistem Warna, Tipografi & Redesign Dashboard

> Proyek: PLN Monitor ULPLTD Poka
> Dibuat: 2026-10-11
> Stack: Laravel + Inertia (Vue 3 `<script setup>`) + Tailwind v4 (`@theme` di `resources/css/app.css`, dark mode class-based) + ApexCharts
> Dokumen terkait: [UX_BUG_REPORT.md](UX_BUG_REPORT.md), [UX_BUG_SOLVED.md](UX_BUG_SOLVED.md)

## 1. Tujuan

Menghilangkan kesan "template AI" pada tampilan dan membuat dashboard menjawab tiga pertanyaan user dalam beberapa detik:

1. **Ada yang gawat?** (mis. stok BBM kritis)
2. **Apa yang harus saya kerjakan sekarang?** (input mana yang belum diisi di shift berjalan)
3. **Datanya sudah lengkap?** (kosong harus terbaca kosong, bukan 0)

Caranya: kunci warna dan tipografi sebagai **design token**, lalu bangun ulang dashboard di atas token tersebut.

## 2. Prinsip desain (aturan yang tidak boleh dilanggar)

| # | Prinsip | Artinya di kode |
|---|---|---|
| P1 | **Satu warna = satu makna** | Warna hanya dari token semantik (`brand`, `status-*`, netral). Tidak ada `text-rose-500`, `text-cyan-500`, dst. di komponen. |
| P2 | **Modul tidak punya warna** | Ikon sidebar, ikon kartu, menu aktif dan tab memakai warna yang sama (netral / brand). |
| P3 | **Merah hanya untuk kritis** | Jika tidak ada data kritis, tidak ada merah di layar, termasuk ikon modul Gangguan. |
| P4 | **Kosong ≠ nol** | Data yang belum diinput ditampilkan `—` + keterangan "Belum diinput". Angka 0 hanya untuk data yang ada dan bernilai nol. |
| P5 | **Hierarki lewat ukuran & warna, bukan ketebalan** | Hanya bobot 400 dan 600. Tidak ada `font-bold`, `font-extrabold`, `font-black`. |
| P6 | **Kalimat biasa, bukan kapital** | Tidak ada `uppercase tracking-wider` pada label. Kapital hanya untuk singkatan (BBM, HOP, WIT, PS). |
| P7 | **Minimal 12px** | Tidak ada `text-[9px]`, `text-[10px]`, `text-[11px]`. |
| P8 | **Angka rata & berformat Indonesia** | `tabular-nums` untuk angka; semua angka lewat `utils/format.js` (koma desimal, titik ribuan). |
| P9 | **Alat kerja, bukan landing page** | Radius `rounded-lg`, border tanpa bayangan, padding lebih rapat, tanpa efek hover pada elemen yang tidak bisa diklik. |

## 3. Kondisi awal (baseline untuk verifikasi)

Diukur 2026-10-11 dengan `grep -rE "<pola>" resources/js --include=*.vue | wc -l`:

| Pola | Jumlah | Target akhir |
|---|---|---|
| `isDarkMode ?` (ternary tema) | 40 | 0 |
| `font-black` | 17 | 0 |
| `font-extrabold` | 39 | 0 |
| `uppercase tracking-wider` | 26 | 0 |
| `text-[9px]` | 6 | 0 |
| `text-[10px]` | 76 | 0 |
| `text-[11px]` | 28 | 0 |
| `font-mono` | 109 | hanya untuk kode/ID (NIP, kode feeder) |
| `rounded-2xl` | 48 | 0 (pakai `rounded-lg`) |
| `shadow-` | 93 | hanya untuk elemen melayang (modal, toast, dropdown) |
| `-500/10` (kotak ikon pastel) | 57 | 0 |

Sudah tersedia dan **dipakai ulang** (jangan dibuat ulang):
- `resources/js/Components/Shared/`: `Button.vue`, `Modal.vue`, `FormField.vue`, `Toast.vue`, `RowActions.vue`, `ChoiceValueInput.vue`
- `resources/js/composables/usePermission.js`
- `resources/js/utils/format.js` (`formatDate`, `formatNumber`), `date.js`, `url.js`, `currentMatrix.js`

Bug yang terlihat dan ikut diperbaiki:
- `Pages/Dashboard/Index.vue` ±baris 124: `kpi.fuel.days_of_supply` dirender mentah → tampil "0.17" (titik), seharusnya "0,17".
- Judul ganda: header "Dashboard Utama" + blok halaman "Dashboard Operasional" beserta subjudul.
- Status BBM dikirim sebagai teks ("Aman"/"Waspada"/"Kritis") dan dipetakan ke warna lewat `switch` teks di template (±baris 389).

## 4. Fase pengerjaan

Kerjakan berurutan. **Satu fase = satu commit** (fase besar boleh dipecah per halaman). Setiap fase punya verifikasi yang bisa diulang (lihat §6). Tidak memakai uji browser.

---

### Fase A — Token warna

**File:** `resources/css/app.css`

1. Definisikan variabel warna mentah di `:root` (terang) dan `.dark` (gelap), lalu petakan ke token Tailwind dengan `@theme inline` supaya kelas seperti `bg-surface` otomatis berganti saat class `dark` aktif:

```css
@import 'tailwindcss';
@variant dark (&:where(.dark, .dark *));

:root {
  /* Netral */
  --bg:            #f6f7f9;
  --surface:       #ffffff;
  --surface-muted: #f1f3f5;
  --border:        #e3e6ea;
  --text:          #15181d;
  --text-muted:    #5b636e;   /* ≥ 4.5:1 di atas --surface */
  --text-subtle:   #858d98;   /* hanya untuk placeholder/ikon */

  /* Brand (biru PLN) — GANTI dengan hex resmi dari brand guideline PLN */
  --brand:         #0a66b2;
  --brand-hover:   #08548f;
  --brand-soft:    #e8f1f9;
  --brand-text:    #0a5799;

  /* Status */
  --ok:            #1f7a4d;   --ok-soft:   #e7f4ec;
  --warn:          #9a5b00;   --warn-soft: #fdf3e1;
  --crit:          #b42318;   --crit-soft: #fdecea;
  --empty:         #5f6670;   --empty-soft:#f1f3f5;

  /* Teks di atas tombol berwarna solid (brand / crit) */
  --on-solid:      #ffffff;
}

.dark {
  --bg:            #0f1115;
  --surface:       #161a20;
  --surface-muted: #1d222a;
  --border:        #2a3039;
  --text:          #e8eaed;
  --text-muted:    #a3aab4;
  --text-subtle:   #6f7782;

  --brand:         #4c9be0;
  --brand-hover:   #6aaee8;
  --brand-soft:    #13283d;
  --brand-text:    #8cc0ef;

  --ok:            #5cc38f;   --ok-soft:   #13291e;
  --warn:          #e0a84a;   --warn-soft: #2e2412;
  --crit:          #f07a6e;   --crit-soft: #321715;
  --empty:         #9aa1ab;   --empty-soft:#1d222a;

  /* Di mode gelap brand & crit terang, jadi teks tombol harus gelap */
  --on-solid:      #0f1115;
}

@theme inline {
  --color-bg: var(--bg);
  --color-surface: var(--surface);
  --color-surface-muted: var(--surface-muted);
  --color-line: var(--border);
  --color-fg: var(--text);
  --color-fg-muted: var(--text-muted);
  --color-fg-subtle: var(--text-subtle);

  --color-brand: var(--brand);
  --color-brand-hover: var(--brand-hover);
  --color-brand-soft: var(--brand-soft);
  --color-brand-text: var(--brand-text);

  --color-ok: var(--ok);       --color-ok-soft: var(--ok-soft);
  --color-warn: var(--warn);   --color-warn-soft: var(--warn-soft);
  --color-crit: var(--crit);   --color-crit-soft: var(--crit-soft);
  --color-empty: var(--empty); --color-empty-soft: var(--empty-soft);
  --color-on-solid: var(--on-solid);
}
```

2. `body` memakai `bg-bg text-fg`. Scrollbar memakai `var(--border)` / `var(--text-subtle)`; hapus pasangan warna `.dark ::-webkit-scrollbar-*` yang hardcoded.
3. Hapus `transition` warna global pada `body` (menyebabkan semua elemen "berkedip" saat ganti tema).
4. Buat `resources/js/utils/chartTheme.js`: ApexCharts tidak membaca kelas Tailwind, jadi baca token lewat `getComputedStyle(document.documentElement).getPropertyValue('--text-muted')` dst., dan sediakan **palet kategorikal khusus grafik** (6–8 warna) yang hanya dipakai di dalam chart.

**Kriteria selesai:**
- Semua pasangan teks/latar netral & status di atas lolos kontras **WCAG AA 4,5:1**. Nilai usulan di atas sudah dihitung (lihat §7); jika hex brand diganti dengan warna resmi PLN, hitung ulang pasangan `brand-text`/`surface` dan `on-solid`/`brand`.
- `npm run build` sukses.

---

### Fase B — Token tipografi

**File:** `resources/css/app.css`, `resources/views/app.blade.php`

1. Tambahkan skala di `@theme`:

```css
@theme {
  --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;

  --text-page:    1.25rem;   --text-page--line-height: 1.75rem;    /* 20/28 judul halaman */
  --text-section: 0.9375rem; --text-section--line-height: 1.375rem;/* 15/22 judul seksi */
  --text-kpi:     1.75rem;   --text-kpi--line-height: 2rem;        /* 28/32 nilai KPI */
  --text-body:    0.875rem;  --text-body--line-height: 1.25rem;    /* 14/20 isi & tabel */
  --text-label:   0.8125rem; --text-label--line-height: 1.125rem;  /* 13/18 label */
  --text-caption: 0.75rem;   --text-caption--line-height: 1rem;    /* 12/16 minimum */
}
```

2. Aturan pemakaian:

| Peran | Kelas | Bobot | Warna |
|---|---|---|---|
| Judul halaman | `text-page` | `font-semibold` | `text-fg` |
| Judul seksi/kartu | `text-section` | `font-semibold` | `text-fg` |
| Nilai KPI | `text-kpi tabular-nums` | `font-semibold` | `text-fg` (warna status hanya lewat badge/ikon status) |
| Satuan di samping nilai | `text-body` | `font-normal` | `text-fg-muted` |
| Label | `text-label` | `font-medium` | `text-fg-muted` |
| Isi & sel tabel | `text-body` (+ `tabular-nums` untuk kolom angka) | `font-normal` | `text-fg` |
| Keterangan/audit trail | `text-caption` | `font-normal` | `text-fg-muted` |

3. Hanya 3 bobot yang boleh dipakai: `font-normal` (400), `font-medium` (500, khusus label), `font-semibold` (600). Muat hanya bobot itu di Google Fonts (`wght@400;500;600`), bukan `100..900`.
4. `font-mono` hanya untuk NIP, kode feeder, dan ID. Jam, tanggal, dan angka memakai `font-sans` + `tabular-nums`.

**Kriteria selesai:** `npm run build` sukses; URL Google Fonts di blade hanya memuat 400/500/600.

---

### Fase C — Sistem status (backend → frontend)

**Masalah:** status dikirim sebagai teks Indonesia dan warnanya diputuskan per halaman dengan `switch` teks.

1. **Backend** — kirim kode status, bukan teks tampilan. Buat `app/Support/Status.php` (atau enum `app/Enums/Status.php`) dengan nilai `ok | warning | critical | empty`.
   - `DashboardController`: `kpi.fuel.status` → kode (`critical` untuk HOP ≤ 5 termasuk 0, `warning` ≤ 10, `ok` selain itu, `empty` jika tidak ada data). Tambahkan `kpi.fuel.status_label` jika perlu teks spesifik.
   - Tambahkan `has_data` (boolean) untuk `kpi.current` dan `kpi.kwh`, supaya frontend bisa membedakan kosong vs nol (P4).
2. **Frontend** — `resources/js/utils/status.js`:

```js
export const STATUS = {
  ok:       { label: 'Normal',         text: 'text-ok',    soft: 'bg-ok-soft' },
  warning:  { label: 'Waspada',        text: 'text-warn',  soft: 'bg-warn-soft' },
  critical: { label: 'Kritis',         text: 'text-crit',  soft: 'bg-crit-soft' },
  empty:    { label: 'Belum ada data', text: 'text-empty', soft: 'bg-empty-soft' },
};
```

3. Komponen `Components/Shared/StatusBadge.vue` (`status`, `label?`): latar `*-soft`, teks token status, tanpa border berwarna, `text-caption font-medium`.
4. Ganti `statusClass` / `switch` teks di `Pages/Dashboard/Index.vue` dan `Pages/UserManagement/Index.vue`, serta badge status Gangguan di `DisturbanceDataTable.vue`, dengan `StatusBadge`.

**Kriteria selesai:**
- Feature test: `kpi.fuel.status` bernilai salah satu dari `ok|warning|critical|empty` untuk skenario tanpa data, HOP 0, HOP 7, HOP 20.
- `grep -rn "'Kritis'\|'Waspada'\|'Aman'" resources/js` → 0 hasil.

---

### Fase D — Komponen bersama memakai token

**File:** `Components/Shared/{Button,Modal,FormField,Toast,RowActions}.vue`

- Ganti semua kelas warna dan `isDarkMode ?` di komponen bersama dengan token (`bg-surface`, `border-line`, `text-fg`, `bg-brand`, dst.).
- `Button`: varian `primary` = `bg-brand text-on-solid hover:bg-brand-hover`; `secondary` = `bg-surface border-line text-fg`; `danger` = `bg-crit text-on-solid`. **Jangan pakai `text-white`** di tombol solid: di mode gelap kontrasnya hanya ±2,9:1. Radius `rounded-lg`, tinggi 36px (desktop) / 44px (mobile, `min-h-11`).
- `Modal`, `Toast`: satu-satunya tempat yang boleh memakai `shadow-*`.
- `FormField`: label `text-label font-medium text-fg-muted`, error `text-caption text-crit`.
- Buat **`Components/Shared/Card.vue`**: `bg-surface border border-line rounded-lg` + slot `header` (judul `text-section`) — pengganti pola kartu yang di-copy-paste di setiap halaman. Tanpa kotak ikon di header.

**Kriteria selesai:** `grep -rE "isDarkMode \?|rounded-2xl|font-(bold|extrabold|black)" resources/js/Components/Shared` → 0 hasil; `npm run build` sukses.

---

### Fase E — Kerangka layout (Sidebar & Header)

**File:** `Components/Desktop/Sidebar.vue`, `Components/Desktop/Header.vue`, `Components/Mobile/BottomNav.vue`, `Layouts/AppLayout.vue`, `app/Http/Middleware/HandleInertiaRequests.php`

**Sidebar**
- Semua ikon monokrom `text-fg-muted`; menu aktif: `bg-brand-soft text-brand-text` + garis kiri 3px `bg-brand`. Tanpa border, tanpa gradient, tanpa shadow.
- Hapus badge "12 Feeder" dan "PBAC". Label "Pengelolaan Pengguna" → **"Pengguna"** (tidak terpotong).
- Judul grup ("Monitoring Utama", "Sistem Admin") → kalimat biasa `text-caption text-fg-subtle`, atau hapus jika tidak menambah makna.
- Logo: ganti kotak petir bergradasi dengan logo PLN resmi (aset SVG di `public/images/`) atau monogram satu warna `bg-brand`. Teks "PLN Monitor" `font-semibold`, "ULPLTD Poka" `text-caption text-fg-muted`.

**Header** — satu-satunya tempat judul & konteks waktu:
- Kiri: judul halaman (`text-page font-semibold`).
- Kanan, satu kelompok teks netral: `Senin, 12 Okt 2026 · 01.08 WIT · Shift Malam`. Jam tanpa `font-mono`, tanpa kotak/pill.
- Shift berjalan dihitung di server: tambahkan shared prop `currentShift` (`{ key, label }`) di `HandleInertiaRequests` berdasarkan `CurrentMonitoringController::SHIFT_INTERVALS` dan zona waktu aplikasi.
- Tombol tema & logout: ikon netral `text-fg-muted`, tanpa latar berwarna; logout tidak berwarna merah (merah hanya untuk kritis, P3).
- Badge role: `StatusBadge`-like netral (`bg-surface-muted text-fg-muted`), bukan warna per role.

**Kriteria selesai:**
- `grep -n "12 Feeder\|PBAC\|from-cyan\|to-blue" resources/js/Components` → 0 hasil.
- Feature test: shared prop `currentShift` ada dan bernilai benar untuk jam 09.00, 17.00 dan 01.00 (pakai `Carbon::setTestNow`).
- `npm run build` sukses.

---

### Fase F — Redesign Dashboard

**File:** `Pages/Dashboard/Index.vue`, `app/Http/Controllers/DashboardController.php`, komponen baru di `Components/Dashboard/`

Tata letak target (dari atas ke bawah):

```
Header: Dashboard · Senin, 12 Okt 2026 · 01.08 WIT · Shift Malam

▌ Stok BBM kritis: 0,17 hari (±4 jam) · netto 2 L        [Input BBM →]   ← StatusBanner, hanya jika ada warning/critical

Beban arus      kWh hari ini     Gangguan bulan ini     Stok BBM          ← KpiStrip (satu kartu, 4 kolom dipisah garis)
—               —                0                      0,17 hari
Belum diinput   Belum diinput    Tidak ada gangguan     [Kritis]

Kelengkapan input · Shift Malam                                           ← InputProgress (baru)
Arus     ▮▮▮▯▯▯▯▯▯▯▯▯▯▯▯▯  3/16 interval        [Lanjutkan →]
kWh      Belum diisi                             [Input →]
Engine   1 catatan hari ini
BBM      Terakhir diisi 11/10/2026

[Grafik arus hari ini]  [Produksi kWh 7 hari]                             ← tampil penuh hanya jika ada data
[Status feeder]         [Gangguan terbaru]
```

Langkah:

1. **Hapus** blok judul "Dashboard Operasional" + subjudul + tanggal di halaman (pindah ke Header, Fase E).
2. **`Components/Dashboard/StatusBanner.vue`** (`status`, `title`, `detail`, `href?`, `actionLabel?`): latar `*-soft`, garis kiri 3px warna status, teks `text-fg`. Dashboard merender banner untuk setiap KPI berstatus `warning`/`critical` (saat ini: BBM). Link aksi hanya jika `can('<modul>.input')`.
3. **`Components/Dashboard/KpiStrip.vue`** + **`KpiStat.vue`** (`label`, `value`, `unit`, `hint`, `status`, `href?`):
   - Satu `Card` dengan 4 kolom (`grid-cols-2 lg:grid-cols-4`, pemisah `divide-x divide-line`), **tanpa ikon**.
   - `value === null` → tampil `—` dan hint "Belum diinput" (P4).
   - Satuan `text-fg-muted`; status hanya lewat `StatusBadge` di baris hint.
   - Seluruh kolom bisa diklik (ke halaman modul) hanya jika `can('<modul>.view')`; tanpa efek hover warna, cukup `hover:bg-surface-muted`.
   - Gangguan: hint "Tidak ada gangguan" jika 0; jika ada, "2 ditangani · 1 investigasi · 3 selesai".
4. **Perbaiki format angka:** semua nilai lewat `formatNumber` / helper baru `formatDays()` di `utils/format.js` (`0.17` → `0,17`; tampilkan juga perkiraan jam jika < 1 hari: "±4 jam").
5. **`Components/Dashboard/InputProgress.vue`** + prop baru `inputProgress` dari controller:

```php
'inputProgress' => [
    'shift' => ['key' => 'malam', 'label' => 'Shift Malam'],
    'items' => [
        ['module' => 'arus',   'label' => 'Arus',   'filled' => 3, 'total' => 16, 'unit' => 'interval',
         'href' => '/monitoring-arus?date=2026-10-12&shift=malam', 'permission' => 'monitoring_arus.input'],
        ['module' => 'kwh',    'label' => 'kWh',    'filled' => 0, 'total' => <jumlah engine + penyulang>, 'unit' => 'data', ...],
        ['module' => 'engine', 'label' => 'Engine', 'filled' => <jumlah catatan hari ini>, 'total' => null, ...],
        ['module' => 'bbm',    'label' => 'BBM',    'filled' => 0|1, 'total' => 1, 'last_date' => '11/10/2026', ...],
    ],
],
```
   - Arus: interval shift berjalan yang **semua** feeder aktifnya terisi, dibanding jumlah interval shift.
   - `total = null` → tampil jumlah saja, tanpa progress bar.
   - Progress bar `bg-brand` di atas `bg-surface-muted`; lengkap → teks "Lengkap" `text-ok`. Tombol aksi hanya jika user punya izin `permission`.
   - Definisi `total` untuk kWh & Engine **dikonfirmasi dulu ke user/operator** sebelum diimplementasi (catat keputusan di §7).
6. **Grafik:**
   - Jika data kosong: jangan render kartu 280px; cukup satu baris di dalam `Card` ("Belum ada data arus hari ini") setinggi ±64px.
   - Warna & teks chart dari `utils/chartTheme.js` (Fase A); hapus ikon di header kartu grafik dan subjudul yang hanya mengulang judul.
7. **Status feeder:** grid kecil netral; titik status memakai `ok`/`empty` dari data (`has_data`), angka `tabular-nums`, tanpa warna cyan.
8. **Gangguan terbaru:** daftar baris (bukan kartu bertumpuk), `StatusBadge` untuk status, deskripsi `text-fg-muted` tanpa italic dan tanpa tanda kutip.

**Kriteria selesai:**
- Feature test `DashboardTest`: struktur prop `kpi.*.has_data`, `kpi.fuel.status`, dan `inputProgress` (shift, items, filled/total) untuk skenario: tanpa data sama sekali; arus 3 dari 16 interval terisi; BBM HOP 0,17.
- `grep -nE "uppercase|font-(bold|extrabold|black)|rounded-2xl|isDarkMode \?|text-\[(9|10|11)px\]|italic" resources/js/Pages/Dashboard/Index.vue resources/js/Components/Dashboard/*.vue` → 0 hasil.
- `grep -n "days_of_supply }}" resources/js/Pages/Dashboard/Index.vue` → 0 hasil (selalu lewat formatter).
- `npm run build` sukses.

---

### Fase G — Sapu bersih halaman lain

Terapkan aturan §2 ke semua halaman, **satu halaman per commit**, urutan: Monitoring Arus → kWh → Operasi Engine → Gangguan → BBM → Pengguna → Login/Lupa Password.

Per halaman:
- Ganti pola kartu manual dengan `Card`; ganti ternary `isDarkMode ?` dengan token.
- Hapus kotak ikon pastel (`-500/10`) di header kartu dan judul halaman; judul halaman cukup di Header (Fase E).
- Tab (ENGINE/PENYULANG, Control Panel/Engine Area, Beban/Fasa, shift): segmented control netral, aktif = `bg-surface text-fg` + border, **bukan** warna modul. Label tab kalimat biasa ("Engine", "Penyulang").
- Tabel: header `text-label font-medium text-fg-muted` tanpa uppercase; sel angka `tabular-nums text-right`; audit trail `text-caption text-fg-muted` tanpa `font-mono`.
- Login: hapus gradient; tombol `primary` brand; judul sesuai skala.

**Kriteria selesai (seluruh `resources/js`):** semua angka di tabel baseline (§3) mencapai target; `npm run build` dan `php artisan test` sukses.

## 5. Di luar cakupan

- Fitur baru selain panel Kelengkapan input.
- Perubahan alur input (form, validasi) yang sudah diperbaiki di UX_BUG_REPORT.
- Pergantian library chart.

## 6. Cara verifikasi (tanpa uji browser)

Setiap fase dibuktikan dengan:

1. **Perintah build & tes**
   ```bash
   npm run build
   php artisan test
   ```
2. **Metrik grep** — jalankan skrip ini dan bandingkan dengan baseline §3:
   ```bash
   for p in "isDarkMode ?" "font-black" "font-extrabold" "font-bold" "uppercase tracking-wider" "text-\[9px\]" "text-\[10px\]" "text-\[11px\]" "font-mono" "rounded-2xl" "shadow-" "\-500/10" "text-(rose|cyan|emerald|amber|violet|purple|blue)-[0-9]"; do
     printf "%-45s %s\n" "$p" "$(grep -rE -- "$p" resources/js --include=*.vue | wc -l)"
   done
   ```
3. **Kontras** — hitung rasio kontras setiap pasangan token (teks di atas `surface`/`bg`/`*-soft`, terang & gelap) dengan skrip kecil (rumus relative luminance WCAG) dan catat hasilnya di §7.
4. **Feature test** untuk setiap perubahan prop dari controller (Fase C, E, F).

## 7. Pelacakan

Status: ⬜ Belum · 🔄 Dikerjakan · ✅ Selesai · ⚠️ Sebagian · ⏭️ Ditunda. Status ✅ hanya jika kolom Bukti terisi (commit + hasil perintah/tes).

| Fase | Isi | Status | Commit | Bukti (perintah → hasil) |
|---|---|---|---|---|
| A | Token warna + `chartTheme.js` + cek kontras | ⬜ | – | – |
| B | Token tipografi + bobot font | ⬜ | – | – |
| C | Sistem status (kode backend, `status.js`, `StatusBadge`) | ⬜ | – | – |
| D | Komponen bersama memakai token + `Card` | ⬜ | – | – |
| E | Sidebar, Header (shift berjalan), BottomNav | ⬜ | – | – |
| F | Redesign Dashboard + `inputProgress` | ⬜ | – | – |
| G1 | Monitoring Arus | ⬜ | – | – |
| G2 | kWh Produksi | ⬜ | – | – |
| G3 | Operasi Engine | ⬜ | – | – |
| G4 | Gangguan | ⬜ | – | – |
| G5 | BBM | ⬜ | – | – |
| G6 | Pengguna | ⬜ | – | – |
| G7 | Login & Lupa Password | ⬜ | – | – |

### Hasil cek kontras

| Pasangan | Terang | Gelap | Lolos AA (≥ 4,5)? |
|---|---|---|---|
| `fg` / `surface` | 17,79 | 14,49 | ✅ |
| `fg-muted` / `surface` | 6,08 | 7,45 | ✅ |
| `fg-muted` / `bg` | 5,67 | 8,07 | ✅ |
| `brand-text` / `surface` | 7,40 | 9,06 | ✅ |
| `ok` / `ok-soft` | 4,70 | 7,08 | ✅ |
| `warn` / `warn-soft` | 4,93 | 7,17 | ✅ |
| `crit` / `crit-soft` | 5,75 | 6,08 | ✅ |
| `empty` / `empty-soft` | 5,21 | 6,13 | ✅ |
| `on-solid` / `brand` (tombol) | 5,90 | 6,36 | ✅ |
| `on-solid` / `crit` (tombol hapus) | 6,57 | 6,94 | ✅ |

> Dihitung 2026-10-11 dengan rumus relative luminance WCAG 2.x untuk **nilai usulan** di Fase A. Saat perencanaan, tiga pasangan sempat gagal lalu sudah dikoreksi: `empty` terang `#6b7280` (4,35 → kini `#5f6670`), serta teks putih di atas `brand`/`crit` di mode gelap (2,97 dan 2,72 → kini memakai `on-solid` gelap). **Wajib dihitung ulang** jika hex diganti (mis. warna resmi PLN).

### Keputusan yang perlu dikonfirmasi

| Topik | Keputusan | Tanggal |
|---|---|---|
| Hex resmi biru (dan kuning) PLN | – | – |
| Logo: aset resmi PLN atau monogram | – | – |
| Definisi `total` kelengkapan kWh (per engine + penyulang?) | – | – |
| Definisi kelengkapan Operasi Engine (jumlah jam wajib per shift?) | – | – |

### Metrik grep (baseline → sekarang)

| Pola | Baseline (2026-10-11) | Sesudah fase terakhir |
|---|---|---|
| `isDarkMode ?` | 40 | – |
| `font-black` | 17 | – |
| `font-extrabold` | 39 | – |
| `uppercase tracking-wider` | 26 | – |
| `text-[9px]` / `[10px]` / `[11px]` | 6 / 76 / 28 | – |
| `font-mono` | 109 | – |
| `rounded-2xl` | 48 | – |
| `shadow-` | 93 | – |
| `-500/10` | 57 | – |

## 8. Log perubahan dokumen

| Tanggal | Perubahan | Oleh |
|---|---|---|
| 2026-10-11 | Dokumen dibuat | Claude |
