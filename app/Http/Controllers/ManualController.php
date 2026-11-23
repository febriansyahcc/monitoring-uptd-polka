<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

/**
 * Manual Book (Panduan Penggunaan). Isi panduan ada di resources/js/utils/manual.js
 * dan disaring per izin di frontend, jadi controller hanya merender halaman.
 */
class ManualController extends Controller
{
    public function index()
    {
        return Inertia::render('Manual/Index');
    }
}
