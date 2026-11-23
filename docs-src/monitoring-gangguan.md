---
title: "8. Monitoring Gangguan"
description: "Pencatatan gangguan operasional dan status penanganannya."
---

# 8. Monitoring Gangguan

Pencatatan gangguan operasional dan status penanganannya. <span class="manual-badge">Akses: Lihat Monitoring Gangguan</span>

## Filter & tampilan

- **Bulan**: pilih bulan yang ingin dilihat.
- **Ringkasan**: total gangguan, jumlah dalam penanganan, investigasi, dan selesai.
- **Grafik**: sebaran jenis gangguan dan perbandingan status penanganan.
- **Tabel**: tanggal, jam, jenis gangguan, keterangan, dan status penanganan.

<figure class="manual-figure">

![Monitoring Gangguan: ringkasan, grafik jenis gangguan, dan riwayat.](/images/manual/gangguan.png)

<figcaption>Monitoring Gangguan: ringkasan, grafik jenis gangguan, dan riwayat.</figcaption>
</figure>

## Mencatat gangguan baru <span class="manual-badge">Perlu izin: Kelola Monitoring Gangguan</span>

1. Tekan **Catat Gangguan Baru**.
2. Isi **tanggal** dan **jam** kejadian.
3. Pilih **Jenis Gangguan**: Trip Feeder, Pohon Tumbang, Sambaran Petir, Overload / Beban Lebih, Kerusakan Komponen, atau Lain-lain.
4. Tulis **Keterangan** singkat (lokasi, penyebab, tindakan).
5. Pilih **Status Penanganan**, lalu tekan simpan.

<span class="manual-badge">Perlu izin: Kelola Monitoring Gangguan</span>

<figure class="manual-figure">

![Formulir Catat Gangguan Baru.](/images/manual/gangguan-form.png)

<figcaption>Formulir Catat Gangguan Baru.</figcaption>
</figure>

## Status penanganan

| Status | Kapan dipakai |
| --- | --- |
| Dalam Penanganan | Gangguan sedang ditangani petugas (ditandai kuning). |
| Investigasi | Penyebab masih diselidiki (ditandai kuning). |
| Selesai | Gangguan sudah tertangani dan sistem normal kembali (ditandai hijau). |

<span class="manual-badge">Perlu izin: Kelola Monitoring Gangguan</span>

::: warning Perhatian
Perbarui status melalui ikon pensil setiap kali penanganan berubah. Gangguan yang belum "Selesai" dihitung sebagai belum selesai di Dashboard.
:::
