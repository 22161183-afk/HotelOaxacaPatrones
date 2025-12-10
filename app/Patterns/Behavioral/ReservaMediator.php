<?php

namespace App\Patterns\Behavioral;

use App\Events\ReservaCancelada;
use App\Events\ReservaConfirmada;
use App\Events\ReservaCreada;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Reserva;
use App\Notifications\ReservaCanceladaNotification;
use App\Notifications\ReservaConfirmadaNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Patrón Mediator para centralizar comunicación entre módulos
 *
 * Viñeta 18: La comunicación entre módulos (UI, motor, notificaciones)
 * deberá estar centralizada para evitar dependencias directas
 */

/**
 * Componente abstracto
 */
abstract class ReservaComponent
{
    protected ?ReservaMediator $mediator = null;

    public function setMediator(ReservaMediator $mediator): void
    {
        $this->mediator = $mediator;
    }
}

/**
 * Componente: Motor de Reservas
 */
class ReservaEngine extends ReservaComponent
{
    /**
     * Crear reserva
     */
    public function crearReserva(array $datos): array
    {
        try {
            // Usar ReservaBuilder para crear la reserva
            $builder = new \App\Patterns\Creational\ReservaBuilder;
            $builder->setCliente($datos['cliente'])
                ->setHabitacion($datos['habitacion'])
                ->setFechas($datos['fecha_inicio'], $datos['fecha_fin'])
                ->setNumeroHuespedes($datos['numero_huespedes'] ?? 1);

            if (isset($datos['servicios'])) {
                foreach ($datos['servicios'] as $servicio) {
                    $builder->agregarServicio($servicio);
                }
            }

            $reserva = $builder->build();

            // Notificar al mediador
            if ($this->mediator) {
                $this->mediator->notify($this, 'reserva_creada', $reserva);
            }

            return ['success' => true, 'reserva' => $reserva];

        } catch (\Exception $e) {
            if ($this->mediator) {
                $this->mediator->notify($this, 'error', ['mensaje' => $e->getMessage()]);
            }

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Confirmar reserva
     */
    public function confirmarReserva(Reserva $reserva): bool
    {
        $reserva->update([
            'estado' => 'confirmada',
            'fecha_confirmacion' => now(),
        ]);

        if ($this->mediator) {
            $this->mediator->notify($this, 'reserva_confirmada', $reserva);
        }

        return true;
    }

    /**
     * Cancelar reserva
     */
    public function cancelarReserva(Reserva $reserva, ?string $motivo = null): bool
    {
        $reserva->update([
            'estado' => 'cancelada',
            'fecha_cancelacion' => now(),
            'observaciones' => $reserva->observaciones."\nMotivo: ".($motivo ?? 'No especificado'),
        ]);

        if ($this->mediator) {
            $this->mediator->notify($this, 'reserva_cancelada', ['reserva' => $reserva, 'motivo' => $motivo]);
        }

        return true;
    }
}

/**
 * Componente: Gestor de Habitaciones
 */
class HabitacionManager extends ReservaComponent
{
    /**
     * Actualizar estado de habitación
     */
    public function actualizarEstado(Habitacion $habitacion, string $nuevoEstado): bool
    {
        $estadoAnterior = $habitacion->estado;
        $habitacion->update(['estado' => $nuevoEstado]);

        if ($this->mediator) {
            $this->mediator->notify($this, 'habitacion_actualizada', [
                'habitacion' => $habitacion,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
            ]);
        }

        return true;
    }

    /**
     * Liberar habitación
     */
    public function liberarHabitacion(Habitacion $habitacion): bool
    {
        return $this->actualizarEstado($habitacion, 'disponible');
    }

    /**
     * Reservar habitación
     */
    public function reservarHabitacion(Habitacion $habitacion): bool
    {
        return $this->actualizarEstado($habitacion, 'reservada');
    }

    /**
     * Ocupar habitación
     */
    public function ocuparHabitacion(Habitacion $habitacion): bool
    {
        return $this->actualizarEstado($habitacion, 'ocupada');
    }
}

/**
 * Componente: Sistema de Notificaciones
 */
class NotificationSystem extends ReservaComponent
{
    /**
     * Enviar notificación de reserva creada
     */
    public function notificarReservaCreada(Reserva $reserva): void
    {
        Log::info('📧 Enviando notificación de reserva creada', ['reserva_id' => $reserva->id]);

        // Aquí iría la lógica real de envío de emails/SMS
        // Notification::send($reserva->cliente, new ReservaCreada Notification($reserva));
    }

    /**
     * Enviar notificación de confirmación
     */
    public function notificarReservaConfirmada(Reserva $reserva): void
    {
        Log::info('📧 Enviando notificación de confirmación', ['reserva_id' => $reserva->id]);

        try {
            $reserva->cliente->notify(new ReservaConfirmadaNotification($reserva));
        } catch (\Exception $e) {
            Log::error("Error enviando notificación: {$e->getMessage()}");
        }
    }

    /**
     * Enviar notificación de cancelación
     */
    public function notificarReservaCancelada(Reserva $reserva, ?string $motivo = null): void
    {
        Log::info('📧 Enviando notificación de cancelación', [
            'reserva_id' => $reserva->id,
            'motivo' => $motivo,
        ]);

        try {
            $reserva->cliente->notify(new ReservaCanceladaNotification($reserva, $motivo));
        } catch (\Exception $e) {
            Log::error("Error enviando notificación: {$e->getMessage()}");
        }
    }
}

/**
 * Componente: Sistema de Auditoría
 */
class AuditSystem extends ReservaComponent
{
    /**
     * Registrar evento
     */
    public function registrarEvento(string $tipo, array $datos): void
    {
        Log::channel('audit')->info("AUDIT: {$tipo}", $datos);

        // Aquí podría guardarse en una tabla de auditoría
        // AuditLog::create(['tipo' => $tipo, 'datos' => $datos]);
    }
}

/**
 * Componente: Gestor de Pagos
 */
class PaymentManager extends ReservaComponent
{
    /**
     * Registrar pago
     */
    public function registrarPago(Reserva $reserva, array $datosPago): ?Pago
    {
        try {
            $pago = Pago::create([
                'reserva_id' => $reserva->id,
                'metodo_pago_id' => $datosPago['metodo_pago_id'],
                'monto' => $datosPago['monto'],
                'estado' => 'completado',
                'referencia' => $datosPago['referencia'] ?? uniqid('PAY-'),
                'fecha_pago' => now(),
            ]);

            if ($this->mediator) {
                $this->mediator->notify($this, 'pago_registrado', $pago);
            }

            return $pago;

        } catch (\Exception $e) {
            if ($this->mediator) {
                $this->mediator->notify($this, 'error_pago', ['mensaje' => $e->getMessage()]);
            }

            return null;
        }
    }
}

/**
 * Mediator - Coordina la comunicación entre componentes
 */
class ReservaMediator
{
    private ReservaEngine $engine;

    private HabitacionManager $habitacionManager;

    private NotificationSystem $notificationSystem;

    private AuditSystem $auditSystem;

    private PaymentManager $paymentManager;

    public function __construct()
    {
        $this->engine = new ReservaEngine;
        $this->habitacionManager = new HabitacionManager;
        $this->notificationSystem = new NotificationSystem;
        $this->auditSystem = new AuditSystem;
        $this->paymentManager = new PaymentManager;

        // Establecer mediador en cada componente
        $this->engine->setMediator($this);
        $this->habitacionManager->setMediator($this);
        $this->notificationSystem->setMediator($this);
        $this->auditSystem->setMediator($this);
        $this->paymentManager->setMediator($this);
    }

    /**
     * Manejar notificaciones de componentes
     */
    public function notify(object $sender, string $evento, $datos = null): void
    {
        // Auditar todos los eventos
        $this->auditSystem->registrarEvento($evento, [
            'sender' => get_class($sender),
            'datos' => is_object($datos) && method_exists($datos, 'toArray') ? $datos->toArray() : $datos,
        ]);

        // Manejar eventos específicos
        match ($evento) {
            'reserva_creada' => $this->manejarReservaCreada($datos),
            'reserva_confirmada' => $this->manejarReservaConfirmada($datos),
            'reserva_cancelada' => $this->manejarReservaCancelada($datos),
            'habitacion_actualizada' => $this->manejarHabitacionActualizada($datos),
            'pago_registrado' => $this->manejarPagoRegistrado($datos),
            'error' => $this->manejarError($datos),
            default => Log::info("Evento no manejado: {$evento}"),
        };
    }

    /**
     * Manejar creación de reserva
     */
    private function manejarReservaCreada(Reserva $reserva): void
    {
        // 1. Actualizar estado de habitación
        $this->habitacionManager->reservarHabitacion($reserva->habitacion);

        // 2. Enviar notificación
        $this->notificationSystem->notificarReservaCreada($reserva);

        // 3. Disparar evento Laravel
        event(new ReservaCreada($reserva));

        Log::info('✅ Reserva creada y procesada', ['reserva_id' => $reserva->id]);
    }

    /**
     * Manejar confirmación de reserva
     */
    private function manejarReservaConfirmada(Reserva $reserva): void
    {
        // 1. Enviar notificación
        $this->notificationSystem->notificarReservaConfirmada($reserva);

        // 2. Disparar evento Laravel
        event(new ReservaConfirmada($reserva));

        Log::info('✅ Reserva confirmada', ['reserva_id' => $reserva->id]);
    }

    /**
     * Manejar cancelación de reserva
     */
    private function manejarReservaCancelada(array $datos): void
    {
        $reserva = $datos['reserva'];
        $motivo = $datos['motivo'] ?? null;

        // 1. Liberar habitación
        $this->habitacionManager->liberarHabitacion($reserva->habitacion);

        // 2. Enviar notificación
        $this->notificationSystem->notificarReservaCancelada($reserva, $motivo);

        // 3. Disparar evento Laravel
        event(new ReservaCancelada($reserva));

        Log::info('✅ Reserva cancelada', ['reserva_id' => $reserva->id, 'motivo' => $motivo]);
    }

    /**
     * Manejar actualización de habitación
     */
    private function manejarHabitacionActualizada(array $datos): void
    {
        Log::info('🏠 Habitación actualizada', [
            'habitacion_id' => $datos['habitacion']->id,
            'estado_anterior' => $datos['estado_anterior'],
            'estado_nuevo' => $datos['estado_nuevo'],
        ]);
    }

    /**
     * Manejar registro de pago
     */
    private function manejarPagoRegistrado(Pago $pago): void
    {
        Log::info('💰 Pago registrado', ['pago_id' => $pago->id, 'monto' => $pago->monto]);
    }

    /**
     * Manejar errores
     */
    private function manejarError(array $datos): void
    {
        Log::error('❌ Error en el sistema', $datos);
    }

    // Getters para acceder a componentes
    public function getEngine(): ReservaEngine
    {
        return $this->engine;
    }

    public function getHabitacionManager(): HabitacionManager
    {
        return $this->habitacionManager;
    }

    public function getNotificationSystem(): NotificationSystem
    {
        return $this->notificationSystem;
    }

    public function getPaymentManager(): PaymentManager
    {
        return $this->paymentManager;
    }
}
