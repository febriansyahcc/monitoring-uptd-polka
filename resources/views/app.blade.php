<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title>PLN Monitoring - Monitoring Arus</title>
    <script>
      // Pasang tema sebelum render agar tidak ada kedip terang saat mode gelap.
      (function () {
        try {
          var saved = localStorage.getItem('pln_theme');
          var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
          if (dark) document.documentElement.classList.add('dark');
        } catch (e) {}
      })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
  </head>
  <body class="h-full font-['Inter',sans-serif] antialiased bg-slate-50 dark:bg-slate-950">
    @inertia
  </body>
</html>
