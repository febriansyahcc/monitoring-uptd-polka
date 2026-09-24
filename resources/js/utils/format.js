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
