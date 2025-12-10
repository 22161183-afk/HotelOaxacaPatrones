<?php

namespace App\Patterns\Behavioral;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;

/**
 * Patrón Observer para notificaciones automáticas
 *
 * Viñeta 20: Los clientes y empleados deberán ser notificados
 * automáticamente ante cambios importantes
 */

/**
 * Subject interface - Observable
 */
interface ReservaSubject
{
    public function agregarObservador(ReservaObservador $observador): void;

    public function quitarObservador(ReservaObservador $observador): void;

    public function notificarObservadores(string $evento, array $datos): void;
}

/**
 * Observer interface
 */
interface ReservaObservador
{
    public function actualizar(string $evento, array $datos): void;

    public function getNombre(): string;
}

/**
 * Concrete Subject - Gestor de Reservas Observable
 */
class ReservaObservable implements ReservaSubject
{
    private array $observadores = [];

    private Reserva $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function agregarObservador(ReservaObservador $observador): void
    {
        $nombre = $observador->getNombre();
        if (! isset($this->observadores[$nombre])) {
            $this->observadores[$nombre] = $observador;
            Log::info("✅ Observador agregado: {$nombre}");
        }
    }

    public function quitarObservador(ReservaObservador $observador): void
    {
        $nombre = $observador->getNombre();
        if (isset($this->observadores[$nombre])) {
            unset($this->observadores[$nombre]);
            Log::info("❌ Observador removido: {$nombre}");
        }
    }

    public function notificarObservadores(string $evento, array $datos): void
    {
        Log::info("📢 Notificando evento: {$evento} a ".count($this->observadores).' observadores');

        $datos['reserva'] = $this->reserva;

        foreach ($this->observadores as $observador) {
            try {
                $observador->actualizar($evento, $datos);
            } catch (\Exception $e) {
                Log::error("Error en observador {$observador->getNombre()}: {$e->getMessage()}");
            }
        }
    }

    public function getReserva(): Reserva
    {
        return $this->reserva;
    }

    /**
     * Métodos del negocio que disparan notificaciones
     */
    public function crearReserva(): void
    {
        $this->notificarObservadores('reserva_creada', [
            'mensaje' => 'Nueva reserva creada',
            'fecha_creacion' => now(),
        ]);
    }

    public function confirmarReserva(): void
    {
        $this->reserva->update([
            'estado' => 'confirmada',
            'fecha_confirmacion' => now(),
        ]);

        $this->notificarObservadores('reserva_confirmada', [
            'mensaje' => 'Reserva confirmada',
            'fecha_confirmacion' => now(),
        ]);
    }

    public function cancelarReserva(string $motivo): void
    {
        $this->reserva->update([
            'estado' => 'cancelada',
            'fecha_cancelacion' => now(),
        ]);

        $this->notificarObservadores('reserva_cancelada', [
            'mensaje' => 'Reserva cancelada',
            'motivo' => $motivo,
            'fecha_cancelacion' => now(),
        ]);
    }

    public function modificarReserva(array $cambios): void
    {
        $this->reserva->update($cambios);

        $this->notificarObservadores('reserva_modificada', [
            'mensaje' => 'Reserva modificada',
            'cambios' => $cambios,
            'fecha_modificacion' => now(),
        ]);
    }

    public function procesarPago(Pago $pago): void
    {
        $this->notificarObservadores('pago_procesado', [
            'mensaje' => 'Pago procesado',
            'pago' => $pago,
            'fecha_pago' => now(),
        ]);
    }

    public function proximaLlegada(): void
    {
        $this->notificarObservadores('proxima_llegada', [
            'mensaje' => 'Recordatorio: Su llegada es mañana',
            'fecha_inicio' => $this->reserva->fecha_inicio,
        ]);
    }
}

/**
 * Concrete Observer: Notificador de Clientes por Email
 */
