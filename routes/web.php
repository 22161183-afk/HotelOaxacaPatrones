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
        Route::get('/{id}', [AdminDashboardController::class, 'showReserva'])->name('show');
        Route::post('/{id}/confirmar', [AdminDashboardController::class, 'confirmarReservaCommand'])->name('confirmar');
        Route::post('/{id}/cancelar', [AdminDashboardController::class, 'cancelarReservaCommand'])->name('cancelar');
        Route::post('/{id}/cambiar-habitacion', [AdminDashboardController::class, 'cambiarHabitacionCommand'])->name('cambiar-habitacion');
    });

    Route::prefix('habitaciones')->name('habitaciones.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'habitaciones'])->name('index');
        Route::get('/crear', [AdminDashboardController::class, 'createHabitacion'])->name('create');
        Route::post('/', [AdminDashboardController::class, 'storeHabitacion'])->name('store');
        Route::get('/{id}/edit', [AdminDashboardController::class, 'editHabitacion'])->name('edit');
        Route::put('/{id}', [AdminDashboardController::class, 'updateHabitacion'])->name('update');
        Route::get('/{id}/clonar', [AdminDashboardController::class, 'showClonarForm'])->name('clonar.form');
        Route::post('/{id}/clonar', [AdminDashboardController::class, 'clonarHabitacion'])->name('clonar');
    });

    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'pagos'])->name('index');
    });

    Route::prefix('metodos-pago')->name('metodos-pago.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'metodosPago'])->name('index');
        Route::get('/crear', [AdminDashboardController::class, 'createMetodoPago'])->name('create');
        Route::post('/', [AdminDashboardController::class, 'storeMetodoPago'])->name('store');
        Route::get('/{id}/edit', [AdminDashboardController::class, 'editMetodoPago'])->name('edit');
        Route::put('/{id}', [AdminDashboardController::class, 'updateMetodoPago'])->name('update');
    });

    Route::get('/api-docs', [AdminDashboardController::class, 'apiDocumentation'])->name('api-docs');

    Route::put('/perfil', [AdminDashboardController::class, 'updatePerfil'])->name('perfil.update');
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
        Route::get('/{id}/editar', [ClienteDashboardController::class, 'editReserva'])->name('edit');
        Route::put('/{id}', [ClienteDashboardController::class, 'updateReserva'])->name('update');
        Route::put('/{id}/cancelar', [ClienteDashboardController::class, 'cancelarReserva'])->name('cancelar');
        Route::post('/{id}/aceptar-reembolso', [ClienteDashboardController::class, 'aceptarReembolso'])->name('aceptar-reembolso');
    });

    Route::prefix('habitaciones')->name('habitaciones.')->group(function () {
        Route::get('/', [ClienteDashboardController::class, 'habitaciones'])->name('index');
    });

    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::get('/crear', [ClienteDashboardController::class, 'createPago'])->name('create');
        Route::post('/', [ClienteDashboardController::class, 'storePago'])->name('store');
    });

    Route::put('/perfil', [ClienteDashboardController::class, 'updatePerfil'])->name('perfil.update');
});
