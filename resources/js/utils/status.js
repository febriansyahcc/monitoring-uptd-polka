// Peta kode status (dari server, lihat app/Support/Status.php) ke kelas token warna.
// Ini SATU-SATUNYA tempat yang memutuskan warna status — jangan memetakan teks status di template.
export const STATUS = {
  ok: { label: 'Normal', text: 'text-ok', soft: 'bg-ok-soft', dot: 'bg-ok', bar: 'bg-ok' },
  warning: { label: 'Waspada', text: 'text-warn', soft: 'bg-warn-soft', dot: 'bg-warn', bar: 'bg-warn' },
  critical: { label: 'Kritis', text: 'text-crit', soft: 'bg-crit-soft', dot: 'bg-crit', bar: 'bg-crit' },
  empty: { label: 'Belum ada data', text: 'text-empty', soft: 'bg-empty-soft', dot: 'bg-empty', bar: 'bg-empty' },
};

export const statusOf = (code) => STATUS[code] ?? STATUS.empty;

/** Status penanganan gangguan (teks di DB) → kode status. Sama dengan Status::disturbance() di PHP. */
export const disturbanceStatus = (status) =>
  status === 'Selesai' ? 'ok' : status === 'Dalam Penanganan' || status === 'Investigasi' ? 'warning' : 'empty';

/** Ketahanan stok BBM (hari) → kode status. Sama dengan Status::fuel() di PHP (data ada). */
export const fuelStatus = (days) => (Number(days) <= 5 ? 'critical' : Number(days) <= 10 ? 'warning' : 'ok');
