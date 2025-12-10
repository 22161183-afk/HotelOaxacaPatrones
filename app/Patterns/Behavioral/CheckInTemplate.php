<?php

namespace App\Patterns\Behavioral;

use App\Models\Cliente;
use App\Models\Reserva;

/**
 * Patrón Template Method para proceso de check-in
 *
 * Viñeta 23: Los procesos comunes (como check-in) deberán tener pasos fijos,
 * pero personalizables por tipo de cliente
 */

/**
 * Resultado del check-in
 */
class CheckInResult
{
    private bool $exitoso;

    private array $pasos = [];

    private array $errores = [];

    private array $info = [];

    public function __construct(bool $exitoso = true)
    {
        $this->exitoso = $exitoso;
    }

    public function agregarPaso(string $paso, bool $completado = true): void
    {
        $this->pasos[] = ['nombre' => $paso, 'completado' => $completado];
        if (! $completado) {
            $this->exitoso = false;
        }
    }

    public function agregarError(string $error): void
    {
        $this->errores[] = $error;
        $this->exitoso = false;
    }

    public function agregarInfo(string $info): void
    {
        $this->info[] = $info;
    }

    public function esExitoso(): bool
    {
        return $this->exitoso;
    }

    public function getResumen(): array
    {
        return [
            'exitoso' => $this->exitoso,
            'pasos_completados' => array_filter($this->pasos, fn ($p) => $p['completado']),
            'pasos_fallidos' => array_filter($this->pasos, fn ($p) => ! $p['completado']),
            'total_pasos' => count($this->pasos),
            'errores' => $this->errores,
            'info' => $this->info,
        ];
    }
}

/**
 * Template Method - Define el esqueleto del algoritmo de check-in
 */
abstract class CheckInTemplate
{
    protected Reserva $reserva;

    protected Cliente $cliente;

    protected CheckInResult $result;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
        $this->cliente = $reserva->cliente;
        $this->result = new CheckInResult;
    }

    /**
     * Template Method - Define la estructura del proceso
     * (PASOS FIJOS que no pueden ser modificados)
     */
    final public function ejecutarCheckIn(): CheckInResult
    {
        // Paso 1: Verificar reserva
        $this->verificarReserva();
        $this->result->agregarPaso('Verificación de reserva', true);

        // Paso 2: Validar documentación (puede ser personalizado)
        if ($this->validarDocumentacion()) {
            $this->result->agregarPaso('Validación de documentación', true);
        } else {
            $this->result->agregarPaso('Validación de documentación', false);

            return $this->result;
        }

        // Paso 3: Verificar pago (puede ser personalizado)
        if ($this->verificarPago()) {
            $this->result->agregarPaso('Verificación de pago', true);
        } else {
            $this->result->agregarPaso('Verificación de pago', false);

            return $this->result;
        }

        // Paso 4: Asignar habitación
        $this->asignarHabitacion();
        $this->result->agregarPaso('Asignación de habitación', true);

        // Paso 5: Entregar llaves (puede ser personalizado)
        $this->entregarLlaves();
        $this->result->agregarPaso('Entrega de llaves', true);

        // Paso 6: Servicios adicionales (HOOK - puede ser personalizado)
        $this->ofrecerServiciosAdicionales();

        // Paso 7: Bienvenida personalizada (HOOK - puede ser personalizado)
        $this->darBienvenida();

        // Paso 8: Finalizar check-in
        $this->finalizarCheckIn();
        $this->result->agregarPaso('Finalización de check-in', true);

        return $this->result;
    }

    /**
     * Paso obligatorio: Verificar reserva
     */
    protected function verificarReserva(): void
    {
        if ($this->reserva->estado !== 'confirmada') {
            $this->result->agregarError('La reserva no está confirmada');

            throw new \Exception('Reserva no confirmada');
        }

        if ($this->reserva->fecha_inicio->isAfter(now())) {
            $this->result->agregarInfo('Check-in anticipado');
        }
    }

    /**
     * Paso que puede ser personalizado: Validar documentación
     */
    abstract protected function validarDocumentacion(): bool;

    /**
     * Paso que puede ser personalizado: Verificar pago
     */
    abstract protected function verificarPago(): bool;

    /**
     * Paso obligatorio: Asignar habitación
     */
    protected function asignarHabitacion(): void
    {
        $this->reserva->habitacion->update(['estado' => 'ocupada']);
        $this->result->agregarInfo("Habitación {$this->reserva->habitacion->numero} asignada");
    }

    /**
     * Paso que puede ser personalizado: Entregar llaves
     */
    abstract protected function entregarLlaves(): void;

    /**
     * Hook opcional: Ofrecer servicios adicionales
     */
    protected function ofrecerServiciosAdicionales(): void
    {
        // Implementación por defecto (puede ser sobrescrita)
        $this->result->agregarInfo('Servicios adicionales ofrecidos');
    }

    /**
     * Hook opcional: Dar bienvenida
     */
    protected function darBienvenida(): void
    {
        // Implementación por defecto (puede ser sobrescrita)
        $this->result->agregarInfo("Bienvenido/a {$this->cliente->nombre}");
    }

    /**
     * Paso obligatorio: Finalizar check-in
     */
    protected function finalizarCheckIn(): void
    {
        $this->reserva->update([
            'estado' => 'activa',
            'fecha_checkin' => now(),
        ]);
        $this->result->agregarInfo('Check-in completado exitosamente');
    }
}

/**
 * Implementación concreta: Check-in para cliente estándar
 */
