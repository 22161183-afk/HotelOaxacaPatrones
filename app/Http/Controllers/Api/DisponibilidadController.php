<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\Reserva;
use Illuminate\Http\Request;

/**
 * Controlador de Disponibilidad de Habitaciones
 *
 * Viñeta 3: La aplicación deberá mostrar disponibilidad de habitaciones en tiempo real.
 */
class DisponibilidadController extends Controller
{
    /**
     * Verificar disponibilidad de una habitación específica
     * GET /api/reservas/disponibilidad/check
     */
    public function verificar(Request $request)
    {
        $request->validate([
            'habitacion_id' => 'required|exists:habitacions,id',
            'fecha_inicio' => 'required|date|after_or_equal:'.now()->toDateString(),
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        $habitacion = Habitacion::findOrFail($request->habitacion_id);

        // Verificar si existe alguna reserva que se solape con las fechas solicitadas
        $conflicto = Reserva::where('habitacion_id', $request->habitacion_id)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where(function ($query) use ($request) {
                // Verificar solapamiento de fechas
                $query->whereBetween('fecha_inicio', [$request->fecha_inicio, $request->fecha_fin])
                    ->orWhereBetween('fecha_fin', [$request->fecha_inicio, $request->fecha_fin])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('fecha_inicio', '<=', $request->fecha_inicio)
                            ->where('fecha_fin', '>=', $request->fecha_fin);
                    });
            })
            ->exists();

        $disponible = ! $conflicto && $habitacion->estado !== 'mantenimiento';

        return response()->json([
            'success' => true,
            'disponible' => $disponible,
            'habitacion' => [
                'id' => $habitacion->id,
                'numero' => $habitacion->numero,
                'tipo' => $habitacion->tipoHabitacion->nombre ?? 'N/A',
                'estado' => $habitacion->estado,
                'precio_noche' => $habitacion->precio_noche,
            ],
            'fechas' => [
                'inicio' => $request->fecha_inicio,
                'fin' => $request->fecha_fin,
                'noches' => \Carbon\Carbon::parse($request->fecha_inicio)
                    ->diffInDays(\Carbon\Carbon::parse($request->fecha_fin)),
            ],
            'mensaje' => $disponible
                ? 'La habitación está disponible para las fechas seleccionadas'
                : 'La habitación no está disponible para las fechas seleccionadas',
        ]);
    }

