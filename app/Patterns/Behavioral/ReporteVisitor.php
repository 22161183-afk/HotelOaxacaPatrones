<?php

namespace App\Patterns\Behavioral;

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Reserva;

/**
 * Patrón Visitor para generar reportes y estadísticas
 *
 * Viñeta 24: El sistema deberá generar reportes y estadísticas
 * sobre reservas, clientes y ocupación
 */

/**
 * Visitor interface
 */
interface ReporteVisitor
{
    public function visitarReserva(Reserva $reserva): void;

    public function visitarCliente(Cliente $cliente): void;

    public function visitarHabitacion(Habitacion $habitacion): void;

    public function visitarPago(Pago $pago): void;

    public function generarReporte(): array;
}

/**
 * Element interface
 */
interface ReportableElement
{
    public function aceptar(ReporteVisitor $visitor): void;
}

/**
 * Concrete Visitor: Reporte de ingresos
 */
class IngresosVisitor implements ReporteVisitor
{
    private float $totalIngresos = 0;

    private array $ingresosPorMes = [];

    private array $ingresosPorTipo = [];

    private int $totalReservas = 0;

    private int $reservasCanceladas = 0;

    public function visitarReserva(Reserva $reserva): void
    {
        $this->totalReservas++;

        if ($reserva->estado === 'cancelada') {
            $this->reservasCanceladas++;

            return;
        }

        if (in_array($reserva->estado, ['confirmada', 'completada'])) {
            $this->totalIngresos += $reserva->precio_total;

            // Agrupar por mes
            $mes = $reserva->fecha_inicio->format('Y-m');
            if (! isset($this->ingresosPorMes[$mes])) {
                $this->ingresosPorMes[$mes] = 0;
            }
            $this->ingresosPorMes[$mes] += $reserva->precio_total;

            // Agrupar por tipo de habitación
            $tipo = $reserva->habitacion->tipoHabitacion->nombre;
            if (! isset($this->ingresosPorTipo[$tipo])) {
                $this->ingresosPorTipo[$tipo] = 0;
            }
            $this->ingresosPorTipo[$tipo] += $reserva->precio_total;
        }
    }

    public function visitarCliente(Cliente $cliente): void
    {
        // No necesario para este reporte
    }

    public function visitarHabitacion(Habitacion $habitacion): void
    {
        // No necesario para este reporte
    }

    public function visitarPago(Pago $pago): void
    {
        // No necesario para este reporte
    }

    public function generarReporte(): array
    {
        return [
            'tipo' => 'Reporte de Ingresos',
            'total_ingresos' => $this->totalIngresos,
            'total_reservas' => $this->totalReservas,
            'reservas_canceladas' => $this->reservasCanceladas,
            'tasa_cancelacion' => $this->totalReservas > 0
                ? round(($this->reservasCanceladas / $this->totalReservas) * 100, 2)
                : 0,
            'ingreso_promedio_por_reserva' => $this->totalReservas > 0
                ? round($this->totalIngresos / ($this->totalReservas - $this->reservasCanceladas), 2)
                : 0,
            'ingresos_por_mes' => $this->ingresosPorMes,
            'ingresos_por_tipo_habitacion' => $this->ingresosPorTipo,
        ];
    }
}

/**
 * Concrete Visitor: Reporte de ocupación
 */
class OcupacionVisitor implements ReporteVisitor
{
    private int $habitacionesDisponibles = 0;

    private int $habitacionesOcupadas = 0;

    private int $habitacionesReservadas = 0;

    private int $habitacionesMantenimiento = 0;

    private int $totalHabitaciones = 0;

    private array $ocupacionPorTipo = [];

    private array $ocupacionPorPiso = [];

    public function visitarReserva(Reserva $reserva): void
    {
        // No necesario para este reporte
    }

    public function visitarCliente(Cliente $cliente): void
    {
        // No necesario para este reporte
    }

    public function visitarHabitacion(Habitacion $habitacion): void
    {
        $this->totalHabitaciones++;

        match ($habitacion->estado) {
            'disponible' => $this->habitacionesDisponibles++,
            'ocupada' => $this->habitacionesOcupadas++,
            'reservada' => $this->habitacionesReservadas++,
            'mantenimiento' => $this->habitacionesMantenimiento++,
            default => null
        };

        // Ocupación por tipo
        $tipo = $habitacion->tipoHabitacion->nombre;
        if (! isset($this->ocupacionPorTipo[$tipo])) {
            $this->ocupacionPorTipo[$tipo] = [
                'total' => 0,
                'disponibles' => 0,
                'ocupadas' => 0,
            ];
        }
        $this->ocupacionPorTipo[$tipo]['total']++;
        if ($habitacion->estado === 'disponible') {
            $this->ocupacionPorTipo[$tipo]['disponibles']++;
        }
        if ($habitacion->estado === 'ocupada') {
            $this->ocupacionPorTipo[$tipo]['ocupadas']++;
        }

        // Ocupación por piso
        $piso = $habitacion->piso;
        if (! isset($this->ocupacionPorPiso[$piso])) {
            $this->ocupacionPorPiso[$piso] = [
                'total' => 0,
                'disponibles' => 0,
                'ocupadas' => 0,
            ];
        }
        $this->ocupacionPorPiso[$piso]['total']++;
        if ($habitacion->estado === 'disponible') {
            $this->ocupacionPorPiso[$piso]['disponibles']++;
        }
        if ($habitacion->estado === 'ocupada') {
            $this->ocupacionPorPiso[$piso]['ocupadas']++;
        }
    }