class CheckInClienteEstandar extends CheckInTemplate
{
    protected function validarDocumentacion(): bool
    {
        // Validación básica de documentación
        if (empty($this->cliente->documento_identidad)) {
            $this->result->agregarError('Falta documento de identidad');

            return false;
        }

        $this->result->agregarInfo('Documentación validada: DNI/Pasaporte');

        return true;
    }

    protected function verificarPago(): bool
    {
        // Verificar que el pago esté completo
        $pagoCompleto = $this->reserva->pagos()
            ->where('estado', 'completado')
            ->exists();

        if (! $pagoCompleto) {
            $this->result->agregarError('Falta completar el pago');

            return false;
        }

        $this->result->agregarInfo('Pago verificado');

        return true;
    }

    protected function entregarLlaves(): void
    {
        // Entrega de llaves físicas estándar
        $this->result->agregarInfo('Llaves físicas entregadas en recepción');
    }

    protected function ofrecerServiciosAdicionales(): void
    {
        // Ofrecer servicios básicos
        $this->result->agregarInfo('Servicios ofrecidos: Desayuno, WiFi');
    }
}

/**
 * Implementación concreta: Check-in para cliente VIP
 */
class CheckInClienteVIP extends CheckInTemplate
{
    protected function validarDocumentacion(): bool
    {
        // Clientes VIP ya tienen documentación pre-validada
        $this->result->agregarInfo('⭐ Documentación pre-validada (Cliente VIP)');

        return true;
    }

    protected function verificarPago(): bool
    {
        // VIPs pueden pagar después
        $this->result->agregarInfo('⭐ Pago post check-out disponible (Cliente VIP)');

        return true;
    }

    protected function entregarLlaves(): void
    {
        // Entrega de tarjeta digital y llaves premium
        $this->result->agregarInfo('⭐ Tarjeta de acceso digital premium entregada');
    }

    protected function ofrecerServiciosAdicionales(): void
    {
        // Servicios premium para VIP
        $this->result->agregarInfo('⭐ Servicios VIP ofrecidos: Spa, Champagne, Butler 24/7');
    }

    protected function darBienvenida(): void
    {
        // Bienvenida personalizada VIP
        $this->result->agregarInfo("⭐ ¡Bienvenido/a de nuevo {$this->cliente->nombre}! Gracias por su preferencia");
    }
}

/**
 * Implementación concreta: Check-in para grupos/familias
 */
class CheckInGrupo extends CheckInTemplate
{
    protected function validarDocumentacion(): bool
    {
        // Validar documentación de todos los miembros
        $numeroHuespedes = $this->reserva->numero_huespedes;
        $this->result->agregarInfo("Validando documentación de {$numeroHuespedes} huéspedes");

        return true;
    }

    protected function verificarPago(): bool
    {
        // Verificar pago completo
        $pagoCompleto = $this->reserva->pagos()
            ->where('estado', 'completado')
            ->sum('monto');

        if ($pagoCompleto < $this->reserva->precio_total) {
            $faltante = $this->reserva->precio_total - $pagoCompleto;
            $this->result->agregarError('Falta pagar: $'.number_format($faltante, 2));

            return false;
        }

        return true;
    }

    protected function entregarLlaves(): void
    {
        // Entregar múltiples tarjetas de acceso
        $numeroTarjetas = $this->reserva->numero_huespedes;
        $this->result->agregarInfo("{$numeroTarjetas} tarjetas de acceso entregadas");
    }

    protected function ofrecerServiciosAdicionales(): void
    {
        // Servicios para grupos/familias
        $this->result->agregarInfo('Servicios ofrecidos: Actividades familiares, Tours grupales');
    }

    protected function darBienvenida(): void
    {
        $this->result->agregarInfo("¡Bienvenida familia {$this->cliente->apellido}!");
    }
}

/**
 * Implementación concreta: Check-in express (sin contacto)
 */
class CheckInExpress extends CheckInTemplate
{
    protected function validarDocumentacion(): bool
    {
        // Documentación validada previamente online
        $this->result->agregarInfo('📱 Documentación verificada online');

        return true;
    }

    protected function verificarPago(): bool
    {
        // Pago ya procesado online
        $this->result->agregarInfo('📱 Pago procesado previamente');

        return true;
    }

    protected function entregarLlaves(): void
    {
        // Código QR enviado al móvil
        $this->result->agregarInfo('📱 Código de acceso digital enviado al móvil');
    }

    protected function ofrecerServiciosAdicionales(): void
    {
        // Skip - ya se ofrecieron online
        $this->result->agregarInfo('📱 Servicios seleccionados previamente online');
    }

    protected function darBienvenida(): void
    {
        $this->result->agregarInfo('📱 Mensaje de bienvenida enviado via app');
    }

    protected function finalizarCheckIn(): void
    {
        parent::finalizarCheckIn();
        $this->result->agregarInfo('📱 Check-in express completado en 2 minutos');
    }
}

/**
 * Factory para crear el tipo de check-in apropiado
 */
class CheckInFactory
{
    public static function crear(Reserva $reserva): CheckInTemplate
    {
        $cliente = $reserva->cliente;

        // Determinar tipo de check-in según características del cliente/reserva
        $reservasAnteriores = $cliente->reservas()->count();

        // Cliente VIP: más de 10 reservas
        if ($reservasAnteriores >= 10) {
            return new CheckInClienteVIP($reserva);
        }

        // Grupo/Familia: más de 3 huéspedes
        if ($reserva->numero_huespedes > 3) {
            return new CheckInGrupo($reserva);
        }

        // Check-in express si tiene flag especial
        if ($reserva->observaciones && str_contains($reserva->observaciones, 'express')) {
            return new CheckInExpress($reserva);
        }

        // Por defecto: cliente estándar
        return new CheckInClienteEstandar($reserva);
    }
}
