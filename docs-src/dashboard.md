---
title: "4. Dashboard"
description: "Ringkasan kondisi pembangkit hari ini."
---

# 4. Dashboard

Ringkasan kondisi pembangkit hari ini. <span class="manual-badge is-public">Akses: Semua Pengguna</span>

Dashboard adalah halaman pertama setelah login. Isinya diperbarui otomatis dari data yang diinput di setiap modul.

<figure class="manual-figure">

![Dashboard: peringatan BBM, ringkasan KPI, kelengkapan input, dan gangguan terbaru.](/images/manual/dashboard.png)

<figcaption>Dashboard: peringatan BBM, ringkasan KPI, kelengkapan input, dan gangguan terbaru.</figcaption>
</figure>

## Bagian-bagian Dashboard

- **Peringatan BBM**: muncul hanya bila ketahanan stok BBM waspada (≤ 10 hari) atau kritis (≤ 5 hari).
- **Ringkasan (KPI)**: beban arus terakhir, kWh produksi hari ini dan bulan ini, jumlah gangguan bulan ini, serta ketahanan stok BBM. Tekan kotak KPI untuk membuka modulnya.
- **Kelengkapan input**: daftar data yang harus diisi pada shift berjalan, beserta progresnya. Tekan **Isi** / **Lanjutkan** untuk langsung ke halaman input.
- **Gangguan terbaru**: catatan gangguan paling baru dan status penanganannya.
- **Grafik**: beban arus hari ini per interval dan produksi kWh 7 hari terakhir.
- **Beban per penyulang**: nilai arus terakhir hari ini untuk setiap penyulang. Titik hijau berarti sudah ada data, abu-abu berarti belum.

## Arti warna status

| Warna | Status | Arti |
| --- | --- | --- |
| Hijau | Normal | Kondisi aman / data lengkap. |
| Kuning | Waspada | Perlu perhatian, mis. stok BBM ≤ 10 hari atau gangguan belum selesai. |
| Merah | Kritis | Perlu tindakan segera, mis. stok BBM ≤ 5 hari. |
| Abu-abu | Belum ada data | Data belum diinput. |
