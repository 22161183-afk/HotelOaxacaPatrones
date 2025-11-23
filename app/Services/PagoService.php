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

        // Simular procesamiento de pago
        $resultado = [
            'exito' => true,
            'transaccion_id' => 'TXN_'.uniqid(),
            'referencia' => strtoupper($metodo->nombre).'_'.time(),
            'mensaje' => 'Pago procesado exitosamente',
        ];

        if (! $resultado['exito']) {
            throw new \Exception($resultado['mensaje']);
        }

        // Registrar pago
        $pago = Pago::create([
            'reserva_id' => $reservaId,
            'metodo_pago_id' => $metodoPagoId,
            'monto' => $reserva->precio_total,
            'estado' => 'completado',
            'transaccion_id' => $resultado['transaccion_id'],
            'referencia' => $resultado['referencia'] ?? null,
        ]);

        // Actualizar estado de reserva
        $reserva->update(['estado' => 'completada']);

        // Disparar evento
        event(new PagoRealizado($pago));

        return $pago;
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
