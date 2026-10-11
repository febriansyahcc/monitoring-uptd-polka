// Warna untuk ApexCharts. ApexCharts tidak membaca kelas Tailwind, jadi nilainya diambil dari
// variabel CSS token (resources/css/app.css). Panggil di dalam computed yang bergantung pada
// isDarkMode agar opsi grafik dihitung ulang saat tema berganti.

const FALLBACK = {
  light: { text: '#15181d', muted: '#5b636e', subtle: '#858d98', border: '#e3e6ea', surface: '#ffffff', brand: '#0a66b2' },
  dark: { text: '#e8eaed', muted: '#a3aab4', subtle: '#6f7782', border: '#2a3039', surface: '#161a20', brand: '#4c9be0' },
};

const cssVar = (name, fallback) => {
  if (typeof document === 'undefined') return fallback;
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
};

/** Token netral + brand untuk tema saat ini. */
export const chartTokens = (isDark) => {
  const f = isDark ? FALLBACK.dark : FALLBACK.light;
  return {
    text: cssVar('--text', f.text),
    muted: cssVar('--text-muted', f.muted),
    subtle: cssVar('--text-subtle', f.subtle),
    border: cssVar('--border', f.border),
    surface: cssVar('--surface', f.surface),
    brand: cssVar('--brand', f.brand),
  };
};

// Palet kategorikal KHUSUS di dalam grafik (seri ke-1, ke-2, ...). Jangan dipakai di luar chart.
// Urutan dipilih agar seri berdekatan mudah dibedakan; seri pertama = warna brand.
export const CHART_SERIES = {
  light: ['#0a66b2', '#c2620a', '#2f8f6a', '#7a4fb3', '#b0436e', '#4b7f99', '#8a7a1f', '#5a6472', '#d08a3c', '#3f5fbf', '#6e9e3a', '#9c4f3a'],
  dark: ['#4c9be0', '#e49a52', '#5cc38f', '#a98be0', '#e07aa3', '#7fb3cc', '#c9b75a', '#9aa4b2', '#f0b77a', '#7d97e8', '#9bc96a', '#d48a73'],
};

export const seriesColors = (isDark, count = CHART_SERIES.light.length) => {
  const palette = isDark ? CHART_SERIES.dark : CHART_SERIES.light;
  return Array.from({ length: count }, (_, i) => palette[i % palette.length]);
};

/** Opsi dasar yang sama untuk semua grafik: font, warna teks, grid, tooltip. */
export const baseChartOptions = (isDark) => {
  const t = chartTokens(isDark);
  return {
    chart: {
      fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
      foreColor: t.muted,
      toolbar: { show: false },
      background: 'transparent',
      animations: { enabled: true, speed: 300 },
    },
    grid: { borderColor: t.border, strokeDashArray: 3 },
    xaxis: {
      labels: { style: { colors: t.muted, fontSize: '12px' } },
      axisBorder: { color: t.border },
      axisTicks: { color: t.border },
    },
    yaxis: { labels: { style: { colors: t.muted, fontSize: '12px' } } },
    legend: { labels: { colors: t.muted }, fontSize: '12px' },
    tooltip: { theme: isDark ? 'dark' : 'light' },
    dataLabels: { enabled: false },
  };
};
