<?php

use App\Http\Controllers\AdminCabangController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\StokController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:kasir,admin_cabang')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.checkout');
    });

    Route::middleware('role:admin_cabang')->prefix('admin/stok')->name('admin.stok.')->group(function () {
        Route::get('/', [StokController::class, 'index'])->name('index');
        Route::post('/masuk', [StokController::class, 'store'])->name('store');
    });

    Route::get('/admin-cabang/dashboard', [AdminCabangController::class, 'index'])
        ->middleware('role:admin_cabang')
        ->name('admin.cabang.dashboard');
});
