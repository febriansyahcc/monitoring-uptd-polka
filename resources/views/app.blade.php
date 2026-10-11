<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title inertia>{{ config('app.name', 'PLN Monitor ULPLTD POKA') }}</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
  </head>
  <body class="h-full font-sans antialiased bg-bg text-fg">
    @inertia
  </body>
</html>
