// Unit test logika baris Monitoring Arus (BUG-01).
//   node --test tests/Frontend/*.test.mjs
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { normalize, differs, syncRows, mapBatchErrors } from '../../resources/js/utils/currentMatrix.js';

const feeders = [{ id: 1 }, { id: 2 }];
const item = (interval, values = {}, extra = {}) => ({
  interval,
  values: { 1: null, 2: null, ...values },
  operator_name: '',
  last_modified: '-',
  ...extra,
});

const initial = () => syncRows([], [item('08.30'), item('09.00')], feeders, false);

test('normalize menyamakan representasi nilai', () => {
  assert.equal(normalize(null), '');
  assert.equal(normalize(''), '');
  assert.equal(normalize('12.50'), '12.5');
  assert.equal(normalize(12.5), '12.5');
});

test('reproduksi BUG-01: simpan baris 08.30 tidak menghapus isian baris 09.00', () => {
  let rows = initial();
  rows[0].formValues[1] = 100; // 08.30 diisi
  rows[1].formValues[2] = 55; // 09.00 diisi, belum disimpan

  // Server mengembalikan matriks setelah 08.30 tersimpan
  rows = syncRows(rows, [item('08.30', { 1: '100.00' }, { last_modified: '08:31:00' }), item('09.00')], feeders, true);

  assert.equal(rows[1].formValues[2], 55, 'isian 09.00 tetap ada');
  assert.equal(differs(rows[1], rows[1].server, feeders), true, '09.00 tetap ditandai belum disimpan');
  assert.equal(differs(rows[0], rows[0].server, feeders), false, '08.30 tidak lagi dirty');
  assert.equal(rows[0].last_modified, '08:31:00');
});

test('baris yang gagal disimpan (error validasi) tetap mempertahankan isian', () => {
  let rows = initial();
  rows[0].formValues[1] = -5;
  rows = syncRows(rows, [item('08.30'), item('09.00')], feeders, true); // server tidak berubah
  assert.equal(rows[0].formValues[1], -5);
});

test('baris yang tidak disentuh mengikuti data server terbaru (mis. diisi user lain)', () => {
  let rows = initial();
  rows = syncRows(rows, [item('08.30'), item('09.00', { 2: '77' })], feeders, true);
  assert.equal(normalize(rows[1].formValues[2]), '77');
  assert.equal(differs(rows[1], rows[1].server, feeders), false);
});

test('ganti tanggal/shift (context berbeda) me-reset semua baris', () => {
  let rows = initial();
  rows[1].formValues[2] = 55;
  rows = syncRows(rows, [item('08.30'), item('09.00')], feeders, false);
  assert.equal(rows[1].formValues[2], '');
});

test('perubahan operator pada baris tanpa nilai tidak dianggap dirty', () => {
  const rows = initial();
  rows[0].operatorName = 'Budi';
  assert.equal(differs(rows[0], rows[0].server, feeders), false);
  rows[0].formValues[1] = 10;
  assert.equal(differs(rows[0], rows[0].server, feeders), true);
});

test('mapBatchErrors memetakan error rows.<i>.* ke baris ke-i', () => {
  const perRow = mapBatchErrors({ 'rows.1.values.2': 'Minimal 0.', 'rows.0.time_interval': 'Tidak valid.' }, 2);
  assert.deepEqual(perRow, [{ time_interval: 'Tidak valid.' }, { 'values.2': 'Minimal 0.' }]);
});