    public function visitarPago(Pago $pago): void
    {
        // No necesario para este reporte
    }

    public function generarReporte(): array
    {
        $habitacionesOperativas = $this->totalHabitaciones - $this->habitacionesMantenimiento;
        $tasaOcupacion = $habitacionesOperativas > 0
            ? round((($this->habitacionesOcupadas + $this->habitacionesReservadas) / $habitacionesOperativas) * 100, 2)
            : 0;

        return [
            'tipo' => 'Reporte de Ocupación',
            'total_habitaciones' => $this->totalHabitaciones,
            'disponibles' => $this->habitacionesDisponibles,
            'ocupadas' => $this->habitacionesOcupadas,
            'reservadas' => $this->habitacionesReservadas,
            'en_mantenimiento' => $this->habitacionesMantenimiento,
            'tasa_ocupacion' => $tasaOcupacion,
            'ocupacion_por_tipo' => $this->ocupacionPorTipo,
            'ocupacion_por_piso' => $this->ocupacionPorPiso,
        ];
    }
}

/**
 * Concrete Visitor: Reporte de clientes
 */
class ClientesVisitor implements ReporteVisitor
{
    private int $totalClientes = 0;

    private int $clientesActivos = 0;

    private array $clientesVIP = [];

    private array $clientesPorPais = [];

    private float $gastoTotal = 0;

    public function visitarReserva(Reserva $reserva): void
    {
        // No necesario para este reporte
    }

    public function visitarCliente(Cliente $cliente): void
    {
        $this->totalClientes++;

        // Clientes activos (con al menos una reserva)
        $reservas = $cliente->reservas()->count();
        if ($reservas > 0) {
            $this->clientesActivos++;
        }

        // Clientes VIP (más de 5 reservas)
        if ($reservas >= 5) {
            $this->clientesVIP[] = [
                'nombre' => $cliente->nombre.' '.$cliente->apellido,
                'email' => $cliente->email,
                'total_reservas' => $reservas,
                'gasto_total' => $cliente->reservas()->sum('precio_total'),
            ];
        }

        // Clientes por país
        $pais = $cliente->pais ?? 'No especificado';
        if (! isset($this->clientesPorPais[$pais])) {
            $this->clientesPorPais[$pais] = 0;
        }
        $this->clientesPorPais[$pais]++;

        // Gasto total
        $this->gastoTotal += $cliente->reservas()->sum('precio_total');
    }

    public function visitarHabitacion(Habitacion $habitacion): void
    {
        // No necesario para este reporte
    }

    public function visitarPago(Pago $pago): void
    {
        // No necesario para este reporte
    }

    public function generarReporte(): array
    {
        // Ordenar VIPs por gasto total
        usort($this->clientesVIP, fn ($a, $b) => $b['gasto_total'] <=> $a['gasto_total']);

        return [
            'tipo' => 'Reporte de Clientes',
            'total_clientes' => $this->totalClientes,
            'clientes_activos' => $this->clientesActivos,
            'clientes_vip' => count($this->clientesVIP),
            'top_10_vip' => array_slice($this->clientesVIP, 0, 10),
            'clientes_por_pais' => $this->clientesPorPais,
            'gasto_promedio_por_cliente' => $this->clientesActivos > 0
                ? round($this->gastoTotal / $this->clientesActivos, 2)
                : 0,
        ];
    }
}

/**
 * Concrete Visitor: Reporte de métodos de pago
 */
class MetodosPagoVisitor implements ReporteVisitor
{
    private array $pagosPorMetodo = [];

    private float $totalPagado = 0;

    private int $totalPagos = 0;

    public function visitarReserva(Reserva $reserva): void
    {
        // No necesario para este reporte
    }

    public function visitarCliente(Cliente $cliente): void
    {
        // No necesario para este reporte
    }

    public function visitarHabitacion(Habitacion $habitacion): void
    {
        // No necesario para este reporte
    }

