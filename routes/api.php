<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\DisponibilidadController;
use App\Http\Controllers\Api\HabitacionController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

// AUTENTICACIÓN (sin auth:sanctum)
Route::post('/auth/register', [AuthController::class, 'register'])->name('api.auth.register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

// Rutas protegidas
Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

    // USUARIOS
    Route::apiResource('usuarios', UsuarioController::class);

    // CLIENTES
    Route::apiResource('clientes', ClienteController::class);

    // HABITACIONES
    Route::apiResource('habitaciones', HabitacionController::class);
    Route::post('/habitaciones/{id}/duplicar', [HabitacionController::class, 'duplicar'])->name('habitaciones.duplicar');
    Route::get('/habitaciones/disponibles/search', [DisponibilidadController::class, 'search'])->name('habitaciones.disponibles.search');

    // RESERVAS
    Route::apiResource('reservas', ReservaController::class);
    Route::post('/reservas/{id}/confirmar', [ReservaController::class, 'confirmar'])->name('reservas.confirmar');
    Route::post('/reservas/{id}/servicios', [ReservaController::class, 'agregarServicio'])->name('reservas.servicios.agregar');
    Route::delete('/reservas/{id}/servicios/{servicio_id}', [ReservaController::class, 'quitarServicio'])->name('reservas.servicios.quitar');
    Route::get('/reservas/disponibilidad/check', [DisponibilidadController::class, 'verificar'])->name('reservas.disponibilidad.check');

    // SERVICIOS
    Route::apiResource('servicios', ServicioController::class);

    // PAGOS
    Route::apiResource('pagos', PagoController::class);
    Route::post('/pagos/{id}/refund', [PagoController::class, 'refund'])->name('pagos.refund');

    // NOTIFICACIONES
    Route::apiResource('notificaciones', NotificacionController::class);
    Route::put('/notificaciones/{id}/marcar-leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.marcar-leida');
    Route::get('/notificaciones/reenviar/{id}', [NotificacionController::class, 'reenviar'])->name('notificaciones.reenviar');

    // CONFIGURACIÓN (admin)
    Route::get('/config', [ConfiguracionController::class, 'obtener'])->name('config.obtener');
    Route::put('/config', [ConfiguracionController::class, 'actualizar'])->name('config.actualizar');
});
