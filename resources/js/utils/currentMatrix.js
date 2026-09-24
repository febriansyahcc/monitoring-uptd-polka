// Logika baris matriks Monitoring Arus (BUG-01), dipisah dari komponen agar bisa diuji
// dengan `node --test tests/Frontend/currentMatrix.test.mjs`.

// Samakan representasi nilai: '', null, undefined -> ''; angka/string angka -> string angka kanonik
export const normalize = (value) =>
  value === null || value === undefined || value === '' ? '' : String(Number(value));

export const serverStateOf = (item, feeders) => {
  const values = {};
  feeders.forEach((f) => {
    values[f.id] = normalize(item.values[f.id]);
  });
  return { values, operatorName: item.operator_name || '' };
};

/** Apakah isian baris berbeda dari state server yang diberikan. */
export const differs = (row, server, feeders) => {
  if (feeders.some((f) => normalize(row.formValues[f.id]) !== server.values[f.id])) return true;
  // Nama operator hanya tersimpan bersama nilai arus; pada baris kosong perubahan operator diabaikan
  const hasValues = feeders.some((f) => normalize(row.formValues[f.id]) !== '');
  return hasValues && (row.operatorName || '').trim() !== (server.operatorName || '').trim();
};

export const makeRow = (item, server) => ({
  interval: item.interval,
  formValues: { ...server.values },
  operatorName: server.operatorName,
  last_modified: item.last_modified || '-',
  server,
  isSaving: false,
  errors: {},
});

/**
 * Bangun ulang baris dari matriks server TANPA membuang isian yang belum disimpan.
 * Baris lama dipertahankan bila (1) user mengubahnya (beda dari snapshot server lama) dan
 * (2) masih beda dari data server baru. Baris yang baru tersimpan sukses otomatis sama
 * dengan server baru sehingga diambil dari server; baris yang tidak disentuh ikut data server
 * terbaru. Bila tanggal/shift berganti (`sameContext` false), semua baris di-reset.
 */
export const syncRows = (previousRows, matrix, feeders, sameContext) => {
  const previous = new Map(previousRows.map((row) => [row.interval, row]));

  return matrix.map((item) => {
    const server = serverStateOf(item, feeders);
    const prev = sameContext ? previous.get(item.interval) : null;

    if (prev && differs(prev, prev.server, feeders) && differs(prev, server, feeders)) {
      prev.server = server;
      prev.last_modified = item.last_modified || '-';
      return prev;
    }
    return makeRow(item, server);
  });
};

/**
 * Petakan error validasi batch (`rows.<i>.values.<feederId>`) ke baris yang dikirim.
 * Mengembalikan array error per baris, urut sesuai `rows`.
 */
export const mapBatchErrors = (errors, rowCount) => {
  const perRow = Array.from({ length: rowCount }, () => ({}));
  Object.entries(errors).forEach(([key, message]) => {
    const match = key.match(/^rows\.(\d+)\.(.+)$/);
    const index = match ? Number(match[1]) : 0;
    if (index < rowCount) perRow[index][match ? match[2] : key] = message;
  });
  return perRow;
};
