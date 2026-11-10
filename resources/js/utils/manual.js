// Isi Manual Book (Panduan Penggunaan) PLN Monitor ULPLTD Poka.
// Satu-satunya sumber isi panduan: dipakai halaman /panduan (Pages/Manual/Index.vue)
// dan skrip pembuat PDF (scripts/build-manual-pdf.mjs). File ini sengaja tanpa import
// agar bisa dibaca langsung oleh Node.
//
// Struktur bagian:
//   id              — anchor (#id) dan kunci tombol "?" di Header (lihat MANUAL_ANCHORS)
//   permission      — izin lihat; null = semua pengguna. Bagian disembunyikan bila tidak punya izin.
//   blocks[].requires — izin tambahan untuk satu blok (mis. langkah input). Disembunyikan bila tidak punya.
// Jenis blok: p (paragraf), steps (langkah bernomor), list (poin), table (head + rows), note (tone: info|warn).
// Teks boleh memakai **tebal**; tidak ada HTML.

export const MANUAL_META = {
  title: 'Manual Book PLN Monitor',
  subtitle: 'Panduan Penggunaan Aplikasi Monitoring Operasional ULPLTD Poka',
  version: '1.0',
  updated: '10 November 2026',
};

export const MANUAL_SECTIONS = [
  {
    id: 'pendahuluan',
    title: 'Pendahuluan',
    icon: 'BookOpen',
    permission: null,
    summary: 'Tujuan aplikasi, siapa penggunanya, dan istilah yang dipakai.',
    blocks: [
      {
        type: 'p',
        text: 'PLN Monitor adalah aplikasi web untuk mencatat dan memantau data operasional ULPLTD Poka secara harian: beban arus penyulang, produksi kWh, logsheet operasi engine, gangguan, serta stok dan pemakaian BBM. Data yang diinput operator langsung tampil di Dashboard sehingga pimpinan dapat melihat kondisi pembangkit tanpa menunggu laporan manual.',
      },
      {
        type: 'list',
        title: 'Aplikasi dapat dibuka dari',
        items: [
          '**Komputer / laptop** (lebar layar ≥ 768 px): menu berada di sidebar kiri.',
          '**Ponsel / tablet** (lebar layar < 768 px): menu berada di bar navigasi bawah; menu tambahan ada di tombol **Lainnya**.',
        ],
      },
      {
        type: 'table',
        title: 'Istilah penting',
        head: ['Istilah', 'Arti'],
        rows: [
          ['Penyulang (feeder)', 'Saluran distribusi 20 kV yang keluar dari pembangkit.'],
          ['Interval', 'Titik waktu pencatatan arus setiap 30 menit dalam satu shift.'],
          ['Shift', 'Pagi 08.00–16.00, Sore 16.00–24.00, Malam 00.00–08.00 WIT.'],
          ['Stand kWh', 'Angka penunjukan meter kWh pada saat pencatatan.'],
          ['PS (Pemakaian Sendiri)', 'Energi yang dipakai pembangkit untuk kebutuhannya sendiri.'],
          ['Control Panel', 'Logsheet pembacaan panel kontrol generator per jam.'],
          ['Engine Area', 'Logsheet pemeriksaan kondisi mesin di area engine per jam.'],
          ['Death Stock', 'Volume BBM di dasar tangki yang tidak dapat dipompa.'],
          ['Netto', 'Stok BBM yang benar-benar dapat dipakai.'],
          ['HOP (Hari Operasi)', 'Perkiraan berapa hari stok netto BBM cukup untuk operasi.'],
          ['Unloading', 'Penerimaan / pengisian BBM ke tangki.'],
        ],
      },
    ],
  },
  {
    id: 'akses',
    title: 'Masuk, Keluar & Profil',
    icon: 'LogIn',
    permission: null,
    summary: 'Cara login, lupa password, mengubah profil, dan logout.',
    blocks: [
      {
        type: 'steps',
        title: 'Masuk ke aplikasi',
        items: [
          'Buka alamat aplikasi di browser (Chrome, Edge, atau Firefox versi terbaru).',
          'Isi **Email atau NIP** dan **Password** akun Anda.',
          'Tekan **Masuk**. Anda akan diarahkan ke Dashboard.',
        ],
      },
      {
        type: 'note',
        tone: 'warn',
        text: 'Akun yang dinonaktifkan Administrator tidak dapat login (muncul pesan "Akun Anda sedang nonaktif"). Jika password selalu ditolak, hubungi Administrator.',
      },
      {
        type: 'p',
        text: '**Lupa password:** tekan tautan **Lupa password?** di halaman login. Aplikasi tidak mengirim email reset; hubungi Administrator untuk mengatur ulang password Anda.',
      },
      {
        type: 'steps',
        title: 'Mengubah nama atau password',
        items: [
          'Tekan nama Anda di pojok kanan atas (di ponsel: ikon orang).',
          'Pada kartu **Data akun**, ubah **Nama** lalu tekan **Simpan profil**.',
          'Pada kartu **Ganti password**, isi password saat ini, password baru (minimal 8 karakter, harus berbeda), dan konfirmasinya, lalu tekan simpan.',
        ],
      },
      {
        type: 'note',
        tone: 'info',
        text: 'Email, NIP, dan peran (role) hanya dapat diubah oleh Administrator.',
      },
      {
        type: 'list',
        title: 'Elemen di bagian atas layar (header)',
        items: [
          '**Judul halaman** yang sedang dibuka.',
          '**Tanggal, jam WIT, dan shift berjalan**: acuan waktu pengisian data.',
          '**Ikon bulan / matahari**: beralih mode gelap / terang (tersimpan di perangkat).',
          '**Ikon ?**: membuka panduan untuk halaman yang sedang dibuka.',
          '**Ikon keluar**: logout. Selalu logout jika memakai komputer bersama.',
        ],
      },
    ],
  },
  {
    id: 'peran',
    title: 'Peran & Hak Akses',
    icon: 'ShieldCheck',
    permission: null,
    summary: 'Apa yang dapat dilakukan setiap peran pengguna.',
    blocks: [
      {
        type: 'p',
        text: 'Setiap akun memiliki satu **peran** dan sekumpulan **izin**. Menu yang tidak diizinkan tidak akan tampil, dan tombol tambah / edit / hapus hanya muncul bila Anda memiliki izin input. Hak akses bawaan per peran:',
      },
      {
        type: 'table',
        head: ['Modul', 'Admin', 'Manager', 'TL Operasi', 'TL Pemeliharaan', 'Operator'],
        rows: [
          ['Dashboard', 'Lihat', 'Lihat', 'Lihat', 'Lihat', 'Lihat'],
          ['Monitoring Arus', 'Lihat + Input', 'Lihat', 'Lihat + Input', 'Lihat', 'Lihat + Input'],
          ['kWh Produksi', 'Lihat + Input', 'Lihat', 'Lihat + Input', '—', 'Lihat + Input'],
          ['Operasi Engine', 'Lihat + Input', 'Lihat', 'Lihat + Input', 'Lihat', 'Lihat + Input'],
          ['Gangguan', 'Lihat + Kelola', 'Lihat', '—', 'Lihat + Kelola', 'Lihat + Kelola'],
          ['Stok BBM', 'Lihat + Input', 'Lihat', 'Lihat', '—', 'Lihat + Input'],
          ['Pengguna', 'Kelola', '—', '—', '—', '—'],
        ],
      },
      {
        type: 'note',
        tone: 'info',
        text: 'Tabel di atas adalah pengaturan awal. Administrator dapat menambah atau mengurangi izin setiap akun, sehingga akses Anda bisa berbeda.',
      },
    ],
  },
  {
    id: 'dashboard',
    title: 'Dashboard',
    icon: 'LayoutDashboard',
    permission: null,
    summary: 'Ringkasan kondisi pembangkit hari ini.',
    blocks: [
      {
        type: 'p',
        text: 'Dashboard adalah halaman pertama setelah login. Isinya diperbarui otomatis dari data yang diinput di setiap modul.',
      },
      {
        type: 'list',
        title: 'Bagian-bagian Dashboard',
        items: [
          '**Peringatan BBM**: muncul hanya bila ketahanan stok BBM waspada (≤ 10 hari) atau kritis (≤ 5 hari).',
          '**Ringkasan (KPI)**: beban arus terakhir, kWh produksi hari ini dan bulan ini, jumlah gangguan bulan ini, serta ketahanan stok BBM. Tekan kotak KPI untuk membuka modulnya.',
          '**Kelengkapan input**: daftar data yang harus diisi pada shift berjalan, beserta progresnya. Tekan **Isi** / **Lanjutkan** untuk langsung ke halaman input.',
          '**Gangguan terbaru**: catatan gangguan paling baru dan status penanganannya.',
          '**Grafik**: beban arus hari ini per interval dan produksi kWh 7 hari terakhir.',
          '**Beban per penyulang**: nilai arus terakhir hari ini untuk setiap penyulang. Titik hijau berarti sudah ada data, abu-abu berarti belum.',
        ],
      },
      {
        type: 'table',
        title: 'Arti warna status',
        head: ['Warna', 'Status', 'Arti'],
        rows: [
          ['Hijau', 'Normal', 'Kondisi aman / data lengkap.'],
          ['Kuning', 'Waspada', 'Perlu perhatian, mis. stok BBM ≤ 10 hari atau gangguan belum selesai.'],
          ['Merah', 'Kritis', 'Perlu tindakan segera, mis. stok BBM ≤ 5 hari.'],
          ['Abu-abu', 'Belum ada data', 'Data belum diinput.'],
        ],
      },
    ],
  },
  {
    id: 'monitoring-arus',
    title: 'Monitoring Arus',
    icon: 'Activity',
    permission: 'monitoring_arus.view',
    inputPermission: 'monitoring_arus.input',
    summary: 'Pencatatan beban arus (Ampere) setiap penyulang tiap 30 menit.',
    blocks: [
      {
        type: 'list',
        title: 'Filter & tampilan',
        items: [
          '**Tanggal**: pilih tanggal pencatatan.',
          '**Shift**: Pagi, Sore, atau Malam. Setiap shift berisi 16 interval (mis. shift pagi 08.30 s.d. 16.00).',
          '**Beban penyulang**: grafik garis dan tabel isian arus per penyulang per interval.',
          '**Arus per fasa**: tabel arus tiap fasa yang dihitung otomatis dari data penyulang (hanya baca).',
        ],
      },
      {
        type: 'steps',
        title: 'Menginput arus',
        requires: 'monitoring_arus.input',
        items: [
          'Pilih **tanggal** dan **shift** yang akan diisi.',
          'Pada tabel **Beban arus per interval**, isi nilai Ampere setiap penyulang di baris jam yang sesuai.',
          'Tekan **Simpan** di ujung baris untuk menyimpan satu jam, atau isi beberapa baris lalu tekan **Simpan semua** di atas tabel.',
          'Tunggu notifikasi berhasil. Grafik dan Dashboard akan langsung diperbarui.',
        ],
      },
      {
        type: 'note',
        tone: 'warn',
        requires: 'monitoring_arus.input',
        text: 'Baris yang sudah diubah tetapi belum disimpan ditandai "baris belum disimpan". Jika Anda pindah tanggal, shift, atau menu, aplikasi akan meminta konfirmasi karena isian tersebut akan hilang.',
      },
      {
        type: 'p',
        text: 'Di ponsel, tabel ditampilkan sebagai kartu per jam. Tombol **Simpan jam ...** ada di setiap kartu dan tombol **Simpan semua** menempel di atas layar saat digulir.',
      },
    ],
  },
  {
    id: 'monitoring-kwh',
    title: 'Monitoring kWh Produksi',
    icon: 'Zap',
    permission: 'monitoring_kwh.view',
    inputPermission: 'monitoring_kwh.input',
    summary: 'Stand kWh harian engine, penyulang, dan PS total.',
    blocks: [
      {
        type: 'list',
        title: 'Filter & tampilan',
        items: [
          '**Bulan**: pilih bulan yang ingin dilihat.',
          '**Ringkasan bulan**: total produksi engine, total PS, dan jumlah hari yang sudah tercatat.',
          'Tab **Engine**: stand akhir kWh produksi dan kWh PS per engine, beserta grafik produksi harian.',
          'Tab **Penyulang**: stand akhir kWh produksi per penyulang.',
          'Tab **PS Total**: kWh PS dan kWh digital per penyulang; nilai **PS Total** dihitung otomatis.',
        ],
      },
      {
        type: 'steps',
        title: 'Menambah data kWh',
        requires: 'monitoring_kwh.input',
        items: [
          'Pilih tab yang sesuai (Engine, Penyulang, atau PS Total).',
          'Tekan tombol **Tambah Data ...** di kanan atas tabel.',
          'Isi tanggal dan angka stand meter pada formulir, lalu tekan simpan.',
          'Data baru muncul di tabel. Kolom **Terakhir diubah** mencatat siapa dan kapan data diubah.',
        ],
      },
      {
        type: 'steps',
        title: 'Mengubah atau menghapus data',
        requires: 'monitoring_kwh.input',
        items: [
          'Pada kolom **Aksi**, tekan ikon pensil untuk mengubah atau ikon tempat sampah untuk menghapus.',
          'Untuk hapus, konfirmasi pada dialog yang muncul. Data yang dihapus tidak dapat dikembalikan.',
        ],
      },
      {
        type: 'note',
        tone: 'info',
        text: 'Produksi harian dihitung dari selisih stand hari ini dengan stand hari sebelumnya. Pastikan stand diisi berurutan setiap hari agar perhitungan benar.',
      },
    ],
  },
  {
    id: 'monitoring-operasi-engine',
    title: 'Monitoring Operasi Engine',
    icon: 'Cpu',
    permission: 'monitoring_engine.view',
    inputPermission: 'monitoring_engine.input',
    summary: 'Logsheet Control Panel dan Engine Area untuk CAT #4 dan ABC #6.',
    blocks: [
      {
        type: 'list',
        title: 'Filter & tampilan',
        items: [
          '**Tanggal**: pilih tanggal logsheet.',
          '**Jenis engine**: **CAT #4** atau **ABC #6**. Setiap jenis memiliki parameter logsheet sendiri.',
          'Tab **Control panel**: pembacaan panel kontrol generator per jam (tegangan, arus, daya, frekuensi, dsb.).',
          'Tab **Engine area**: pemeriksaan kondisi mesin per jam (tekanan, temperatur, level, dsb.).',
        ],
      },
      {
        type: 'steps',
        title: 'Menambah logsheet',
        requires: 'monitoring_engine.input',
        items: [
          'Pilih **tanggal**, **jenis engine**, lalu tab **Control panel** atau **Engine area**.',
          'Tekan **Tambah Data ...** di atas tabel.',
          'Pilih **jam** pembacaan dan (untuk CAT #4) **engine** yang diperiksa.',
          'Isi setiap parameter sesuai penunjukan alat. Parameter pilihan (mis. kondisi Normal / Tidak Normal) diisi dengan memilih salah satu opsi.',
          'Tekan simpan. Data tampil di tabel menurut jam dan engine.',
        ],
      },
      {
        type: 'note',
        tone: 'info',
        text: 'Tabel logsheet memiliki banyak kolom. Di layar kecil, geser tabel ke kanan untuk melihat parameter lainnya.',
      },
    ],
  },
  {
    id: 'monitoring-gangguan',
    title: 'Monitoring Gangguan',
    icon: 'AlertTriangle',
    permission: 'monitoring_gangguan.view',
    inputPermission: 'monitoring_gangguan.manage',
    summary: 'Pencatatan gangguan operasional dan status penanganannya.',
    blocks: [
      {
        type: 'list',
        title: 'Filter & tampilan',
        items: [
          '**Bulan**: pilih bulan yang ingin dilihat.',
          '**Ringkasan**: total gangguan, jumlah dalam penanganan, investigasi, dan selesai.',
          '**Grafik**: sebaran jenis gangguan dan perbandingan status penanganan.',
          '**Tabel**: tanggal, jam, jenis gangguan, keterangan, dan status penanganan.',
        ],
      },
      {
        type: 'steps',
        title: 'Mencatat gangguan baru',
        requires: 'monitoring_gangguan.manage',
        items: [
          'Tekan **Catat Gangguan Baru**.',
          'Isi **tanggal** dan **jam** kejadian.',
          'Pilih **Jenis Gangguan**: Trip Feeder, Pohon Tumbang, Sambaran Petir, Overload / Beban Lebih, Kerusakan Komponen, atau Lain-lain.',
          'Tulis **Keterangan** singkat (lokasi, penyebab, tindakan).',
          'Pilih **Status Penanganan**, lalu tekan simpan.',
        ],
      },
      {
        type: 'table',
        title: 'Status penanganan',
        head: ['Status', 'Kapan dipakai'],
        rows: [
          ['Dalam Penanganan', 'Gangguan sedang ditangani petugas (ditandai kuning).'],
          ['Investigasi', 'Penyebab masih diselidiki (ditandai kuning).'],
          ['Selesai', 'Gangguan sudah tertangani dan sistem normal kembali (ditandai hijau).'],
        ],
      },
      {
        type: 'note',
        tone: 'warn',
        requires: 'monitoring_gangguan.manage',
        text: 'Perbarui status melalui ikon pensil setiap kali penanganan berubah. Gangguan yang belum "Selesai" dihitung sebagai belum selesai di Dashboard.',
      },
    ],
  },
  {
    id: 'monitoring-bbm',
    title: 'Monitoring Stok BBM',
    icon: 'Fuel',
    permission: 'monitoring_bbm.view',
    inputPermission: 'monitoring_bbm.input',
    summary: 'Stok tangki, pemakaian, unloading, dan ketahanan stok (HOP).',
    blocks: [
      {
        type: 'list',
        title: 'Filter & tampilan',
        items: [
          '**Bulan**: pilih bulan yang ingin dilihat.',
          '**Peringatan**: muncul bila stok menipis atau kritis, sebagai pengingat untuk mengajukan unloading BBM.',
          '**Ringkasan**: stok netto terakhir, ketahanan stok (HOP), total pemakaian, dan total unloading bulan ini.',
          '**Grafik & tabel**: perkembangan stok harian.',
        ],
      },
      {
        type: 'steps',
        title: 'Menginput stok BBM harian',
        requires: 'monitoring_bbm.input',
        items: [
          'Tekan **Tambah Data Stok BBM**.',
          'Isi **tanggal**, **Pemakaian Hari Ini**, **Main Tank**, **Death Stock**, **Unloading** (jika ada pengisian), dan **Estimasi Harian** pemakaian.',
          'Tekan simpan. Total Gross, Netto, dan HOP dihitung otomatis.',
        ],
      },
      {
        type: 'table',
        title: 'Rumus perhitungan otomatis',
        head: ['Nilai', 'Rumus'],
        rows: [
          ['Total Gross', '= Main Tank'],
          ['Netto', '= Total Gross − Death Stock + Unloading'],
          ['HOP (hari)', '= Netto ÷ Estimasi Harian'],
        ],
      },
      {
        type: 'table',
        title: 'Status ketahanan stok',
        head: ['HOP', 'Status'],
        rows: [
          ['> 10 hari', 'Normal (hijau)'],
          ['6 – 10 hari', 'Waspada (kuning)'],
          ['≤ 5 hari', 'Kritis (merah)'],
        ],
      },
      {
        type: 'note',
        tone: 'warn',
        requires: 'monitoring_bbm.input',
        text: 'Satu tanggal hanya boleh memiliki satu data. Jika tanggal sudah terisi, gunakan ikon pensil pada baris tersebut untuk mengubahnya.',
      },
    ],
  },
  {
    id: 'users',
    title: 'Pengelolaan Pengguna',
    icon: 'Users',
    permission: 'users.manage',
    summary: 'Menambah akun pegawai, mengatur izin, dan menonaktifkan akun (khusus Administrator).',
    blocks: [
      {
        type: 'steps',
        title: 'Menambah pengguna',
        items: [
          'Buka menu **Pengguna**, lalu tekan **Tambah pengguna**.',
          'Isi nama, NIP, email, dan password awal.',
          'Pilih **peran**. Izin bawaan peran tersebut akan tercentang otomatis.',
          'Sesuaikan centang izin bila perlu, lalu simpan.',
          'Berikan email dan password awal kepada pegawai dan minta mereka segera mengganti password lewat menu Profil.',
        ],
      },
      {
        type: 'list',
        title: 'Pengelolaan lainnya',
        items: [
          '**Cari** pengguna berdasarkan nama, NIP, atau email, dan **saring** berdasarkan peran.',
          '**Edit** untuk mengubah data, peran, izin, atau mengatur ulang password pengguna yang lupa password.',
          '**Nonaktifkan / Aktifkan** akun. Akun nonaktif tidak bisa login, tetapi datanya tetap tersimpan.',
        ],
      },
      {
        type: 'note',
        tone: 'warn',
        text: 'Nonaktifkan akun pegawai yang pindah tugas atau tidak lagi bertugas daripada membagikan akunnya ke orang lain.',
      },
    ],
  },
  {
    id: 'faq',
    title: 'Tanya Jawab & Kendala',
    icon: 'HelpCircle',
    permission: null,
    summary: 'Masalah yang sering ditemui dan cara mengatasinya.',
    blocks: [
      {
        type: 'table',
        head: ['Kendala', 'Penyebab & solusi'],
        rows: [
          ['Menu suatu modul tidak muncul', 'Akun Anda tidak memiliki izin lihat modul tersebut. Hubungi Administrator.'],
          ['Tombol Tambah / Simpan tidak ada', 'Akun Anda hanya memiliki izin lihat, tanpa izin input.'],
          ['Muncul pesan "Data untuk tanggal ini sudah ada"', 'Tanggal tersebut sudah terisi. Gunakan ikon pensil pada baris yang ada untuk mengubahnya.'],
          ['Isian arus hilang setelah pindah halaman', 'Data belum disimpan. Selalu tekan Simpan / Simpan semua sebelum berpindah tanggal, shift, atau menu.'],
          ['Halaman kembali ke login', 'Sesi berakhir karena lama tidak aktif. Login kembali; data yang belum disimpan perlu diisi ulang.'],
          ['Angka di Dashboard belum berubah', 'Muat ulang halaman (tarik ke bawah di ponsel atau tekan F5).'],
          ['Lupa password', 'Hubungi Administrator untuk mengatur ulang password.'],
        ],
      },
      {
        type: 'note',
        tone: 'info',
        text: 'Jika kendala tidak tercantum di sini, catat langkah yang dilakukan dan pesan yang muncul, lalu laporkan ke Administrator aplikasi.',
      },
    ],
  },
];

