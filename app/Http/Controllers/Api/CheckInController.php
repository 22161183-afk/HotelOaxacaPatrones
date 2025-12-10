<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Patterns\Behavioral\CheckInFactory;
use Illuminate\Http\Request;

/**
 * Controlador de Check-In
 *
 * Maneja el proceso de check-in usando el patrón Template Method
 */
class CheckInController extends Controller
{
    /**
     * Ejecutar proceso de check-in automático
     *
     * El sistema detecta automáticamente el tipo de cliente y ejecuta
     * el proceso de check-in correspondiente
     */
    public function ejecutar(Request $request, $reservaId)
    {
        try {
            $reserva = Reserva::with(['cliente', 'habitacion'])->findOrFail($reservaId);

            // Verificar que la reserva esté confirmada
            if ($reserva->estado !== 'confirmada') {
                return response()->json([
                    'success' => false,
                    'error' => 'Solo se puede hacer check-in a reservas confirmadas',
                ], 400);
            }

            // Usar el Factory para crear el tipo de check-in apropiado
            $checkIn = CheckInFactory::crear($reserva);

            // Ejecutar el proceso de check-in
            $resultado = $checkIn->ejecutarCheckIn();

            if ($resultado->esExitoso()) {
                return response()->json([
                    'success' => true,
                    'mensaje' => 'Check-in completado exitosamente',
                    'resultado' => $resultado->getResumen(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Check-in no completado',
                    'resultado' => $resultado->getResumen(),
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simular proceso de check-in sin guardarlo
     */
    public function simular(Request $request, $reservaId)
    {
        try {
            $reserva = Reserva::with(['cliente', 'habitacion'])->findOrFail($reservaId);

            // Crear el tipo de check-in apropiado
            $checkIn = CheckInFactory::crear($reserva);

            // Obtener información del tipo de check-in sin ejecutarlo
            $tipoCheckIn = match (get_class($checkIn)) {
                \App\Patterns\Behavioral\CheckInClienteVIP::class => 'VIP',
                \App\Patterns\Behavioral\CheckInGrupo::class => 'Grupo',
                \App\Patterns\Behavioral\CheckInExpress::class => 'Express',
                default => 'Estándar',
            };

            return response()->json([
                'success' => true,
                'tipo_checkin' => $tipoCheckIn,
                'reserva_id' => $reserva->id,
                'cliente' => $reserva->cliente->nombre.' '.$reserva->cliente->apellido,
                'reservas_anteriores' => $reserva->cliente->reservas()->count(),
                'numero_huespedes' => $reserva->numero_huespedes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener información sobre los tipos de check-in disponibles
     */
    public function tipos(Request $request)
    {
        return response()->json([
            'success' => true,
            'tipos' => [
                [
                    'tipo' => 'Estándar',
                    'descripcion' => 'Check-in estándar para clientes regulares',
                    'condiciones' => 'Clientes con menos de 10 reservas',
                    'caracteristicas' => [
                        'Validación de documentación completa',
                        'Verificación de pago obligatoria',
                        'Entrega de llaves físicas',
                        'Servicios básicos ofrecidos',
                    ],
                ],
                [
                    'tipo' => 'VIP',
                    'descripcion' => 'Check-in premium para clientes frecuentes',
                    'condiciones' => 'Clientes con 10 o más reservas',
                    'caracteristicas' => [
                        'Documentación pre-validada',
                        'Pago post check-out disponible',
                        'Tarjeta de acceso digital premium',
                        'Servicios VIP: Spa, Champagne, Butler 24/7',
                        'Bienvenida personalizada',
                    ],
                ],
                [
                    'tipo' => 'Grupo',
                    'descripcion' => 'Check-in para grupos y familias',
                    'condiciones' => 'Reservas con más de 3 huéspedes',
                    'caracteristicas' => [
                        'Validación de documentación de todos los miembros',
                        'Verificación de pago completo',
                        'Múltiples tarjetas de acceso',
                        'Servicios familiares: Actividades, Tours grupales',
                    ],
                ],
                [
                    'tipo' => 'Express',
                    'descripcion' => 'Check-in sin contacto',
                    'condiciones' => 'Reservas marcadas como "express"',
                    'caracteristicas' => [
                        'Documentación verificada online',
                        'Pago procesado previamente',
                        'Código de acceso digital enviado al móvil',
                        'Proceso completado en 2 minutos',
                    ],
                ],
            ],
        ]);
    }
}
