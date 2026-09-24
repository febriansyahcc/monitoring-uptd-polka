<?php

/**
 * Ambil objek page Inertia (JSON) setiap route, dipakai tests/Frontend/ssr-pages.mjs.
 *
 *   php tests/Frontend/dump-pages.php <email|-> <url> [<url> ...]
 *
 * `-` = tanpa login (halaman tamu). Hanya melakukan GET (read-only). Database diambil dari env,
 * jadi jalankan dengan DB_DATABASE=laravel_testing SESSION_DRIVER=array (ssr-pages.mjs sudah mengaturnya).
 */

chdir(__DIR__.'/../..');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->instance('request', Illuminate\Http\Request::create('/'));
$kernel->bootstrap();

$email = $argv[1];
$urls = array_slice($argv, 2);
$out = [];

foreach ($urls as $url) {
    if ($email !== '-') {
        $app['auth']->guard('web')->setUser(App\Models\User::where('email', $email)->firstOrFail());
    }

    $request = Illuminate\Http\Request::create($url, 'GET');
    $response = $kernel->handle($request);
    $html = $response->getContent();

    $json = null;
    if (preg_match('#<script[^>]*data-page="app"[^>]*>(.*?)</script>#s', $html, $m)) {
        $json = $m[1];
    } elseif (preg_match('#data-page="([^"]*)"#', $html, $m)) {
        $json = html_entity_decode($m[1], ENT_QUOTES);
    }
    $out[$url] = ['status' => $response->getStatusCode(), 'page' => $json ? json_decode($json, true) : null];
    $kernel->terminate($request, $response);
}

echo json_encode($out);
