# Panduan Deploy — pltdpoka.my.id (shared hosting / cPanel)

Struktur di hosting (sesuai File Manager Anda):

```
~/                              (home)
├── pltdpoka/                   <- APLIKASI Laravel (di luar web root, tidak bisa diakses browser)
└── public_html/
    ├── pln.stg.febriansyah.my.id/   (staging, jangan disentuh)
    └── pltdpoka.my.id/              <- DOCUMENT ROOT domain, isinya folder public/ Laravel
```

`index.php` di paket sudah disesuaikan ke `../../pltdpoka`. Kalau nama folder berbeda, jalankan
`build-package.ps1 -AppDirName <nama> -DocRootName <nama>`.

## A. Di PC (sekali tiap rilis)

```powershell
powershell -ExecutionPolicy Bypass -File deploy\build-package.ps1
php artisan key:generate --show      # salin hasilnya untuk APP_KEY
```

Hasil di `deploy\dist\`: `pltdpoka-app.zip` (~8 MB, sudah berisi `vendor/` tanpa paket dev) dan
`pltdpoka-public.zip` (~0,5 MB). `public.zip` & `monitoring-pln.zip` di root proyek adalah paket lama, jangan dipakai.

Buat hash password admin:

```powershell
php -r "echo password_hash('PasswordKuatAnda', PASSWORD_BCRYPT);"
```

Buka `deploy\fresh-install-2026-11-02.sql`, ganti `__GANTI_HASH_BCRYPT__` dengan hash tadi (simpan di tanda kutip).

## B. Di cPanel

1. **Cek versi PHP** domain: MultiPHP Manager → pltdpoka.my.id → PHP **8.2 atau lebih baru**.
   Ekstensi yang perlu aktif: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, fileinfo.
2. **Database**: MySQL® Databases → buat database + user → beri **ALL PRIVILEGES**. Catat nama lengkapnya (berawalan akun).
3. **Upload kode**
   - File Manager → `~/pltdpoka` → Upload `pltdpoka-app.zip` → klik kanan → Extract → hapus zip-nya.
   - File Manager → `public_html/pltdpoka.my.id` → Upload `pltdpoka-public.zip` → Extract → hapus zip-nya.
   - Pastikan `.htaccess` ikut (File Manager → Settings → centang *Show Hidden Files*).
4. **`.env`**: salin `deploy\env.production.example` jadi `~/pltdpoka/.env`, isi `APP_KEY` dan `DB_*`.
5. **Izin folder**: `~/pltdpoka/storage` dan `~/pltdpoka/bootstrap/cache` → permission **775** (rekursif).
6. **Import database**: phpMyAdmin → pilih database (harus kosong) → Import → `fresh-install-2026-11-02.sql`.
7. Buka `https://pltdpoka.my.id` → login `admin@pln.co.id` dengan password yang tadi Anda hash.
   Setelah masuk, buat akun operator/manager dari menu **Pengguna**.
8. Pastikan SSL aktif (cPanel → SSL/TLS Status → AutoSSL / Let's Encrypt) agar `https://` valid.

Jika muncul **500**: lihat `~/pltdpoka/storage/logs/laravel.log`. Penyebab umum: `APP_KEY` kosong, kredensial DB salah,
PHP < 8.2, atau permission `storage` belum 775.

## C. Database — jawaban singkat

**Tidak perlu berurutan untuk database baru.** Domain baru berarti database kosong, jadi cukup impor
**satu file** `fresh-install-2026-11-02.sql` (skema final + data awal + catatan 21 migration).

File `staging-*.sql` adalah *tambalan* untuk database staging yang sudah ada isinya. Jangan dijalankan di database
baru (butuh tabel `users`/`feeders`/`migrations` yang belum ada, akan error).

Urutan tambalan, hanya bila suatu saat Anda harus menyusul database lama (mis. staging) yang belum diperbarui —
jalankan yang belum pernah, sesuai tanggal, satu kali:

| # | File | Catatan |
|---|------|---------|
| 1 | `staging-upgrade-2026-10-12.sql` | 4 tabel engine/kWh + izin engine. Aman diulang |
| 2 | `staging-fix-rinjali-2026-10-25.sql` | Gabung penyulang Rinjali → Rijali. Aman diulang |
| 3 | `staging-revisi-penyulang-2026-10-28.sql` | Daftar penyulang baru. Aman diulang |
| 4 | `staging-kwh-ps-total-2026-10-28.sql` | Kolom PS jadi NULL. Aman diulang |
| 5 | `staging-engine-area-2026-10-28.sql` | **Sekali saja** (ulang = Duplicate column) |
| 6 | `staging-rename-kwh-produksi-2026-10-29.sql` | **Sekali saja** (ulang = error kolom lama hilang) |
| 7 | `staging-abc-engine-2026-10-31.sql` | Tabel ABC. Aman diulang |

Urutan ini penting khususnya untuk 5 dan 6 (butuh #1 lebih dulu). Tiap file mencatat dirinya di tabel `migrations`.

### Rilis berikutnya (setelah live)

Tiap kali ada migration baru, buat file SQL tambalan serupa (atau jalankan `php artisan migrate --force`
bila hosting punya Terminal/SSH). **Selalu backup dulu** (phpMyAdmin → Export). Karena `migrations` sudah terisi
21 baris, `php artisan migrate` tidak akan mengulang yang lama.

### Bila hosting punya Terminal/SSH (opsi lebih rapi)

Alih-alih impor SQL: buat database kosong, isi `.env`, lalu

```bash
cd ~/pltdpoka
php artisan migrate --force
php artisan db:seed --class=FeederSeeder --force
```

Lalu buat admin lewat `php artisan tinker`. **Jangan** menjalankan `UserSeeder`/`db:seed` penuh di produksi:
seeder itu membuat 12 akun dengan password `password`.

## D. Checklist sesudah live

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] Login admin berhasil, password admin bukan default
- [ ] Input satu data per menu (Arus, kWh, Engine CAT & ABC, Gangguan, BBM) dan simpan berhasil
- [ ] `https://pltdpoka.my.id/.env` dan `/storage/logs/laravel.log` → harus 404/403 (tidak boleh terbuka)
- [ ] Backup database terjadwal (cPanel → Backup, atau Export berkala)
