// Tanggal hari ini (YYYY-MM-DD) menurut zona waktu lokal browser.
// Jangan pakai toISOString(): hasilnya UTC, sehingga di WIT (UTC+9) pukul 00:00–08:59 menjadi tanggal kemarin.
export const todayLocal = () => {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};
