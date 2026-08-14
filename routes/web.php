<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CurrentMonitoringController;
use App\Http\Controllers\KwhProductionController;
use App\Http\Controllers\DisturbanceMonitoringController;
use App\Http\Controllers\FuelStockController;
use App\Http\Controllers\UserManagementController;

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
});

// Logout Route (Auth)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Operational Routes (Auth + PBAC Middleware)
Route::middleware(['auth'])->group(function () {
    // Dashboard Utama (Eksekutif)
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Monitoring Arus
    Route::get('/monitoring-arus', [CurrentMonitoringController::class, 'index'])->name('monitoring-arus.index');
    Route::post('/monitoring-arus', [CurrentMonitoringController::class, 'storeOrUpdate'])->name('monitoring-arus.store');

    // Monitoring kWh Produksi
    Route::get('/monitoring-kwh', [KwhProductionController::class, 'index'])->name('kwh-production.index');
    Route::post('/monitoring-kwh', [KwhProductionController::class, 'storeOrUpdate'])->name('kwh-production.store');
    Route::delete('/monitoring-kwh/{id}', [KwhProductionController::class, 'destroy'])->name('kwh-production.destroy');

    // Monitoring Gangguan Operasional
    Route::get('/monitoring-gangguan', [DisturbanceMonitoringController::class, 'index'])->name('disturbances.index');
    Route::post('/monitoring-gangguan', [DisturbanceMonitoringController::class, 'storeOrUpdate'])->name('disturbances.store');
    Route::delete('/monitoring-gangguan/{id}', [DisturbanceMonitoringController::class, 'destroy'])->name('disturbances.destroy');

    // Monitoring Stok & Pemakaian BBM
    Route::get('/monitoring-bbm', [FuelStockController::class, 'index'])->name('fuel.index');
    Route::post('/monitoring-bbm', [FuelStockController::class, 'storeOrUpdate'])->name('fuel.store');
    Route::delete('/monitoring-bbm/{id}', [FuelStockController::class, 'destroy'])->name('fuel.destroy');

    // User Role Management & PBAC (Admin)
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
});
