// Membuat Manual Book versi PDF dari isi yang sama dengan halaman /panduan.
//
//   npm run manual:pdf
//
// Hasil: docs/manual-book.html dan docs/manual-book.pdf (lengkap, semua modul & semua peran).
// PDF dicetak dengan Chrome/Edge headless; atur CHROME_PATH bila browser tidak ditemukan.
import { mkdirSync, writeFileSync, existsSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { dirname, resolve, relative, sep } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { MANUAL_META, MANUAL_SECTIONS, richParts } from '../resources/js/utils/manual.js';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const outDir = resolve(root, 'docs');
const htmlPath = resolve(outDir, 'manual-book.html');
const pdfPath = resolve(outDir, 'manual-book.pdf');

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

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const rich = (s) => richParts(s).map((p) => (p.bold ? `<strong>${esc(p.text)}</strong>` : esc(p.text))).join('');

const requiresTag = (slug) => (slug ? `<span class="tag">Perlu izin: ${esc(PERMISSION_LABELS[slug] ?? slug)}</span>` : '');

let figure = 0;
const renderBlock = (b) => {
  const title = b.title ? `<h3>${esc(b.title)}${requiresTag(b.requires)}</h3>` : b.requires ? `<p>${requiresTag(b.requires)}</p>` : '';
  switch (b.type) {
    case 'p':
      return `<div class="block">${title}<p>${rich(b.text)}</p></div>`;
    case 'steps':
      return `<div class="block">${title}<ol>${b.items.map((i) => `<li>${rich(i)}</li>`).join('')}</ol></div>`;
    case 'list':
      return `<div class="block">${title}<ul>${b.items.map((i) => `<li>${rich(i)}</li>`).join('')}</ul></div>`;
    case 'table':
      return `<div class="block">${title}<table><thead><tr>${b.head.map((h) => `<th>${esc(h)}</th>`).join('')}</tr></thead><tbody>${b.rows
        .map((r) => `<tr>${r.map((c) => `<td>${rich(c)}</td>`).join('')}</tr>`)
        .join('')}</tbody></table></div>`;
    case 'image': {
      // src "/images/manual/x.png" → file di public/, relatif terhadap docs/manual-book.html
      const file = resolve(root, 'public', b.src.replace(/^\//, ''));
      if (!existsSync(file)) {
        console.warn(`  ! gambar tidak ditemukan: ${b.src} (jalankan npm run manual:screenshots)`);
        return '';
      }
      const rel = relative(outDir, file).split(sep).join('/');
      return `<figure class="block${b.mobile ? ' mobile' : ''}">${b.requires ? `<p>${requiresTag(b.requires)}</p>` : ''}<img src="${esc(rel)}" alt="${esc(b.caption)}"><figcaption>Gambar ${++figure}. ${esc(b.caption)}</figcaption></figure>`;
    }
    case 'note':
      return `<div class="block">${title}<div class="note ${b.tone === 'warn' ? 'warn' : 'info'}"><b>${b.tone === 'warn' ? 'Perhatian' : 'Catatan'}:</b> ${rich(b.text)}</div></div>`;
    default:
      return '';
  }
};

const sectionsHtml = MANUAL_SECTIONS.map(
  (s, i) => `
  <section id="${s.id}">
    <h2><span class="num">${i + 1}</span>${esc(s.title)}</h2>
    <p class="summary">${esc(s.summary)}${s.permission ? ` <span class="tag">Akses: ${esc(PERMISSION_LABELS[s.permission])}</span>` : ''}</p>
    ${s.blocks.map(renderBlock).join('\n')}
  </section>`
).join('\n');

const tocHtml = MANUAL_SECTIONS.map((s, i) => `<li><a href="#${s.id}"><span>${i + 1}. ${esc(s.title)}</span><span class="dots"></span></a></li>`).join('');

const html = `<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>${esc(MANUAL_META.title)}</title>
<style>
  @page { size: A4; margin: 18mm 16mm 20mm; }
  * { box-sizing: border-box; }
  body { font-family: "Segoe UI", Arial, sans-serif; color: #15181d; font-size: 10.5pt; line-height: 1.55; margin: 0; }
  .cover { height: 250mm; display: flex; flex-direction: column; justify-content: space-between; break-after: page; }
  .cover .brand { display: inline-flex; align-items: center; justify-content: center; width: 22mm; height: 22mm; border-radius: 4mm; background: #0a66b2; color: #fff; font-weight: 700; font-size: 16pt; letter-spacing: .5pt; }
  .cover h1 { font-size: 30pt; line-height: 1.15; margin: 14mm 0 4mm; color: #0a3f6e; }
  .cover .sub { font-size: 13pt; color: #3d444d; max-width: 140mm; }
  .cover .bar { height: 3mm; width: 40mm; background: #0a66b2; margin-top: 10mm; border-radius: 2mm; }
  .cover dl { display: grid; grid-template-columns: 38mm 1fr; gap: 2mm 6mm; font-size: 10pt; color: #3d444d; margin: 0; }
  .cover dt { font-weight: 600; color: #15181d; }
  .toc { break-after: page; }
  .toc h2 { font-size: 18pt; color: #0a3f6e; margin: 0 0 6mm; }
  .toc ol { list-style: none; padding: 0; margin: 0; }
  .toc li { margin: 0 0 3mm; font-size: 11.5pt; }
  .toc a { color: inherit; text-decoration: none; display: flex; gap: 3mm; }
  .toc .dots { flex: 1; border-bottom: 1px dotted #9aa1ab; margin-bottom: 1.2mm; }
  section { break-before: page; }
  section h2 { font-size: 17pt; color: #0a3f6e; margin: 0 0 1mm; display: flex; align-items: center; gap: 3mm; break-after: avoid; }
  .num { display: inline-flex; align-items: center; justify-content: center; width: 9mm; height: 9mm; border-radius: 2mm; background: #e8f1f9; color: #0a5799; font-size: 12pt; }
  .summary { color: #5b636e; margin: 0 0 6mm; padding-bottom: 3mm; border-bottom: 1px solid #d5d9de; }
  .block { margin: 0 0 5mm; break-inside: avoid; }
  h3 { font-size: 11.5pt; margin: 0 0 2mm; break-after: avoid; }
  p { margin: 0 0 2mm; }
  ol, ul { margin: 0; padding-left: 6mm; }
  li { margin: 0 0 1.2mm; }
  table { width: 100%; border-collapse: collapse; font-size: 9.5pt; }
  th { background: #f1f3f5; text-align: left; font-weight: 600; }
  th, td { border: 1px solid #d5d9de; padding: 1.6mm 2.4mm; vertical-align: top; }
  td:first-child { font-weight: 600; }
  tr { break-inside: avoid; }
  .note { border-radius: 2mm; padding: 2.5mm 3.5mm; }
  .note.info { background: #e8f1f9; border-left: 1.2mm solid #0a66b2; }
  .note.warn { background: #fdf3e1; border-left: 1.2mm solid #9a5b00; }
  figure { margin: 0 0 5mm; }
  figure img { display: block; width: 100%; height: auto; border: 1px solid #d5d9de; border-radius: 2mm; }
  figure.mobile img { width: 60mm; }
  figcaption { margin-top: 1.5mm; font-size: 8.5pt; color: #5b636e; font-style: italic; }
  .tag { display: inline-block; margin-left: 2mm; padding: .2mm 2mm; border: 1px solid #c9d8e8; border-radius: 1.5mm; font-size: 8pt; font-weight: 500; color: #0a5799; background: #f4f8fc; vertical-align: middle; }
  * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
</style>
</head>
<body>
  <div class="cover">
    <div>
      <span class="brand">PLN</span>
      <h1>${esc(MANUAL_META.title)}</h1>
      <p class="sub">${esc(MANUAL_META.subtitle)}</p>
      <div class="bar"></div>
    </div>
    <dl>
      <dt>Aplikasi</dt><dd>PLN Monitor — ULPLTD Poka</dd>
      <dt>Versi dokumen</dt><dd>${esc(MANUAL_META.version)}</dd>
      <dt>Diperbarui</dt><dd>${esc(MANUAL_META.updated)}</dd>
      <dt>Versi daring</dt><dd>Menu <b>Panduan</b> di dalam aplikasi</dd>
    </dl>
  </div>
  <nav class="toc">
    <h2>Daftar Isi</h2>
    <ol>${tocHtml}</ol>
  </nav>
  ${sectionsHtml}
</body>
</html>`;

mkdirSync(outDir, { recursive: true });
writeFileSync(htmlPath, html, 'utf8');
console.log(`HTML : ${htmlPath}`);

const candidates = [
  process.env.CHROME_PATH,
  'C:/Program Files/Google/Chrome/Application/chrome.exe',
  'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
  '/usr/bin/google-chrome',
  '/usr/bin/chromium',
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
].filter(Boolean);
const browser = candidates.find((p) => existsSync(p));
if (!browser) {
  console.error('Chrome/Edge tidak ditemukan. Atur CHROME_PATH, atau buka file HTML di atas lalu Cetak → Simpan sebagai PDF.');
  process.exit(1);
}

execFileSync(
  browser,
  ['--headless=new', '--disable-gpu', '--no-pdf-header-footer', `--print-to-pdf=${pdfPath}`, pathToFileURL(htmlPath).href],
  { stdio: 'ignore', timeout: 60000 }
);
console.log(`PDF  : ${pdfPath}`);
