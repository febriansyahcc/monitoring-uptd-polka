// Menghasilkan halaman Markdown VitePress per bab dari sumber tunggal:
//   resources/js/utils/manual.js
//
//   npm run docs:sync
//   npm run docs:build
//
// Hasil Markdown ditulis ke docs-src/ dan aset gambar disalin dari public/images/manual/
// ke docs-src/public/images/manual/ agar hasil build VitePress di public/docs/ mandiri.
import { mkdirSync, writeFileSync, copyFileSync, existsSync, readdirSync, rmSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { MANUAL_META, MANUAL_SECTIONS } from '../resources/js/utils/manual.js';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const srcDir = resolve(root, 'docs-src');
const publicImgDir = resolve(root, 'public/images/manual');
const docsImgDir = resolve(srcDir, 'public/images/manual');
const pdfSrc = resolve(root, 'docs/manual-book.pdf');
const pdfDest = resolve(srcDir, 'public/manual-book.pdf');

const PERMISSION_LABELS = {
  'monitoring_arus.view': 'Lihat Monitoring Arus',
  'monitoring_arus.input': 'Input/Edit Monitoring Arus',
  'monitoring_kwh.view': 'Lihat Monitoring kWh',
  'monitoring_kwh.input': 'Input/Edit Monitoring kWh',
  'monitoring_engine.view': 'Lihat Monitoring Operasi Engine',
  'monitoring_engine.input': 'Input/Edit Monitoring Operasi Engine',
  'monitoring_gangguan.view': 'Lihat Monitoring Gangguan',
  'monitoring_gangguan.manage': 'Kelola Monitoring Gangguan',
  'monitoring_bbm.view': 'Lihat Monitoring BBM',
  'monitoring_bbm.input': 'Input/Edit Monitoring BBM',
  'users.manage': 'Pengelolaan Pengguna',
};

const mdSafe = (s) =>
  String(s)
    .replace(/<([a-zA-Z!/])/g, '&lt;$1')
    .replace(/\{\{/g, '&#123;&#123;');

const mdCell = (s) => mdSafe(s).replace(/\|/g, '\\|').replace(/\r?\n/g, ' ');

const permBadge = (slug) =>
  slug ? `<span class="manual-badge">Perlu izin: ${mdSafe(PERMISSION_LABELS[slug] ?? slug)}</span>` : '';

const renderBlock = (b) => {
  const parts = [];
  if (b.title) {
    parts.push(`## ${mdSafe(b.title)}${b.requires ? ` ${permBadge(b.requires)}` : ''}`);
  } else if (b.requires) {
    parts.push(permBadge(b.requires));
  }

  switch (b.type) {
    case 'p':
      parts.push(mdSafe(b.text));
      break;
    case 'steps':
      parts.push(b.items.map((item, idx) => `${idx + 1}. ${mdSafe(item)}`).join('\n'));
      break;
    case 'list':
      parts.push(b.items.map((item) => `- ${mdSafe(item)}`).join('\n'));
      break;
    case 'table': {
      const head = `| ${b.head.map(mdCell).join(' | ')} |`;
      const sep = `| ${b.head.map(() => '---').join(' | ')} |`;
      const rows = b.rows.map((r) => `| ${r.map(mdCell).join(' | ')} |`).join('\n');
      parts.push([head, sep, rows].join('\n'));
      break;
    }
    case 'image': {
      const caption = mdSafe(b.caption);
      const cls = b.mobile ? 'manual-figure is-mobile' : 'manual-figure';
      parts.push(
        `<figure class="${cls}">\n\n![${caption}](${b.src})\n\n<figcaption>${caption}</figcaption>\n</figure>`
      );
      break;
    }
    case 'note': {
      const kind = b.tone === 'warn' ? 'warning' : 'info';
      const label = b.tone === 'warn' ? 'Perhatian' : 'Catatan';
      parts.push(`::: ${kind} ${label}\n${mdSafe(b.text)}\n:::`);
      break;
    }
    default:
      break;
  }

  return parts.join('\n\n');
};

mkdirSync(srcDir, { recursive: true });

// Salin screenshot agar tersedia di dalam output statis /docs/
if (existsSync(publicImgDir)) {
  rmSync(docsImgDir, { recursive: true, force: true });
  mkdirSync(docsImgDir, { recursive: true });
  for (const file of readdirSync(publicImgDir)) {
    if (/\.(png|jpe?g|webp|svg)$/i.test(file)) {
      copyFileSync(resolve(publicImgDir, file), resolve(docsImgDir, file));
    }
  }
}

// Salin PDF jika sudah dibuat agar tombol Unduh PDF di /docs/ selalu berfungsi
if (existsSync(pdfSrc)) {
  mkdirSync(resolve(srcDir, 'public'), { recursive: true });
  copyFileSync(pdfSrc, pdfDest);
}

// Halaman beranda (/docs/)
const features = MANUAL_SECTIONS.map(
  (s, i) =>
    `  - title: "${i + 1}. ${s.title.replace(/"/g, '\\"')}"\n    details: "${s.summary.replace(/"/g, '\\"')}"\n    link: /${s.id}`
).join('\n');

const hasPdf = existsSync(pdfDest);
const pdfAction = hasPdf
  ? `\n    - theme: alt\n      text: Unduh Versi PDF\n      link: /docs/manual-book.pdf\n      target: _blank`
  : '';

const indexMd = `---
layout: home

hero:
  name: "${MANUAL_META.title}"
  text: "Panduan Penggunaan"
  tagline: "${MANUAL_META.subtitle} — Versi ${MANUAL_META.version} (${MANUAL_META.updated})"
  actions:
    - theme: brand
      text: Mulai Membaca
      link: /pendahuluan${pdfAction}
    - theme: alt
      text: Kembali ke Aplikasi
      link: /../
      target: _self

features:
${features}
---
`;
writeFileSync(resolve(srcDir, 'index.md'), indexMd, 'utf8');

// Halaman per bab (11 file .md)
MANUAL_SECTIONS.forEach((section, index) => {
  const num = index + 1;
  const accessBadge = section.permission
    ? `<span class="manual-badge">Akses: ${mdSafe(PERMISSION_LABELS[section.permission] ?? section.permission)}</span>`
    : '<span class="manual-badge is-public">Akses: Semua Pengguna</span>';

  const body = section.blocks.map(renderBlock).filter(Boolean).join('\n\n');
  const md = `---
title: "${num}. ${section.title.replace(/"/g, '\\"')}"
description: "${section.summary.replace(/"/g, '\\"')}"
---

# ${num}. ${mdSafe(section.title)}

${mdSafe(section.summary)} ${accessBadge}

${body}
`;
  writeFileSync(resolve(srcDir, `${section.id}.md`), md, 'utf8');
});

console.log(`Docs synced: ${MANUAL_SECTIONS.length} bab + index.md -> ${srcDir}`);
