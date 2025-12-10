<?php

namespace App\Patterns\Behavioral;

use App\Models\Reserva;
use Carbon\Carbon;

/**
 * Patrón Strategy para Cálculo de Precios
 *
 * Permite aplicar diferentes algoritmos de cálculo de precio según estrategia.
 */
interface PricingStrategy
{
    public function calcular(float $precioBase, Reserva $reserva): float;
}

/**
 * Estrategia: Precio Normal
 */
class PrecioNormal implements PricingStrategy
{
    public function calcular(float $precioBase, Reserva $reserva): float
    {
        $noches = $reserva->calcularNoches();

        return $precioBase * $noches;
    }
}

/**
 * Estrategia: Precio por Temporada Alta
 */
class PrecioTemporada implements PricingStrategy
{
    public function calcular(float $precioBase, Reserva $reserva): float
    {
        $noches = $reserva->calcularNoches();
        $es_temporada = $this->esTemporada($reserva->fecha_inicio);
        $multiplicador = $es_temporada ? 1.5 : 1.0;

        return $precioBase * $noches * $multiplicador;
    }

    private function esTemporada(Carbon $fecha)
    {
        $mes = $fecha->month;

        return in_array($mes, [7, 8, 12]); // Julio, agosto, diciembre
    }
}

/**
 * Estrategia: Descuento por Fidelidad
 */
class PrecioFidelidad implements PricingStrategy
{
    public function calcular(float $precioBase, Reserva $reserva): float
    {
        $noches = $reserva->calcularNoches();
        $total_reservas_cliente = $reserva->cliente->reservas->count();

        $descuento = match (true) {
            $total_reservas_cliente >= 10 => 0.20,
            $total_reservas_cliente >= 5 => 0.15,
            $total_reservas_cliente >= 3 => 0.10,
            default => 0
        };

        $precio = $precioBase * $noches;

        return $precio - ($precio * $descuento);
    }
}

/**
 * Estrategia: Descuento de Última Hora
 */
class PrecioUltimaHora implements PricingStrategy
{
    public function calcular(float $precioBase, Reserva $reserva): float
    {
        $noches = $reserva->calcularNoches();
        $dias_hasta = now()->diffInDays($reserva->fecha_inicio);

        if ($dias_hasta <= 2) {
            return $precioBase * $noches * 0.70; // 30% descuento
        } elseif ($dias_hasta <= 5) {
            return $precioBase * $noches * 0.85; // 15% descuento
        }

        return $precioBase * $noches;
    }
}

/**
 * Contexto para aplicar estrategias de precio
 */
class PricingContext
{
    private PricingStrategy $strategy;

    public function __construct(PricingStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(PricingStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function calcularPrecio(float $precioBase, Reserva $reserva): float
    {
        return $this->strategy->calcular($precioBase, $reserva);
    }
}

/**
 * Calculador de Precio (alias para mantener compatibilidad)
 */
class CalculadorPrecio
{
    private PricingStrategy $strategy;

    public function __construct(PricingStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(PricingStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function calcular($habitacion, $noches, $reserva = null): float
    {
        // Si no se proporciona una reserva, crear una temporal básica
        if ($reserva === null) {
            $precioBase = $habitacion->precio_base ?? $habitacion->precio_noche ?? 0;

            return $precioBase * $noches;
        }

        // Usar la reserva proporcionada
        $precioBase = $habitacion->precio_base ?? $habitacion->precio_noche ?? 0;

        return $this->strategy->calcular($precioBase, $reserva);
    }
}
