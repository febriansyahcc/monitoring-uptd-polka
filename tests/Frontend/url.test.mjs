// Unit test util query URL (tab aktif dipertahankan lewat ?tab=).
//   node --test tests/Frontend/*.test.mjs
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { queryParam } from '../../resources/js/utils/url.js';

const tabs = ['engine', 'penyulang'];

test('queryParam membaca tab yang valid dari URL relatif Inertia', () => {
  assert.equal(queryParam('/monitoring-kwh?month=2026-08&tab=penyulang', 'tab', tabs, 'engine'), 'penyulang');
});

test('queryParam memakai fallback bila tab tidak ada atau tidak dikenal', () => {
  assert.equal(queryParam('/monitoring-kwh?month=2026-08', 'tab', tabs, 'engine'), 'engine');
  assert.equal(queryParam('/monitoring-kwh?tab=<script>', 'tab', tabs, 'engine'), 'engine');
});
