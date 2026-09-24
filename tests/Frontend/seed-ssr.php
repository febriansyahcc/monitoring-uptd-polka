<?php

/**
 * Siapkan database untuk tests/Frontend/ssr-pages.mjs: migrate:fresh + seeder user/feeder,
 * lalu satu contoh data per modul yang dikirim lewat controller aplikasi (POST sebagai admin),
 * sehingga validasi & perhitungan asli ikut berjalan dan tabel punya baris (tombol Edit/Hapus).
 *
 *   php tests/Frontend/seed-ssr.php            # database default: laravel_testing
 *   SSR_DB=nama_db php tests/Frontend/seed-ssr.php
 *
 * Hanya mau berjalan pada database yang namanya mengandung "test" (migrate:fresh menghapus semua tabel).
 */

$database = getenv('SSR_DB') ?: 'laravel_testing';
if (!str_contains($database, 'test')) {
    fwrite(STDERR, "Menolak: database '{$database}' bukan database tes.\n");
    exit(1);
}

// APP_ENV=testing -> middleware CSRF dilewati untuk request internal di bawah
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => $database, 'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array'] as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $_SERVER[$key] = $value;
}

chdir(__DIR__.'/../..');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->instance('request', Illuminate\Http\Request::create('/'));
$kernel->bootstrap();

use App\Http\Controllers\EngineOperationController;
use App\Models\Feeder;
use App\Models\KwhEngineLog;
use App\Models\KwhFeederLog;
use Illuminate\Support\Facades\Artisan;

Artisan::call('migrate:fresh', ['--force' => true]);
Artisan::call('db:seed', ['--class' => 'FeederSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'UserSeeder', '--force' => true]);

$admin = App\Models\User::where('email', 'admin@pln.co.id')->firstOrFail();
$today = now()->toDateString();
$feederId = Feeder::where('is_active', true)->orderBy('sort_order')->value('id');
$engine = array_key_first(EngineOperationController::ENGINES);

$posts = [
    '/monitoring-arus' => ['date' => $today, 'shift' => 'pagi', 'time_interval' => '08.30', 'values' => [$feederId => 120.5]],
    '/monitoring-kwh/engine' => ['recorded_date' => $today, 'engine' => array_key_first(KwhEngineLog::ENGINES), 'stand_akhir' => 1000],
    '/monitoring-kwh/penyulang' => ['recorded_date' => $today, 'feeder' => array_key_first(KwhFeederLog::FEEDERS), 'pm800_ex' => 10],
    '/monitoring-operasi-engine/control-panel' => ['recorded_date' => $today, 'recorded_time' => '08:00', 'engine' => $engine, 'kw' => 100],
    '/monitoring-operasi-engine/engine-area' => ['recorded_date' => $today, 'recorded_time' => '08:00', 'engine' => $engine, 'turbo_speed_r' => 10],
    '/monitoring-gangguan' => ['event_date' => $today, 'event_time' => '10:00', 'disturbance_type' => 'Trip Feeder', 'status' => 'Investigasi', 'description' => 'Contoh'],
    '/monitoring-bbm' => ['recorded_date' => $today, 'daily_consumption' => 100, 'main_tank' => 5000, 'death_stock' => 200, 'unloading' => 0, 'estimated_daily_consumption' => 100],
];

$failed = 0;
foreach ($posts as $url => $payload) {
    $app['auth']->guard('web')->setUser($admin);
    $request = Illuminate\Http\Request::create($url, 'POST', $payload);
    $response = $kernel->handle($request);
    $errors = $app['session.store']->get('errors');
    $ok = $response->getStatusCode() === 302 && !$errors;
    echo ($ok ? 'OK  ' : 'FAIL')."  POST {$url} -> {$response->getStatusCode()}".($errors ? ' '.json_encode($errors->getMessages()) : '')."\n";
    $failed += $ok ? 0 : 1;
    $app['session.store']->forget('errors');
    $kernel->terminate($request, $response);
}

exit($failed ? 1 : 0);