// URL halaman → anchor bagian panduan (dipakai tombol "?" di Header)
export const MANUAL_ANCHORS = [
  { match: (url) => url.startsWith('/monitoring-arus'), id: 'monitoring-arus' },
  { match: (url) => url.startsWith('/monitoring-kwh'), id: 'monitoring-kwh' },
  { match: (url) => url.startsWith('/monitoring-operasi-engine'), id: 'monitoring-operasi-engine' },
  { match: (url) => url.startsWith('/monitoring-gangguan'), id: 'monitoring-gangguan' },
  { match: (url) => url.startsWith('/monitoring-bbm'), id: 'monitoring-bbm' },
  { match: (url) => url.startsWith('/users'), id: 'users' },
  { match: (url) => url.startsWith('/profil'), id: 'akses' },
  { match: (url) => url === '/' || url.startsWith('/dashboard') || url.startsWith('/?'), id: 'dashboard' },
];

export const manualAnchorFor = (url) => MANUAL_ANCHORS.find((a) => a.match(url ?? ''))?.id ?? null;

/** "teks **tebal** teks" → [{ text, bold }] untuk dirender tanpa v-html */
export const richParts = (text) =>
  String(text)
    .split(/(\*\*[^*]+\*\*)/g)
    .filter(Boolean)
    .map((part) => (part.startsWith('**') && part.endsWith('**') ? { text: part.slice(2, -2), bold: true } : { text: part, bold: false }));