class EmailClienteObserver implements ReservaObservador
{
    public function actualizar(string $evento, array $datos): void
    {
        $reserva = $datos['reserva'];
        $cliente = $reserva->cliente;

        $asunto = match ($evento) {
            'reserva_creada' => 'Confirmación de Reserva',
            'reserva_confirmada' => 'Reserva Confirmada - Pago Recibido',
            'reserva_cancelada' => 'Reserva Cancelada',
            'reserva_modificada' => 'Reserva Modificada',
            'pago_procesado' => 'Pago Procesado',
            'proxima_llegada' => 'Recordatorio: Su llegada es mañana',
            default => 'Notificación de Reserva',
        };

        Log::info("📧 EMAIL enviado a {$cliente->email}: {$asunto}");

        // Envío real de email
        try {
            \Mail::to($cliente->email)->send(new \App\Mail\ReservaNotificationMail($reserva, $evento, $datos));
        } catch (\Exception $e) {
            Log::error("Error al enviar email: {$e->getMessage()}");
        }
    }

    public function getNombre(): string
    {
        return 'EmailClienteObserver';
    }
}

/**
 * Concrete Observer: Notificador de Clientes por SMS
 */
class SMSClienteObserver implements ReservaObservador
{
    public function actualizar(string $evento, array $datos): void
    {
        $reserva = $datos['reserva'];
        $cliente = $reserva->cliente;

        if (empty($cliente->telefono)) {
            return;
        }

        $mensaje = match ($evento) {
            'reserva_creada' => "Reserva #{$reserva->id} creada. Check-in: {$reserva->fecha_inicio->format('d/m/Y')}",
            'reserva_confirmada' => "Reserva #{$reserva->id} confirmada. ¡Nos vemos pronto!",
            'reserva_cancelada' => "Reserva #{$reserva->id} cancelada. Motivo: {$datos['motivo']}",
            'pago_procesado' => "Pago recibido por \${$datos['pago']->monto}. Gracias!",
            'proxima_llegada' => "Recordatorio: Su llegada es mañana a las 3PM. Habitación: {$reserva->habitacion->numero}",
            default => "Actualización de reserva #{$reserva->id}",
        };

        Log::info("📱 SMS enviado a {$cliente->telefono}: {$mensaje}");

        // Envío real de SMS
        \App\Services\Notifications\SMSService::enviar($cliente->telefono, $mensaje);
    }

    public function getNombre(): string
    {
        return 'SMSClienteObserver';
    }
}

/**
 * Concrete Observer: Notificador de Clientes por WhatsApp
 */
class WhatsAppClienteObserver implements ReservaObservador
{
    public function actualizar(string $evento, array $datos): void
    {
        $reserva = $datos['reserva'];
        $cliente = $reserva->cliente;

        if (empty($cliente->telefono)) {
            return;
        }

        $mensaje = match ($evento) {
            'reserva_creada' => "¡Hola {$cliente->nombre}! 🎉\n\nTu reserva #{$reserva->id} ha sido creada.\n\nDetalles:\n- Habitación: {$reserva->habitacion->numero}\n- Check-in: {$reserva->fecha_inicio->format('d/m/Y')}\n- Check-out: {$reserva->fecha_fin->format('d/m/Y')}\n- Total: \${$reserva->precio_total}",
            'reserva_confirmada' => "¡Excelente {$cliente->nombre}! ✅\n\nTu reserva #{$reserva->id} ha sido confirmada.\n\n¡Nos vemos pronto!",
            'reserva_cancelada' => "Hola {$cliente->nombre},\n\nTu reserva #{$reserva->id} ha sido cancelada.\n\nMotivo: {$datos['motivo']}\n\nEsperamos verte pronto.",
            'pago_procesado' => "¡Pago recibido! 💰\n\nMonto: \${$datos['pago']->monto}\nReserva: #{$reserva->id}\n\nGracias por tu pago.",
            'proxima_llegada' => "¡Hola {$cliente->nombre}! 👋\n\nTe recordamos que tu llegada es mañana:\n\n- Check-in: 3:00 PM\n- Habitación: {$reserva->habitacion->numero}\n- Reserva: #{$reserva->id}\n\n¡Te esperamos!",
            default => "Actualización de tu reserva #{$reserva->id}",
        };

        Log::info("💬 WhatsApp enviado a {$cliente->telefono}: {$mensaje}");

        // Envío real de WhatsApp
        \App\Services\Notifications\WhatsAppService::enviar($cliente->telefono, $mensaje);
    }

