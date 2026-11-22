---
title: "9. Monitoring Stok BBM"
description: "Stok tangki, pemakaian, unloading, dan ketahanan stok (HOP)."
---

# 9. Monitoring Stok BBM

Stok tangki, pemakaian, unloading, dan ketahanan stok (HOP). <span class="manual-badge">Akses: Lihat Monitoring BBM</span>

## Filter & tampilan

- **Bulan**: pilih bulan yang ingin dilihat.
- **Peringatan**: muncul bila stok menipis atau kritis, sebagai pengingat untuk mengajukan unloading BBM.
- **Ringkasan**: stok netto terakhir, ketahanan stok (HOP), total pemakaian, dan total unloading bulan ini.
- **Grafik & tabel**: perkembangan stok harian.

<figure class="manual-figure">

![Monitoring Stok BBM: peringatan, ringkasan, dan grafik ketahanan stok dengan batas waspada & kritis.](/images/manual/bbm.png)

<figcaption>Monitoring Stok BBM: peringatan, ringkasan, dan grafik ketahanan stok dengan batas waspada & kritis.</figcaption>
</figure>

## Menginput stok BBM harian <span class="manual-badge">Perlu izin: Input/Edit Monitoring BBM</span>

1. Tekan **Tambah Data Stok BBM**.
2. Isi **tanggal**, **Pemakaian Hari Ini**, **Main Tank**, **Death Stock**, **Unloading** (jika ada pengisian), dan **Estimasi Harian** pemakaian.
3. Tekan simpan. Total Gross, Netto, dan HOP dihitung otomatis.

<span class="manual-badge">Perlu izin: Input/Edit Monitoring BBM</span>

<figure class="manual-figure">

![Formulir Tambah Data Stok BBM.](/images/manual/bbm-form.png)

<figcaption>Formulir Tambah Data Stok BBM.</figcaption>
</figure>

## Rumus perhitungan otomatis

| Nilai | Rumus |
| --- | --- |
| Total Gross | = Main Tank |
| Netto | = Total Gross − Death Stock + Unloading |
| HOP (hari) | = Netto ÷ Estimasi Harian |

## Status ketahanan stok

| HOP | Status |
| --- | --- |
| > 10 hari | Normal (hijau) |
| 6 – 10 hari | Waspada (kuning) |
| ≤ 5 hari | Kritis (merah) |

<span class="manual-badge">Perlu izin: Input/Edit Monitoring BBM</span>

::: warning Perhatian
Satu tanggal hanya boleh memiliki satu data. Jika tanggal sudah terisi, gunakan ikon pensil pada baris tersebut untuk mengubahnya.
:::
