<?php

namespace App\Services;

use App\Events\PagoRealizado;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Support\Str;

class PagoService
{
    public function procesarPago($reservaId, $metodoPagoId, $datos)
    {
        $reserva = Reserva::findOrFail($reservaId);
        $metodo = MetodoPago::findOrFail($metodoPagoId);

        if ($reserva->estado !== 'confirmada') {
            throw new \Exception('La reserva debe estar confirmada para procesar el pago');
        }

        // ============================================================
        // ADAPTER PATTERN - Usar adaptador de pasarela según método
        // ============================================================
        $tipoPasarela = $this->determinarPasarela($metodo->tipo);
        $adapter = \App\Patterns\Structural\Adapters\PasarelaPagoAdapterFactory::crear($tipoPasarela);

        // ============================================================
        // STRATEGY PATTERN - Procesar pago según método
        // ============================================================
        $strategy = $this->obtenerEstrategiaPago($metodo->tipo);
        $resultado = $strategy->procesar($reserva->precio_total, $datos);

        // Simular procesamiento con el Adapter
        $resultadoAdapter = $adapter->procesarPago(
            $reserva->precio_total,
            [
                'numero' => $datos['numero_tarjeta'] ?? null,
                'cvv' => $datos['cvv'] ?? null,
                'expiracion' => $datos['expiracion'] ?? null,
                'titular' => $datos['titular'] ?? $reserva->cliente->nombre,
            ]
        );

        if (! $resultadoAdapter['exito']) {
            throw new \Exception($resultadoAdapter['mensaje']);
        }

        // Registrar pago
        $pago = Pago::create([
            'reserva_id' => $reservaId,
            'metodo_pago_id' => $metodoPagoId,
            'monto' => $reserva->precio_total,
            'estado' => 'completado',
            'transaccion_id' => $resultadoAdapter['transaccion_id'],
            'referencia' => $resultadoAdapter['referencia'] ?? strtoupper($metodo->nombre).'_'.time(),
            'observaciones' => 'Procesado con '.$tipoPasarela.' usando Strategy y Adapter patterns',
        ]);

        // Actualizar estado de reserva
        $reserva->update(['estado' => 'completada']);

        // Disparar evento
        event(new PagoRealizado($pago));

        return $pago;
    }

    /**
     * Determinar qué pasarela usar según tipo de método
     */
    private function determinarPasarela(string $tipoMetodo): string
    {
        return match ($tipoMetodo) {
            'tarjeta_credito' => 'stripe',
            'tarjeta_debito' => 'stripe',
            'transferencia' => 'mercadopago',
            'efectivo' => 'paypal',
            default => 'stripe',
        };
    }

    /**
     * Obtener estrategia de pago según tipo de método
     */
    private function obtenerEstrategiaPago(string $tipoMetodo): \App\Patterns\Behavioral\MetodoPagoStrategy
    {
        return match ($tipoMetodo) {
            'tarjeta_credito' => new \App\Patterns\Behavioral\TarjetaCreditoStrategy,
            'tarjeta_debito' => new \App\Patterns\Behavioral\TarjetaDebitoStrategy,
            'transferencia' => new \App\Patterns\Behavioral\TransferenciaStrategy,
            'efectivo' => new \App\Patterns\Behavioral\EfectivoStrategy,
            default => new \App\Patterns\Behavioral\TarjetaCreditoStrategy,
        };
    }

    public function procesarReembolso($pagoId, $razon = null)
    {
        $pago = Pago::findOrFail($pagoId);

        // STRATEGY PATTERN - Diferentes estrategias de reembolso
        $diasAntes = now()->diffInDays($pago->reserva->fecha_inicio);

        if ($diasAntes >= 7) {
            $porcentaje = 1.0; // Reembolso completo
        } elseif ($diasAntes >= 3) {
            $porcentaje = 0.75; // 75% reembolso
        } else {
            $porcentaje = 0.50; // 50% reembolso
        }

        $monto = $pago->monto * $porcentaje;

        // Crear nuevo pago de reembolso (negativo)
        $reembolso = Pago::create([
            'reserva_id' => $pago->reserva_id,
            'metodo_pago_id' => $pago->metodo_pago_id,
            'monto' => -$monto,
            'estado' => 'reembolsado',
            'transaccion_id' => 'REFUND_'.Str::random(10),
            'referencia' => $razon,
        ]);

        $pago->reserva->update(['estado' => 'cancelada']);

        return $reembolso;
    }

    public function obtenerPagosPorReserva($reservaId)
    {
        return Pago::where('reserva_id', $reservaId)
            ->with('metodoPago')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
