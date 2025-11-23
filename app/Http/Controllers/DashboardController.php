<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        // ESTADÍSTICAS PRINCIPALES
        $reservasHoy = Reserva::whereDate('fecha_inicio', $today)
            ->count();

        $habitacionesDisponibles = Habitacion::where('estado', 'disponible')
            ->count();

        $habitacionesTotales = Habitacion::count();
        $ocupacion = $habitacionesTotales > 0
            ? round(((($habitacionesTotales - $habitacionesDisponibles) / $habitacionesTotales) * 100), 2)
            : 0;

        $pagosPendientes = Reserva::where('estado', 'pendiente')
            ->sum('precio_total');

        $ingresosHoy = Pago::whereDate('created_at', $today)
            ->where('estado', 'completado')
            ->sum('monto');

        $ingresosTotal = Pago::where('estado', 'completado')
            ->sum('monto');

        // RESERVAS RECIENTES
        $reservasRecientes = Reserva::with('cliente', 'habitacion', 'servicios')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // PRÓXIMAS RESERVAS (próximos 7 días)
        $fechaFin = now()->addDays(7);
        $proximasReservas = Reserva::where('fecha_inicio', '>=', $today)
            ->where('fecha_inicio', '<=', $fechaFin)
            ->with('cliente', 'habitacion')
            ->orderBy('fecha_inicio')
            ->get();

        // HABITACIONES CON RESERVAS PRÓXIMAS
        $habitacionesProximas = Habitacion::with('reservas')
            ->whereHas('reservas', function ($query) use ($today, $fechaFin) {
                $query->where('fecha_inicio', '>=', $today)
                    ->where('fecha_inicio', '<=', $fechaFin);
            })
            ->get();

        // CLIENTES TOP
        $clientesTop = Cliente::withCount('reservas')
            ->orderBy('reservas_count', 'desc')
            ->take(5)
            ->get();

        // SERVICIOS MÁS VENDIDOS
        $serviciosMasVendidos = DB::table('reserva_servicio')
            ->join('servicios', 'reserva_servicio.servicio_id', '=', 'servicios.id')
            ->selectRaw('servicios.nombre, COUNT(*) as cantidad, SUM(reserva_servicio.subtotal) as total')
            ->groupBy('servicios.id', 'servicios.nombre')
            ->orderBy('cantidad', 'desc')
            ->take(5)
            ->get();

        // OCUPACIÓN POR TIPO DE HABITACIÓN
        $ocupacionPorTipo = DB::table('habitacions')
            ->join('tipo_habitacions', 'habitacions.tipo_habitacion_id', '=', 'tipo_habitacions.id')
            ->selectRaw('tipo_habitacions.nombre, COUNT(*) as total, SUM(CASE WHEN habitacions.estado = "disponible" THEN 1 ELSE 0 END) as disponibles')
            ->groupBy('tipo_habitacions.id', 'tipo_habitacions.nombre')
            ->get();

        // GRÁFICO: Ingresos últimos 7 días
        $ingresosUltimaSemana = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->format('Y-m-d');
            $ingreso = Pago::whereDate('created_at', $fecha)
                ->where('estado', 'completado')
                ->sum('monto');
            $ingresosUltimaSemana[$fecha] = $ingreso;
        }

        return view('dashboard', [
            'reservasHoy' => $reservasHoy,
            'habitacionesDisponibles' => $habitacionesDisponibles,
            'habitacionesTotales' => $habitacionesTotales,
            'ocupacion' => $ocupacion,
            'pagosPendientes' => $pagosPendientes,
            'ingresosHoy' => $ingresosHoy,
            'ingresosTotal' => $ingresosTotal,
            'reservasRecientes' => $reservasRecientes,
            'proximasReservas' => $proximasReservas,
            'habitacionesProximas' => $habitacionesProximas,
            'clientesTop' => $clientesTop,
            'serviciosMasVendidos' => $serviciosMasVendidos,
            'ocupacionPorTipo' => $ocupacionPorTipo,
            'ingresosUltimaSemana' => $ingresosUltimaSemana,
        ]);
    }

    public function reservas()
    {
        $reservas = Reserva::with('cliente', 'habitacion', 'servicios', 'pagos')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('reservas.index', ['reservas' => $reservas]);
    }

    public function habitaciones()
    {
        $habitaciones = Habitacion::with('tipoHabitacion', 'reservas')
            ->paginate(15);

        return view('habitaciones.index', ['habitaciones' => $habitaciones]);
    }

    public function pagos()
    {
        $pagos = Pago::with('reserva', 'metodoPago')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pagos.index', ['pagos' => $pagos]);
    }
}