    public function visitarPago(Pago $pago): void
    {
        if ($pago->estado === 'completado') {
            $this->totalPagos++;
            $this->totalPagado += $pago->monto;

            $metodo = $pago->metodoPago->nombre;
            if (! isset($this->pagosPorMetodo[$metodo])) {
                $this->pagosPorMetodo[$metodo] = [
                    'cantidad' => 0,
                    'monto_total' => 0,
                ];
            }
            $this->pagosPorMetodo[$metodo]['cantidad']++;
            $this->pagosPorMetodo[$metodo]['monto_total'] += $pago->monto;
        }
    }

    public function generarReporte(): array
    {
        return [
            'tipo' => 'Reporte de Métodos de Pago',
            'total_pagos' => $this->totalPagos,
            'total_pagado' => $this->totalPagado,
            'pago_promedio' => $this->totalPagos > 0
                ? round($this->totalPagado / $this->totalPagos, 2)
                : 0,
            'pagos_por_metodo' => $this->pagosPorMetodo,
        ];
    }
}

/**
 * Concrete Visitor: Reporte consolidado (combina todos los reportes)
 */
class ReporteConsolidadoVisitor implements ReporteVisitor
{
    private IngresosVisitor $ingresosVisitor;

    private OcupacionVisitor $ocupacionVisitor;

    private ClientesVisitor $clientesVisitor;

    private MetodosPagoVisitor $pagoVisitor;

    public function __construct()
    {
        $this->ingresosVisitor = new IngresosVisitor;
        $this->ocupacionVisitor = new OcupacionVisitor;
        $this->clientesVisitor = new ClientesVisitor;
        $this->pagoVisitor = new MetodosPagoVisitor;
    }

    public function visitarReserva(Reserva $reserva): void
    {
        $this->ingresosVisitor->visitarReserva($reserva);
    }

    public function visitarCliente(Cliente $cliente): void
    {
        $this->clientesVisitor->visitarCliente($cliente);
    }

    public function visitarHabitacion(Habitacion $habitacion): void
    {
        $this->ocupacionVisitor->visitarHabitacion($habitacion);
    }

    public function visitarPago(Pago $pago): void
    {
        $this->pagoVisitor->visitarPago($pago);
    }

    public function generarReporte(): array
    {
        return [
            'tipo' => 'Reporte Consolidado',
            'fecha_generacion' => now()->format('Y-m-d H:i:s'),
            'ingresos' => $this->ingresosVisitor->generarReporte(),
            'ocupacion' => $this->ocupacionVisitor->generarReporte(),
            'clientes' => $this->clientesVisitor->generarReporte(),
            'metodos_pago' => $this->pagoVisitor->generarReporte(),
        ];
    }
}

/**
 * Generador de reportes
 */
class ReporteGenerator
{
    /**
     * Generar reporte de ingresos
     */
    public static function generarReporteIngresos(): array
    {
        $visitor = new IngresosVisitor;
        $reservas = Reserva::with(['habitacion.tipoHabitacion'])->get();

        foreach ($reservas as $reserva) {
            $visitor->visitarReserva($reserva);
        }

        return $visitor->generarReporte();
    }

    /**
     * Generar reporte de ocupación
     */
    public static function generarReporteOcupacion(): array
    {
        $visitor = new OcupacionVisitor;
        $habitaciones = Habitacion::with('tipoHabitacion')->get();

        foreach ($habitaciones as $habitacion) {
            $visitor->visitarHabitacion($habitacion);
        }

        return $visitor->generarReporte();
    }

    /**
     * Generar reporte de clientes
     */
    public static function generarReporteClientes(): array
    {
        $visitor = new ClientesVisitor;
        $clientes = Cliente::with('reservas')->get();

        foreach ($clientes as $cliente) {
            $visitor->visitarCliente($cliente);
        }

        return $visitor->generarReporte();
    }

    /**
     * Generar reporte de métodos de pago
     */
    public static function generarReporteMetodosPago(): array
    {
        $visitor = new MetodosPagoVisitor;
        $pagos = Pago::with('metodoPago')->get();

        foreach ($pagos as $pago) {
            $visitor->visitarPago($pago);
        }

        return $visitor->generarReporte();
    }

    /**
     * Generar reporte consolidado
     */
    public static function generarReporteConsolidado(): array
    {
        $visitor = new ReporteConsolidadoVisitor;

        // Visitar reservas
        $reservas = Reserva::with(['habitacion.tipoHabitacion'])->get();
        foreach ($reservas as $reserva) {
            $visitor->visitarReserva($reserva);
        }

        // Visitar habitaciones
        $habitaciones = Habitacion::with('tipoHabitacion')->get();
        foreach ($habitaciones as $habitacion) {
            $visitor->visitarHabitacion($habitacion);
        }

        // Visitar clientes
        $clientes = Cliente::with('reservas')->get();
        foreach ($clientes as $cliente) {
            $visitor->visitarCliente($cliente);
        }

        // Visitar pagos
        $pagos = Pago::with('metodoPago')->get();
        foreach ($pagos as $pago) {
            $visitor->visitarPago($pago);
        }

        return $visitor->generarReporte();
    }
}