    public function getNombre(): string
    {
        return 'WhatsAppClienteObserver';
    }
}

/**
 * Concrete Observer: Notificador de Empleados
 */
class EmpleadoObserver implements ReservaObservador
{
    public function actualizar(string $evento, array $datos): void
    {
        $reserva = $datos['reserva'];

        // Notificar a recepcionistas y gerentes
        $empleados = Usuario::whereIn('rol', ['admin', 'recepcionista'])->get();

        foreach ($empleados as $empleado) {
            $mensaje = match ($evento) {
                'reserva_creada' => "Nueva reserva #{$reserva->id} - Cliente: {$reserva->cliente->nombre}",
                'reserva_confirmada' => "Reserva #{$reserva->id} confirmada - Pago recibido",
                'reserva_cancelada' => "Reserva #{$reserva->id} cancelada - Liberar habitación {$reserva->habitacion->numero}",
                'pago_procesado' => "Pago de \${$datos['pago']->monto} procesado para reserva #{$reserva->id}",
                'proxima_llegada' => "Llegada mañana: Cliente {$reserva->cliente->nombre} - Habitación {$reserva->habitacion->numero}",
                default => "Actualización de reserva #{$reserva->id}",
            };

            Log::info("👔 Notificación a empleado {$empleado->email}: {$mensaje}");

            // Aquí iría el código real de notificación interna
            // Notification::send($empleado, new ReservaUpdateNotification($reserva, $evento, $mensaje));
        }
    }

    public function getNombre(): string
    {
        return 'EmpleadoObserver';
    }
}

/**
 * Concrete Observer: Sistema de Auditoría
 */
class AuditoriaObserver implements ReservaObservador
{
    public function actualizar(string $evento, array $datos): void
    {
        $reserva = $datos['reserva'];

        $registro = [
            'evento' => $evento,
            'reserva_id' => $reserva->id,
            'cliente_id' => $reserva->cliente_id,
            'habitacion_id' => $reserva->habitacion_id,
            'estado_anterior' => $reserva->getOriginal('estado'),
            'estado_nuevo' => $reserva->estado,
            'datos' => $datos,
            'timestamp' => now(),
            'usuario_id' => auth()->id(),
        ];

        Log::channel('audit')->info('RESERVA_EVENTO', $registro);

        // Aquí iría el código para guardar en tabla de auditoría
        // Auditoria::create($registro);
    }

    public function getNombre(): string
    {
        return 'AuditoriaObserver';
    }
}

/**
 * Concrete Observer: Actualizador de Estadísticas
 */
class EstadisticasObserver implements ReservaObservador
{
    public function actualizar(string $evento, array $datos): void
    {
        $reserva = $datos['reserva'];

        match ($evento) {
            'reserva_creada' => $this->incrementarReservasCreadas(),
            'reserva_confirmada' => $this->incrementarReservasConfirmadas(),
            'reserva_cancelada' => $this->incrementarReservasCanceladas(),
            'pago_procesado' => $this->actualizarIngresos($datos['pago']->monto),
            default => null,
        };

        Log::info("📊 Estadísticas actualizadas por evento: {$evento}");
    }

    private function incrementarReservasCreadas(): void
    {
        // Cache::increment('estadisticas.reservas.creadas');
    }

    private function incrementarReservasConfirmadas(): void
    {
        // Cache::increment('estadisticas.reservas.confirmadas');
    }

    private function incrementarReservasCanceladas(): void
    {
        // Cache::increment('estadisticas.reservas.canceladas');
    }

