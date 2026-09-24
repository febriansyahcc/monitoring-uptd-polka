<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CurrentMonitoringController;
use App\Http\Controllers\KwhProductionController;
use App\Http\Controllers\EngineOperationController;
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
    Route::get('/monitoring-arus', [CurrentMonitoringController::class, 'index'])->name('monitoring-arus.index')->middleware('permission:monitoring_arus.view');
    Route::post('/monitoring-arus', [CurrentMonitoringController::class, 'storeOrUpdate'])->name('monitoring-arus.store')->middleware('permission:monitoring_arus.input');

    // Monitoring kWh Produksi
    Route::get('/monitoring-kwh', [KwhProductionController::class, 'index'])->name('kwh-production.index')->middleware('permission:monitoring_kwh.view');
    Route::middleware('permission:monitoring_kwh.input')->group(function () {
        Route::post('/monitoring-kwh/engine', [KwhProductionController::class, 'storeEngine'])->name('kwh-production.engine.store');
        Route::delete('/monitoring-kwh/engine/{id}', [KwhProductionController::class, 'destroyEngine'])->name('kwh-production.engine.destroy');
        Route::post('/monitoring-kwh/penyulang', [KwhProductionController::class, 'storeFeeder'])->name('kwh-production.feeder.store');
        Route::delete('/monitoring-kwh/penyulang/{id}', [KwhProductionController::class, 'destroyFeeder'])->name('kwh-production.feeder.destroy');
    });

    // Monitoring Operasi Engine
    Route::get('/monitoring-operasi-engine', [EngineOperationController::class, 'index'])->name('engine-operation.index')->middleware('permission:monitoring_engine.view');
    Route::middleware('permission:monitoring_engine.input')->group(function () {
        Route::post('/monitoring-operasi-engine/control-panel', [EngineOperationController::class, 'storeControlPanel'])->name('engine-operation.control-panel.store');
        Route::delete('/monitoring-operasi-engine/control-panel/{id}', [EngineOperationController::class, 'destroyControlPanel'])->name('engine-operation.control-panel.destroy');
        Route::post('/monitoring-operasi-engine/engine-area', [EngineOperationController::class, 'storeEngineArea'])->name('engine-operation.engine-area.store');
        Route::delete('/monitoring-operasi-engine/engine-area/{id}', [EngineOperationController::class, 'destroyEngineArea'])->name('engine-operation.engine-area.destroy');
    });

    // Monitoring Gangguan Operasional
    Route::get('/monitoring-gangguan', [DisturbanceMonitoringController::class, 'index'])->name('disturbances.index')->middleware('permission:monitoring_gangguan.view');
    Route::middleware('permission:monitoring_gangguan.manage')->group(function () {
        Route::post('/monitoring-gangguan', [DisturbanceMonitoringController::class, 'storeOrUpdate'])->name('disturbances.store');
        Route::delete('/monitoring-gangguan/{id}', [DisturbanceMonitoringController::class, 'destroy'])->name('disturbances.destroy');
    });

    // Monitoring Stok & Pemakaian BBM
    Route::get('/monitoring-bbm', [FuelStockController::class, 'index'])->name('fuel.index')->middleware('permission:monitoring_bbm.view');
    Route::middleware('permission:monitoring_bbm.input')->group(function () {
        Route::post('/monitoring-bbm', [FuelStockController::class, 'storeOrUpdate'])->name('fuel.store');
        Route::delete('/monitoring-bbm/{id}', [FuelStockController::class, 'destroy'])->name('fuel.destroy');
    });

    // User Role Management & PBAC (Admin)
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
        Route::post('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});
