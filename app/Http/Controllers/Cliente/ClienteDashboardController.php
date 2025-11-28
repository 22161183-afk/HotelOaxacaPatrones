<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\Reserva;
use Illuminate\Support\Facades\Auth;

class ClienteDashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $cliente = $usuario->cliente;

        // Si el usuario no tiene un cliente asociado, crear uno
        if (! $cliente) {
            $cliente = \App\Models\Cliente::firstOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'nombre' => $usuario->name,
                    'apellido' => '',
                    'email' => $usuario->email,
                    'telefono' => null,
                ]
            );
        }

        $today = today();

        // Reservas del cliente
        $reservasActivas = Reserva::where('cliente_id', $cliente->id)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where('fecha_inicio', '>=', $today)
            ->with('habitacion', 'servicios')
            ->orderBy('fecha_inicio')
            ->get();

        $reservasPasadas = Reserva::where('cliente_id', $cliente->id)
            ->where('fecha_fin', '<', $today)
            ->with('habitacion', 'servicios')
            ->orderBy('fecha_fin', 'desc')
            ->take(5)
            ->get();

        $reservasCanceladas = Reserva::where('cliente_id', $cliente->id)
            ->where('estado', 'cancelada')
            ->with('habitacion')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Habitaciones disponibles para reservar (solo las que están en estado 'disponible')
        $habitacionesDisponibles = Habitacion::where('estado', 'disponible')
            ->with('tipoHabitacion')
            ->take(8)
            ->get();

        // Estadísticas del cliente
        $totalReservas = Reserva::where('cliente_id', $cliente->id)->count();
        $totalGastado = Reserva::where('cliente_id', $cliente->id)
            ->where('estado', 'completada')
            ->sum('precio_total');

        return view('cliente.dashboard', [
            'cliente' => $cliente,
            'reservasActivas' => $reservasActivas,
            'reservasPasadas' => $reservasPasadas,
            'reservasCanceladas' => $reservasCanceladas,
            'habitacionesDisponibles' => $habitacionesDisponibles,
            'totalReservas' => $totalReservas,
            'totalGastado' => $totalGastado,
        ]);
    }

    public function reservas()
    {
        $usuario = Auth::user();
        $cliente = $usuario->cliente;

        if (! $cliente) {
            $cliente = \App\Models\Cliente::firstOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'nombre' => $usuario->name,
                    'apellido' => '',
                    'email' => $usuario->email,
                    'telefono' => null,
                ]
            );
        }

        $reservas = Reserva::where('cliente_id', $cliente->id)
            ->with('habitacion', 'servicios', 'pagos')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('cliente.reservas', [
            'reservas' => $reservas,
            'cliente' => $cliente,
        ]);
    }

    public function habitaciones(\Illuminate\Http\Request $request)
    {
        // Usar el patrón Interpreter para búsqueda avanzada
        $query = Habitacion::with('tipoHabitacion');

        // Si hay parámetros de búsqueda, usar el Interpreter
        if ($request->hasAny(['tipo', 'capacidad', 'precio_min', 'precio_max', 'piso', 'amenidades'])) {
            $interpreter = \App\Patterns\Behavioral\HabitacionSearchInterpreter::fromRequest($request->all());
            $query = $interpreter->interpret($query);
        } else {
            // Si no hay búsqueda, mostrar todas disponibles, reservadas y ocupadas
            $query->whereIn('estado', ['disponible', 'reservada', 'ocupada'])
                ->orderByRaw("FIELD(estado, 'disponible', 'reservada', 'ocupada')");
        }

        $habitaciones = $query->paginate(12)->withQueryString();

        // Obtener tipos de habitación para el filtro
        $tiposHabitacion = \App\Models\TipoHabitacion::all();

        return view('cliente.habitaciones', [
            'habitaciones' => $habitaciones,
            'tiposHabitacion' => $tiposHabitacion,
        ]);
    }

    public function createReserva(\Illuminate\Http\Request $request)
    {
        $habitacionId = $request->get('habitacion_id');
        $habitacion = null;

        if ($habitacionId) {
            $habitacion = Habitacion::with('tipoHabitacion')->findOrFail($habitacionId);

            // Verificar que la habitación esté disponible
            if ($habitacion->estado !== 'disponible') {
                return redirect()
                    ->route('cliente.habitaciones.index')
                    ->with('error', 'La habitación seleccionada no está disponible');
            }
        }

        // Obtener servicios disponibles
        $serviciosDisponibles = \App\Models\Servicio::where('disponible', true)
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        return view('cliente.reservas.create', [
            'habitacion' => $habitacion,
            'serviciosDisponibles' => $serviciosDisponibles,
        ]);
    }

    public function storeReserva(\Illuminate\Http\Request $request)
    {
        $usuario = Auth::user();
        $cliente = $usuario->cliente;

        if (! $cliente) {
            $cliente = \App\Models\Cliente::firstOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'nombre' => $usuario->name,
                    'apellido' => '',
                    'email' => $usuario->email,
                    'telefono' => null,
                ]
            );
        }

        $validated = $request->validate([
            'habitacion_id' => 'required|exists:habitacions,id',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'numero_huespedes' => 'required|integer|min:1',
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ]);

        // ============================================================
        // FACADE PATTERN - Simplificar creación de reservas
        // ============================================================
        $facade = new \App\Patterns\Structural\ReservaFacade;

        // Preparar datos para el Facade
        $datosReserva = [
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'email' => $cliente->email,
            ],
            'habitacion_id' => $validated['habitacion_id'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'numero_huespedes' => $validated['numero_huespedes'],
            'servicios' => $validated['servicios'] ?? [],
        ];

        // Usar el Facade para crear la reserva completa
        $resultado = $facade->crearReservaCompleta($datosReserva);

        if (! $resultado['exito']) {
            return back()
                ->withInput()
                ->with('error', $resultado['error']);
        }

        $reserva = $resultado['reserva'];

        // Aplicar estrategia de precio óptima automáticamente
        $precioOptimizado = $reserva->aplicarMejorEstrategia();
        $reserva->update(['precio_total' => $precioOptimizado]);

        return redirect()
            ->route('cliente.reservas.index')
            ->with('success', '¡Reserva creada exitosamente! Total: $'.number_format($reserva->precio_total, 2));
    }

    public function editReserva($id)
    {
        $usuario = Auth::user();
        $cliente = $usuario->cliente;

        if (! $cliente) {
            return redirect()
                ->route('cliente.dashboard')
                ->with('error', 'Cliente no encontrado');
        }

        $reserva = Reserva::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->with('habitacion.tipoHabitacion', 'servicios')
            ->firstOrFail();

        // Solo se pueden editar reservas pendientes
        if ($reserva->estado !== 'pendiente') {
            return redirect()
                ->route('cliente.reservas.index')
                ->with('error', 'Solo se pueden editar reservas pendientes');
        }

        // Obtener servicios disponibles
        $serviciosDisponibles = \App\Models\Servicio::where('disponible', true)
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        return view('cliente.reservas.edit', [
            'reserva' => $reserva,
            'habitacion' => $reserva->habitacion,
            'serviciosDisponibles' => $serviciosDisponibles,
        ]);
    }

    public function updateReserva(\Illuminate\Http\Request $request, $id)
    {
        $usuario = Auth::user();
        $cliente = $usuario->cliente;

        if (! $cliente) {
            return redirect()
                ->route('cliente.dashboard')
                ->with('error', 'Cliente no encontrado');
        }

        $reserva = Reserva::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->with('habitacion')
            ->firstOrFail();

        // Solo se pueden editar reservas pendientes
        if ($reserva->estado !== 'pendiente') {
            return redirect()
                ->route('cliente.reservas.index')
                ->with('error', 'Solo se pueden editar reservas pendientes');
        }

        $validated = $request->validate([
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'numero_huespedes' => 'required|integer|min:1',
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ]);

        // Verificar que no haya conflictos con otras reservas
        $conflicto = Reserva::where('habitacion_id', $reserva->habitacion_id)
            ->where('id', '!=', $reserva->id) // Excluir la reserva actual
            ->where(function ($query) use ($validated) {
                $query->whereBetween('fecha_inicio', [$validated['fecha_inicio'], $validated['fecha_fin']])
                    ->orWhereBetween('fecha_fin', [$validated['fecha_inicio'], $validated['fecha_fin']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('fecha_inicio', '<=', $validated['fecha_inicio'])
                            ->where('fecha_fin', '>=', $validated['fecha_fin']);
                    });
            })
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->exists();

        if ($conflicto) {
            return back()
                ->withInput()
                ->with('error', 'La habitación ya está reservada para estas fechas');
        }

        // Calcular nuevo precio total
        $fechaInicio = new \DateTime($validated['fecha_inicio']);
        $fechaFin = new \DateTime($validated['fecha_fin']);
        $dias = $fechaInicio->diff($fechaFin)->days;
        $precioHabitacion = $dias * $reserva->habitacion->precio_base;

        // Actualizar reserva
        $reserva->update([
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'numero_huespedes' => $validated['numero_huespedes'],
            'precio_total' => $precioHabitacion,
            'precio_servicios' => 0,
        ]);

        // Sincronizar servicios (eliminar los antiguos y agregar los nuevos)
        $reserva->servicios()->detach();

        if (isset($validated['servicios']) && count($validated['servicios']) > 0) {
            $precioServicios = 0;
            foreach ($validated['servicios'] as $servicioId) {
                $servicio = \App\Models\Servicio::find($servicioId);
                if ($servicio) {
                    $reserva->servicios()->attach($servicioId, [
                        'cantidad' => 1,
                        'precio_unitario' => $servicio->precio,
                        'subtotal' => $servicio->precio,
                    ]);
                    $precioServicios += $servicio->precio;
                }
            }

            // Actualizar precio total con servicios
            $reserva->update([
                'precio_servicios' => $precioServicios,
                'precio_total' => $precioHabitacion + $precioServicios,
            ]);
        }

        return redirect()
            ->route('cliente.reservas.index')
            ->with('success', 'Reserva actualizada exitosamente. Nuevo total: $'.number_format($reserva->precio_total, 2));
    }

    public function cancelarReserva($id)
    {
        $usuario = Auth::user();
        $cliente = $usuario->cliente;

        if (! $cliente) {
            return redirect()
                ->route('cliente.dashboard')
                ->with('error', 'Cliente no encontrado');
        }

        $reserva = Reserva::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        // Solo se pueden cancelar reservas pendientes o confirmadas
        if (! in_array($reserva->estado, ['pendiente', 'confirmada'])) {
            return redirect()
                ->route('cliente.reservas.index')
                ->with('error', 'Esta reserva no se puede cancelar');
        }

        $reserva->update([
            'estado' => 'cancelada',
            'fecha_cancelacion' => now(),
        ]);

        // Devolver la habitación a estado 'disponible' cuando se cancela la reserva
        $reserva->habitacion->update([
            'estado' => 'disponible',
        ]);

        return redirect()
            ->route('cliente.reservas.index')
            ->with('success', 'Reserva cancelada exitosamente');
    }

    public function createPago(\Illuminate\Http\Request $request)
    {
        $reservaId = $request->get('reserva_id');

        if (! $reservaId) {
            return redirect()
                ->route('cliente.reservas.index')
                ->with('error', 'Debe seleccionar una reserva');
        }

        $usuario = Auth::user();
        $cliente = $usuario->cliente;

        $reserva = Reserva::where('id', $reservaId)
            ->where('cliente_id', $cliente->id)
            ->with('habitacion.tipoHabitacion')
            ->firstOrFail();

        // Verificar que la reserva esté pendiente
        if ($reserva->estado !== 'pendiente') {
            return redirect()
                ->route('cliente.reservas.index')
                ->with('error', 'Esta reserva ya no está pendiente de pago');
        }

        $metodosPago = \App\Models\MetodoPago::where('activo', true)->get();

        return view('cliente.pagos.create', [
            'reserva' => $reserva,
            'metodosPago' => $metodosPago,
        ]);
    }

    public function storePago(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'metodo_pago_id' => 'required|exists:metodo_pagos,id',
        ]);

        $usuario = Auth::user();
        $cliente = $usuario->cliente;

        $reserva = Reserva::where('id', $validated['reserva_id'])
            ->where('cliente_id', $cliente->id)
            ->with('habitacion')
            ->firstOrFail();

        if ($reserva->estado !== 'pendiente') {
            return redirect()
                ->route('cliente.reservas.index')
                ->with('error', 'Esta reserva ya no está pendiente de pago');
        }

        // Crear el pago
        $pago = \App\Models\Pago::create([
            'reserva_id' => $reserva->id,
            'metodo_pago_id' => $validated['metodo_pago_id'],
            'monto' => $reserva->precio_total,
            'estado' => 'completado',
            'referencia' => 'PAG-'.strtoupper(uniqid()),
            'fecha_pago' => now(),
        ]);

        // Actualizar estado de la reserva usando el patrón State
        $reserva->update([
            'estado' => 'confirmada',
            'fecha_confirmacion' => now(),
        ]);

        // Cambiar estado de la habitación a 'ocupada' cuando la reserva se confirma
        $reserva->habitacion->update([
            'estado' => 'ocupada',
        ]);

        return redirect()
            ->route('cliente.reservas.index')
            ->with('success', '¡Pago realizado exitosamente! Reserva confirmada.');
    }
}