    private function actualizarIngresos(float $monto): void
    {
        // Cache::increment('estadisticas.ingresos.total', $monto);
    }

    public function getNombre(): string
    {
        return 'EstadisticasObserver';
    }
}

/**
 * Factory para crear observables con todos los observadores necesarios
 */
class ReservaObservableFactory
{
    /**
     * Crear observable con observadores estándar
     */
    public static function crearConObservadoresEstandar(Reserva $reserva): ReservaObservable
    {
        $observable = new ReservaObservable($reserva);

        // Agregar observadores de notificación al cliente
        $observable->agregarObservador(new EmailClienteObserver);
        $observable->agregarObservador(new SMSClienteObserver);
        $observable->agregarObservador(new WhatsAppClienteObserver);

        // Agregar observador de empleados
        $observable->agregarObservador(new EmpleadoObserver);

        // Agregar observadores del sistema
        $observable->agregarObservador(new AuditoriaObserver);
        $observable->agregarObservador(new EstadisticasObserver);

        return $observable;
    }

    /**
     * Crear observable solo con email
     */
    public static function crearSoloEmail(Reserva $reserva): ReservaObservable
    {
        $observable = new ReservaObservable($reserva);
        $observable->agregarObservador(new EmailClienteObserver);
        $observable->agregarObservador(new AuditoriaObserver);

        return $observable;
    }

    /**
     * Crear observable con canales personalizados
     */
    public static function crearConCanales(Reserva $reserva, array $canales): ReservaObservable
    {
        $observable = new ReservaObservable($reserva);

        $observadores = [
            'email' => new EmailClienteObserver,
            'sms' => new SMSClienteObserver,
            'whatsapp' => new WhatsAppClienteObserver,
            'empleados' => new EmpleadoObserver,
            'auditoria' => new AuditoriaObserver,
            'estadisticas' => new EstadisticasObserver,
        ];

        foreach ($canales as $canal) {
            if (isset($observadores[$canal])) {
                $observable->agregarObservador($observadores[$canal]);
            }
        }

        return $observable;
    }
}

/**
 * Manager de alto nivel para gestionar observables
 */
class ReservaObserverManager
{
    /**
     * Procesar reserva con notificaciones automáticas
     */
    public static function crearReservaConNotificaciones(Reserva $reserva, array $canales = ['email', 'sms', 'whatsapp', 'empleados', 'auditoria']): void
    {
        $observable = ReservaObservableFactory::crearConCanales($reserva, $canales);
        $observable->crearReserva();
    }

    /**
     * Confirmar reserva con notificaciones
     */
    public static function confirmarReservaConNotificaciones(Reserva $reserva, array $canales = ['email', 'sms', 'whatsapp', 'empleados', 'auditoria']): void
    {
        $observable = ReservaObservableFactory::crearConCanales($reserva, $canales);
        $observable->confirmarReserva();
    }

    /**
     * Cancelar reserva con notificaciones
     */
    public static function cancelarReservaConNotificaciones(Reserva $reserva, string $motivo, array $canales = ['email', 'sms', 'whatsapp', 'empleados', 'auditoria']): void
    {
        $observable = ReservaObservableFactory::crearConCanales($reserva, $canales);
        $observable->cancelarReserva($motivo);
    }

    /**
     * Procesar pago con notificaciones
     */
    public static function procesarPagoConNotificaciones(Reserva $reserva, Pago $pago, array $canales = ['email', 'sms', 'empleados']): void
    {
        $observable = ReservaObservableFactory::crearConCanales($reserva, $canales);
        $observable->procesarPago($pago);
    }

    /**
     * Enviar recordatorio de llegada
     */
    public static function enviarRecordatorioLlegada(Reserva $reserva, array $canales = ['email', 'sms', 'whatsapp']): void
    {
        $observable = ReservaObservableFactory::crearConCanales($reserva, $canales);
        $observable->proximaLlegada();
    }
}
