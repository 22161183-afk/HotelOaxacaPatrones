<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cliente\ClienteDashboardController;
use App\Http\Controllers\HabitacionController;
use Illuminate\Support\Facades\Route;

// ============================================================
// RUTAS PÚBLICAS (SIN AUTENTICACIÓN)
// ============================================================

Route::redirect('/', '/login');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
Route::post('/register', [AuthController::class, 'register']);

// ============================================================
// RUTA PÚBLICA - Ver Detalles de Habitación
// ============================================================

Route::get('/habitaciones/{id}', [HabitacionController::class, 'show'])->name('habitaciones.show');

// ============================================================
// RUTAS PROTEGIDAS (CON AUTENTICACIÓN)
// ============================================================

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

// ============================================================
// RUTAS DE ADMINISTRADOR
// ============================================================

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('reservas')->name('reservas.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'reservas'])->name('index');
    });

    Route::prefix('habitaciones')->name('habitaciones.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'habitaciones'])->name('index');
        Route::get('/{id}/edit', [AdminDashboardController::class, 'editHabitacion'])->name('edit');
        Route::put('/{id}', [AdminDashboardController::class, 'updateHabitacion'])->name('update');
    });

    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'pagos'])->name('index');
    });
});

// ============================================================
// RUTAS DE CLIENTE
// ============================================================

Route::middleware(['auth', 'role:cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/dashboard', [ClienteDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('reservas')->name('reservas.')->group(function () {
        Route::get('/', [ClienteDashboardController::class, 'reservas'])->name('index');
        Route::get('/crear', [ClienteDashboardController::class, 'createReserva'])->name('create');
        Route::post('/', [ClienteDashboardController::class, 'storeReserva'])->name('store');
        Route::put('/{id}/cancelar', [ClienteDashboardController::class, 'cancelarReserva'])->name('cancelar');
    });

    Route::prefix('habitaciones')->name('habitaciones.')->group(function () {
        Route::get('/', [ClienteDashboardController::class, 'habitaciones'])->name('index');
    });

    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::get('/crear', [ClienteDashboardController::class, 'createPago'])->name('create');
        Route::post('/', [ClienteDashboardController::class, 'storePago'])->name('store');
    });
});
