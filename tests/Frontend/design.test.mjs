// Unit test util sistem desain (IMPLEMENTATION.md Fase C/E/F): peta status, shift, format angka.
//   node --test tests/Frontend/*.test.mjs
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { STATUS, statusOf, disturbanceStatus, fuelStatus } from '../../resources/js/utils/status.js';
import { shiftKeyAt } from '../../resources/js/utils/shift.js';
import { formatDays, formatQty } from '../../resources/js/utils/format.js';

test('statusOf: setiap kode memakai token warna status yang sesuai', () => {
  assert.equal(statusOf('ok').text, 'text-ok');
  assert.equal(statusOf('warning').text, 'text-warn');
  assert.equal(statusOf('critical').text, 'text-crit');
  assert.equal(statusOf('empty').text, 'text-empty');
});

test('statusOf: kode tidak dikenal / kosong -> empty (tidak pernah merah)', () => {
  assert.equal(statusOf('Kritis'), STATUS.empty);
  assert.equal(statusOf(undefined), STATUS.empty);
});

test('disturbanceStatus sama dengan App\\Support\\Status::disturbance()', () => {
  assert.equal(disturbanceStatus('Selesai'), 'ok');
  assert.equal(disturbanceStatus('Dalam Penanganan'), 'warning');
  assert.equal(disturbanceStatus('Investigasi'), 'warning');
  assert.equal(disturbanceStatus('lainnya'), 'empty');
});

test('shiftKeyAt sama dengan App\\Support\\Shift (pagi 08–16, sore 16–24, malam 00–08)', () => {
  assert.equal(shiftKeyAt(0), 'malam');
  assert.equal(shiftKeyAt(7), 'malam');
  assert.equal(shiftKeyAt(8), 'pagi');
  assert.equal(shiftKeyAt(15), 'pagi');
  assert.equal(shiftKeyAt(16), 'sore');
  assert.equal(shiftKeyAt(23), 'sore');
});

test('formatDays: koma desimal Indonesia + perkiraan jam bila < 1 hari', () => {
  assert.deepEqual(formatDays(0.17), { value: '0,17', approx: '±4 jam' });
  assert.deepEqual(formatDays(12.5), { value: '12,5', approx: null });
  assert.deepEqual(formatDays(0), { value: '0', approx: null });
  assert.deepEqual(formatDays(null), { value: null, approx: null });
});

test('formatQty: kosong tetap null (ditampilkan "—"), nol tetap "0"', () => {
  assert.equal(formatQty(null), null);
  assert.equal(formatQty(''), null);
  assert.equal(formatQty(0), '0');
  assert.equal(formatQty(12450.26, 0), '12.450');
});

test('fuelStatus sama dengan App\\Support\\Status::fuel() (data ada)', () => {
  assert.equal(fuelStatus(0), 'critical');
  assert.equal(fuelStatus(5), 'critical');
  assert.equal(fuelStatus(5.1), 'warning');
  assert.equal(fuelStatus(10), 'warning');
  assert.equal(fuelStatus(10.5), 'ok');
});
