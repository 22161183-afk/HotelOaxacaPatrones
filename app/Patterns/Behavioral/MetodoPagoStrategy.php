<?php

namespace App\Patterns\Behavioral;

use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Reserva;

/**
 * Patrón Strategy para procesar diferentes métodos de pago
 *
 * Define una familia de algoritmos para procesar pagos,
 * encapsula cada uno y los hace intercambiables.
 */
interface PagoStrategy
{
    public function procesarPago(Reserva $reserva, float $monto): array;

    public function validar(array $datos): bool;

    public function calcularComision(float $monto): float;
}

/**
 * Strategy para pago con tarjeta de crédito
 */
class TarjetaCreditoStrategy implements PagoStrategy
{
    private float $comision = 3.5;

    public function procesarPago(Reserva $reserva, float $monto): array
    {
        // Simular procesamiento con pasarela de pago
        $comision = $this->calcularComision($monto);

        return [
            'exito' => true,
            'referencia' => 'TC-'.uniqid(),
            'monto' => $monto,
            'comision' => $comision,
            'total' => $monto + $comision,
            'mensaje' => 'Pago procesado exitosamente con tarjeta de crédito',
        ];
    }

    public function validar(array $datos): bool
    {
        return isset($datos['numero_tarjeta']) &&
               isset($datos['cvv']) &&
               isset($datos['fecha_expiracion']) &&
               isset($datos['nombre_titular']);
    }

    public function calcularComision(float $monto): float
    {
        return $monto * ($this->comision / 100);
    }
}

/**
 * Strategy para pago con tarjeta de débito
 */
class TarjetaDebitoStrategy implements PagoStrategy
{
    private float $comision = 2.0;

    public function procesarPago(Reserva $reserva, float $monto): array
    {
        $comision = $this->calcularComision($monto);

        return [
            'exito' => true,
            'referencia' => 'TD-'.uniqid(),
            'monto' => $monto,
            'comision' => $comision,
            'total' => $monto + $comision,
            'mensaje' => 'Pago procesado exitosamente con tarjeta de débito',
        ];
    }

    public function validar(array $datos): bool
    {
        return isset($datos['numero_tarjeta']) &&
               isset($datos['nip']);
    }

    public function calcularComision(float $monto): float
    {
        return $monto * ($this->comision / 100);
    }
}

/**
 * Strategy para pago con PayPal
 */
class PayPalStrategy implements PagoStrategy
{
    private float $comision = 4.0;

    public function procesarPago(Reserva $reserva, float $monto): array
    {
        $comision = $this->calcularComision($monto);

        // Simular integración con API de PayPal
        return [
            'exito' => true,
            'referencia' => 'PP-'.uniqid(),
            'monto' => $monto,
            'comision' => $comision,
            'total' => $monto + $comision,
            'url_aprobacion' => 'https://paypal.com/checkout/'.uniqid(),
            'mensaje' => 'Redirigir a PayPal para completar el pago',
        ];
    }

    public function validar(array $datos): bool
    {
        return isset($datos['email_paypal']);
    }

    public function calcularComision(float $monto): float
    {
        return $monto * ($this->comision / 100);
    }
}

/**
 * Strategy para transferencia bancaria
 */
class TransferenciaBancariaStrategy implements PagoStrategy
{
    private float $comision = 0.0;

    public function procesarPago(Reserva $reserva, float $monto): array
    {
        return [
            'exito' => false,
            'pendiente' => true,
            'referencia' => 'TB-'.uniqid(),
            'monto' => $monto,
            'comision' => 0,
            'total' => $monto,
            'datos_bancarios' => [
                'banco' => 'Banco Nacional',
                'cuenta' => '1234567890',
                'clabe' => '012345678901234567',
                'beneficiario' => 'Hotel Oaxaca Grand',
            ],
            'mensaje' => 'Realizar transferencia a la cuenta proporcionada',
        ];
    }

    public function validar(array $datos): bool
    {
        return isset($datos['banco']) &&
               isset($datos['referencia_transferencia']);
    }

    public function calcularComision(float $monto): float
    {
        return 0.0;
    }
}

/**
 * Strategy para pago en efectivo
 */
class EfectivoStrategy implements PagoStrategy
{
    private float $comision = 0.0;

    public function procesarPago(Reserva $reserva, float $monto): array
    {
        return [
            'exito' => false,
            'pendiente' => true,
            'referencia' => 'EF-'.uniqid(),
            'monto' => $monto,
            'comision' => 0,
            'total' => $monto,
            'mensaje' => 'Pago pendiente - Realizar en recepción durante check-in',
        ];
    }

    public function validar(array $datos): bool
    {
        return true; // El efectivo no requiere validación previa
    }

    public function calcularComision(float $monto): float
    {
        return 0.0;
    }
}

/**
 * Contexto que usa las estrategias
 */
class PagoContext
{
    private PagoStrategy $strategy;

    private MetodoPago $metodoPago;

    public function __construct(MetodoPago $metodoPago)
    {
        $this->metodoPago = $metodoPago;
        $this->strategy = $this->getStrategy($metodoPago->nombre);
    }

    /**
     * Obtener la estrategia según el método de pago
     */
    private function getStrategy(string $nombreMetodo): PagoStrategy
    {
        return match ($nombreMetodo) {
            'Tarjeta de Crédito' => new TarjetaCreditoStrategy,
            'Tarjeta de Débito' => new TarjetaDebitoStrategy,
            'PayPal' => new PayPalStrategy,
            'Transferencia Bancaria' => new TransferenciaBancariaStrategy,
            'Efectivo' => new EfectivoStrategy,
            default => new EfectivoStrategy,
        };
    }

    /**
     * Cambiar estrategia dinámicamente
     */
    public function setStrategy(PagoStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Procesar el pago usando la estrategia seleccionada
     */
    public function procesarPago(Reserva $reserva, float $monto, array $datos = []): Pago
    {
        // Validar datos según la estrategia
        if (! $this->strategy->validar($datos)) {
            throw new \Exception('Datos de pago inválidos');
        }

        // Procesar el pago
        $resultado = $this->strategy->procesarPago($reserva, $monto);

        // Crear registro de pago
        $pago = Pago::create([
            'reserva_id' => $reserva->id,
            'metodo_pago_id' => $this->metodoPago->id,
            'monto' => $resultado['monto'],
            'comision' => $resultado['comision'],
            'estado' => $resultado['exito'] ? 'completado' : 'pendiente',
            'referencia' => $resultado['referencia'],
            'observaciones' => $resultado['mensaje'],
            'fecha_pago' => $resultado['exito'] ? now() : null,
        ]);

        return $pago;
    }
}
