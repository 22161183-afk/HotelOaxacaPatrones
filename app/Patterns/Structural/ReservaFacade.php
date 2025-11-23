<?php

namespace App\Patterns\Structural;

use App\Events\ReservaCancelada;
use App\Events\ReservaConfirmada;
use App\Events\ReservaCreada;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\MetodoPago;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Patterns\Behavioral\PagoContext;
use App\Patterns\Creational\ReservaBuilder;

/**
 * Patrón Facade para el sistema de reservas
 *
 * Proporciona una interfaz simplificada para las operaciones complejas
 * del sistema de reservas, ocultando la complejidad de subsistemas.
 */
class ReservaFacade
{
    /**
     * Crear una reserva completa con todos los pasos necesarios
     */
    public function crearReservaCompleta(array $datos): array
    {
        try {
            // 1. Buscar o crear cliente
            $cliente = $this->obtenerOCrearCliente($datos['cliente']);

            // 2. Verificar disponibilidad de habitación
            $habitacion = $this->verificarDisponibilidad(
                $datos['habitacion_id'],
                $datos['fecha_inicio'],
                $datos['fecha_fin']
            );

            // 3. Construir reserva usando Builder
            $builder = new ReservaBuilder;
            $builder->setCliente($cliente)
                ->setHabitacion($habitacion)
                ->setFechas($datos['fecha_inicio'], $datos['fecha_fin'])
                ->setNumeroHuespedes($datos['numero_huespedes'] ?? 1);

            // 4. Agregar servicios si se especifican
            if (isset($datos['servicios'])) {
                foreach ($datos['servicios'] as $servicioId) {
                    $servicio = Servicio::findOrFail($servicioId);
                    $builder->agregarServicio($servicio);
                }
            }

            // 5. Agregar observaciones
            if (isset($datos['observaciones'])) {
                $builder->setObservaciones($datos['observaciones']);
            }

            // 6. Crear la reserva
            $reserva = $builder->build();

            // 7. Actualizar estado de habitación
            $habitacion->update(['estado' => 'ocupada']);

            // 8. Disparar evento
            event(new ReservaCreada($reserva));

            return [
                'exito' => true,
                'reserva' => $reserva,
                'mensaje' => 'Reserva creada exitosamente',
            ];

        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirmar reserva y procesar pago
     */
    public function confirmarReservaConPago(
        Reserva $reserva,
        int $metodoPagoId,
        array $datosPago = []
    ): array {
        try {
            // 1. Obtener método de pago
            $metodoPago = MetodoPago::findOrFail($metodoPagoId);

            // 2. Procesar pago usando Strategy
            $pagoContext = new PagoContext($metodoPago);
            $pago = $pagoContext->procesarPago(
                $reserva,
                $reserva->precio_total,
                $datosPago
            );

            // 3. Actualizar estado de reserva
            if ($pago->estado === 'completado') {
                $reserva->update([
                    'estado' => 'confirmada',
                    'fecha_confirmacion' => now(),
                ]);

                // 4. Disparar evento
                event(new ReservaConfirmada($reserva));

                return [
                    'exito' => true,
                    'reserva' => $reserva,
                    'pago' => $pago,
                    'mensaje' => 'Reserva confirmada y pago procesado',
                ];
            }

            return [
                'exito' => false,
                'pago' => $pago,
                'mensaje' => 'Pago pendiente de confirmación',
            ];

        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancelar reserva
     */
    public function cancelarReserva(Reserva $reserva, ?string $motivo = null): array
    {
        try {
            // 1. Verificar si la cancelación es permitida
            $config = \App\Patterns\Creational\ConfiguracionSingleton::getInstance();
            $diasCancelacion = $config->getDiasCancelacion();

            $diasHastaReserva = now()->diffInDays($reserva->fecha_inicio);

            if ($diasHastaReserva < $diasCancelacion) {
                return [
                    'exito' => false,
                    'error' => "La cancelación debe hacerse con al menos {$diasCancelacion} días de anticipación",
                ];
            }

            // 2. Actualizar estado de reserva
            $reserva->update([
                'estado' => 'cancelada',
                'fecha_cancelacion' => now(),
                'observaciones' => $reserva->observaciones."\nMotivo cancelación: ".($motivo ?? 'No especificado'),
            ]);

            // 3. Liberar habitación
            $reserva->habitacion->update(['estado' => 'disponible']);

            // 4. Procesar reembolso si aplica
            $pagos = $reserva->pagos()->where('estado', 'completado')->get();
            foreach ($pagos as $pago) {
                $pago->update([
                    'estado' => 'reembolsado',
                    'observaciones' => $pago->observaciones.' - Reembolso por cancelación',
                ]);
            }

            // 5. Disparar evento
            event(new ReservaCancelada($reserva));

            return [
                'exito' => true,
                'reserva' => $reserva,
                'mensaje' => 'Reserva cancelada exitosamente',
            ];

        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Buscar habitaciones disponibles
     */
    public function buscarHabitacionesDisponibles(
        string $fechaInicio,
        string $fechaFin,
        int $capacidad = 1
    ): array {
        $habitaciones = Habitacion::where('estado', 'disponible')
            ->where('capacidad', '>=', $capacidad)
            ->whereDoesntHave('reservas', function ($query) use ($fechaInicio, $fechaFin) {
                $query->where(function ($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                        ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                        ->orWhere(function ($q2) use ($fechaInicio, $fechaFin) {
                            $q2->where('fecha_inicio', '<=', $fechaInicio)
                                ->where('fecha_fin', '>=', $fechaFin);
                        });
                })->whereIn('estado', ['pendiente', 'confirmada']);
            })
            ->with('tipoHabitacion')
            ->get();

        return [
            'habitaciones' => $habitaciones,
            'total' => $habitaciones->count(),
        ];
    }

    /**
     * Obtener o crear cliente
     */
    private function obtenerOCrearCliente(array $datosCliente): Cliente
    {
        $cliente = Cliente::where('email', $datosCliente['email'])->first();

        if (! $cliente) {
            $cliente = Cliente::create($datosCliente);
        }

        return $cliente;
    }

    /**
     * Verificar disponibilidad de habitación
     */
    private function verificarDisponibilidad(
        int $habitacionId,
        string $fechaInicio,
        string $fechaFin
    ): Habitacion {
        $habitacion = Habitacion::findOrFail($habitacionId);

        // Verificar reservas existentes
        $reservasConflicto = $habitacion->reservas()
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                    ->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                        $q->where('fecha_inicio', '<=', $fechaInicio)
                            ->where('fecha_fin', '>=', $fechaFin);
                    });
            })
            ->exists();

        if ($reservasConflicto) {
            throw new \Exception('La habitación no está disponible para las fechas seleccionadas');
        }

        return $habitacion;
    }

    /**
     * Obtener resumen de reserva
     */
    public function obtenerResumenReserva(Reserva $reserva): array
    {
        return [
            'reserva_id' => $reserva->id,
            'cliente' => $reserva->cliente->nombre.' '.$reserva->cliente->apellido,
            'habitacion' => $reserva->habitacion->numero,
            'tipo_habitacion' => $reserva->habitacion->tipoHabitacion->nombre,
            'fecha_inicio' => $reserva->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $reserva->fecha_fin->format('Y-m-d'),
            'noches' => $reserva->fecha_inicio->diffInDays($reserva->fecha_fin),
            'numero_huespedes' => $reserva->numero_huespedes,
            'servicios' => $reserva->servicios->map(fn ($s) => $s->nombre),
            'precio_total' => $reserva->precio_total,
            'estado' => $reserva->estado,
            'pagos' => $reserva->pagos,
        ];
    }
}
