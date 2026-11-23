# Membuat paket upload untuk shared hosting (tanpa SSH).
#   pltdpoka-app.zip     -> diekstrak ke  ~/pltdpoka/                       (di luar public_html)
#   pltdpoka-public.zip  -> diekstrak ke  ~/public_html/pltdpoka.my.id/     (document root domain)
#
# Jalankan dari root proyek:  powershell -ExecutionPolicy Bypass -File deploy\build-package.ps1
# Salinan dikerjakan di deploy\dist, folder vendor/ & node_modules proyek TIDAK disentuh.

param(
    [string]$AppDirName = 'pltdpoka',                 # nama folder aplikasi di home hosting
    [string]$DocRootName = 'pltdpoka.my.id'           # nama folder document root di public_html
)

$ErrorActionPreference = 'Stop'
$root = Resolve-Path (Join-Path $PSScriptRoot '..')
$dist = Join-Path $root 'deploy\dist'
$app = Join-Path $dist 'app'
$pub = Join-Path $dist 'public'

Set-Location $root

Write-Host '==> 1/5 Build aset frontend (vite)'
npm run build
if ($LASTEXITCODE -ne 0) { throw 'npm run build gagal' }

Write-Host '==> 2/5 Salin file aplikasi'
if (Test-Path $dist) { Remove-Item $dist -Recurse -Force }
New-Item -ItemType Directory -Path $app, $pub | Out-Null

foreach ($dir in 'app', 'bootstrap', 'config', 'database', 'routes', 'resources\views') {
    robocopy (Join-Path $root $dir) (Join-Path $app $dir) /E /NFL /NDL /NJH /NJS /NP /XF *.sqlite *.log | Out-Null
}
foreach ($file in 'artisan', 'composer.json', 'composer.lock') {
    Copy-Item (Join-Path $root $file) $app
}
Remove-Item (Join-Path $app 'bootstrap\cache\*') -Recurse -Force -Exclude '.gitignore' -ErrorAction SilentlyContinue
foreach ($dir in 'storage\app\public', 'storage\framework\cache\data', 'storage\framework\sessions', 'storage\framework\views', 'storage\logs') {
    New-Item -ItemType Directory -Path (Join-Path $app $dir) -Force | Out-Null
}

Write-Host '==> 3/5 composer install --no-dev (tanpa paket pengembangan)'
Push-Location $app
composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
if ($LASTEXITCODE -ne 0) { throw 'composer install gagal' }
php artisan package:discover --ansi
Pop-Location

Write-Host '==> 4/5 Siapkan folder public'
robocopy (Join-Path $root 'public') $pub /E /NFL /NDL /NJH /NJS /NP /XF build.zip hot | Out-Null
$index = Join-Path $pub 'index.php'
$code = Get-Content $index -Raw
# public_html/<docroot>/index.php -> ../../<app>  = ~/<app>
$code = $code.Replace("__DIR__.'/../storage", "__DIR__.'/../../$AppDirName/storage")
$code = $code.Replace("__DIR__.'/../vendor", "__DIR__.'/../../$AppDirName/vendor")
$code = $code.Replace("__DIR__.'/../bootstrap", "__DIR__.'/../../$AppDirName/bootstrap")
Set-Content $index $code -NoNewline -Encoding utf8

Write-Host '==> 5/5 Zip'
# tar (bsdtar bawaan Windows 10+) memakai "/" sebagai pemisah folder; Compress-Archive PS 5.1 memakai "\"
# sehingga nama file rusak saat diekstrak di cPanel (Linux).
tar -a -c -f (Join-Path $dist 'pltdpoka-app.zip') -C $app .
tar -a -c -f (Join-Path $dist 'pltdpoka-public.zip') -C $pub .

Get-ChildItem $dist -Filter *.zip | ForEach-Object { '{0}  {1:N1} MB' -f $_.Name, ($_.Length / 1MB) }
Write-Host 'Selesai. Lanjut ikuti deploy\PANDUAN-DEPLOY.md'