    /**
     * Buscar habitaciones disponibles según criterios
     * GET /api/habitaciones/disponibles/search
     */
    public function search(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date|after_or_equal:'.now()->toDateString(),
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'tipo_habitacion_id' => 'nullable|exists:tipo_habitacions,id',
            'capacidad_minima' => 'nullable|integer|min:1',
            'precio_maximo' => 'nullable|numeric|min:0',
        ]);

        // Obtener todas las habitaciones que NO están en mantenimiento
        $query = Habitacion::with('tipoHabitacion')
            ->where('estado', '!=', 'mantenimiento');

        // Filtrar por tipo de habitación
        if ($request->filled('tipo_habitacion_id')) {
            $query->where('tipo_habitacion_id', $request->tipo_habitacion_id);
        }

        // Filtrar por capacidad mínima
        if ($request->filled('capacidad_minima')) {
            $query->where('capacidad', '>=', $request->capacidad_minima);
        }

        // Filtrar por precio máximo
        if ($request->filled('precio_maximo')) {
            $query->where('precio_noche', '<=', $request->precio_maximo);
        }

        $habitaciones = $query->get();

        // Filtrar habitaciones que NO tienen reservas en las fechas solicitadas
        $habitacionesDisponibles = $habitaciones->filter(function ($habitacion) use ($request) {
            $conflicto = Reserva::where('habitacion_id', $habitacion->id)
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->where(function ($query) use ($request) {
                    $query->whereBetween('fecha_inicio', [$request->fecha_inicio, $request->fecha_fin])
                        ->orWhereBetween('fecha_fin', [$request->fecha_inicio, $request->fecha_fin])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('fecha_inicio', '<=', $request->fecha_inicio)
                                ->where('fecha_fin', '>=', $request->fecha_fin);
                        });
                })
                ->exists();

            return ! $conflicto;
        });

        $noches = \Carbon\Carbon::parse($request->fecha_inicio)
            ->diffInDays(\Carbon\Carbon::parse($request->fecha_fin));

        // Formatear resultados
        $resultado = $habitacionesDisponibles->map(function ($habitacion) use ($noches) {
            return [
                'id' => $habitacion->id,
                'numero' => $habitacion->numero,
                'tipo' => $habitacion->tipoHabitacion->nombre ?? 'N/A',
                'capacidad' => $habitacion->capacidad,
                'precio_noche' => $habitacion->precio_noche,
                'precio_total' => $habitacion->precio_noche * $noches,
                'amenidades' => $habitacion->amenidades,
                'descripcion' => $habitacion->descripcion,
                'piso' => $habitacion->piso,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'habitaciones_disponibles' => $resultado,
            'total' => $resultado->count(),
            'filtros_aplicados' => [
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'noches' => $noches,
                'tipo_habitacion_id' => $request->tipo_habitacion_id,
                'capacidad_minima' => $request->capacidad_minima,
                'precio_maximo' => $request->precio_maximo,
            ],
        ]);
    }

    /**
     * Obtener el calendario de disponibilidad de una habitación
     * GET /api/habitaciones/{id}/calendario
     */
    public function calendario(Request $request, $habitacionId)
    {
        $request->validate([
            'mes' => 'nullable|integer|min:1|max:12',
            'anio' => 'nullable|integer|min:2024',
        ]);

        $habitacion = Habitacion::with('tipoHabitacion')->findOrFail($habitacionId);

        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        $inicioMes = \Carbon\Carbon::create($anio, $mes, 1);
        $finMes = $inicioMes->copy()->endOfMonth();

        // Obtener todas las reservas del mes
        $reservas = Reserva::where('habitacion_id', $habitacionId)
            ->whereIn('estado', ['pendiente', 'confirmada', 'ocupada'])
            ->where(function ($query) use ($inicioMes, $finMes) {
                $query->whereBetween('fecha_inicio', [$inicioMes, $finMes])
                    ->orWhereBetween('fecha_fin', [$inicioMes, $finMes])
                    ->orWhere(function ($q) use ($inicioMes, $finMes) {
                        $q->where('fecha_inicio', '<=', $inicioMes)
                            ->where('fecha_fin', '>=', $finMes);
                    });
            })
            ->get();

        // Crear calendario día por día
        $calendario = [];
        $fecha = $inicioMes->copy();

        while ($fecha->lte($finMes)) {
            $disponible = true;
            $reservaDelDia = null;

            foreach ($reservas as $reserva) {
                if ($fecha->between($reserva->fecha_inicio, $reserva->fecha_fin)) {
                    $disponible = false;
                    $reservaDelDia = [
                        'id' => $reserva->id,
                        'estado' => $reserva->estado,
                        'cliente' => $reserva->cliente->nombre.' '.$reserva->cliente->apellido,
                    ];
                    break;
                }
            }

            $calendario[] = [
                'fecha' => $fecha->format('Y-m-d'),
                'dia_semana' => $fecha->dayName,
                'disponible' => $disponible && $habitacion->estado !== 'mantenimiento',
                'reserva' => $reservaDelDia,
            ];

            $fecha->addDay();
        }

        return response()->json([
            'success' => true,
            'habitacion' => [
                'id' => $habitacion->id,
                'numero' => $habitacion->numero,
                'tipo' => $habitacion->tipoHabitacion->nombre ?? 'N/A',
                'estado' => $habitacion->estado,
            ],
            'mes' => $mes,
            'anio' => $anio,
            'calendario' => $calendario,
        ]);
    }
}
