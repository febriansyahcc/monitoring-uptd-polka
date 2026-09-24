// Tanggal hari ini (YYYY-MM-DD) menurut zona waktu lokal browser.
// Jangan pakai toISOString(): hasilnya UTC, sehingga di WIT (UTC+9) pukul 00:00–08:59 menjadi tanggal kemarin.
export const todayLocal = () => {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};

// Jam default form dibulatkan ke interval jam/setengah jam terdekat (HH:MM).
// Contoh: 08:07 -> 08:00, 08:18 -> 08:30, 08:47 -> 09:00.
export const currentTimeRounded = (d = new Date()) => {
  const minutes = d.getMinutes();
  const roundedMin = minutes < 15 ? 0 : minutes < 45 ? 30 : 0;
  const hours = minutes >= 45 ? (d.getHours() + 1) % 24 : d.getHours();
  return `${String(hours).padStart(2, '0')}:${String(roundedMin).padStart(2, '0')}`;
};
