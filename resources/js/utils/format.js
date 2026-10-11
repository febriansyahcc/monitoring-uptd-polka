// Format tampilan yang dipakai bersama oleh tabel-tabel monitoring.

/** '2026-09-24' -> '24/09/2026' */
export const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const [year, month, day] = String(dateStr).split('-');
  return `${day}/${month}/${year}`;
};

/** Angka format Indonesia dengan minimal 2 desimal; null/undefined/'' -> '-' */
export const formatNumber = (value, minimumFractionDigits = 2) =>
  value === null || value === undefined || value === ''
    ? '-'
    : Number(value).toLocaleString('id-ID', { minimumFractionDigits });

/** Angka format Indonesia tanpa desimal paksa (maks. `maximumFractionDigits`); null/'' -> null. */
export const formatQty = (value, maximumFractionDigits = 1) =>
  value === null || value === undefined || value === ''
    ? null
    : Number(value).toLocaleString('id-ID', { maximumFractionDigits });

/** Ketahanan stok (hari) -> { value: '0,17', approx: '±4 jam' | null }. */
export const formatDays = (days) => {
  if (days === null || days === undefined) return { value: null, approx: null };
  const n = Number(days);
  const value = n.toLocaleString('id-ID', { maximumFractionDigits: 2 });
  if (n > 0 && n < 1) return { value, approx: `±${Math.max(1, Math.round(n * 24))} jam` };
  return { value, approx: null };
};
