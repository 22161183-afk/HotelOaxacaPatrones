<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
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

        return view('admin.dashboard', [
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

        return view('admin.reservas', ['reservas' => $reservas]);
    }

    public function habitaciones()
    {
        $habitaciones = Habitacion::with('tipoHabitacion', 'reservas')
            ->paginate(15);

        return view('admin.habitaciones', ['habitaciones' => $habitaciones]);
    }

    public function pagos()
    {
        $pagos = Pago::with('reserva', 'metodoPago')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.pagos', ['pagos' => $pagos]);
    }

    public function editHabitacion($id)
    {
        $habitacion = Habitacion::with('tipoHabitacion')->findOrFail($id);
        $tiposHabitacion = \App\Models\TipoHabitacion::all();

        return view('admin.habitaciones.edit', [
            'habitacion' => $habitacion,
            'tiposHabitacion' => $tiposHabitacion,
        ]);
    }

    public function updateHabitacion(\Illuminate\Http\Request $request, $id)
    {
        $habitacion = Habitacion::findOrFail($id);

        $validated = $request->validate([
            'numero' => 'required|string|max:10',
            'tipo_habitacion_id' => 'required|exists:tipo_habitacions,id',
            'piso' => 'required|integer',
            'capacidad' => 'required|integer|min:1',
            'precio_base' => 'required|numeric|min:0',
            'estado' => 'required|in:disponible,ocupada,mantenimiento',
            'descripcion' => 'nullable|string',
            'amenidades' => 'nullable|string',
        ]);

        // Convertir amenidades de string a array
        if (isset($validated['amenidades'])) {
            $amenidades = array_filter(array_map('trim', explode(',', $validated['amenidades'])));
            $validated['amenidades'] = $amenidades;
        }

        $habitacion->update($validated);

        return redirect()
            ->route('admin.habitaciones.index')
            ->with('success', 'Habitación actualizada correctamente');
    }
}
