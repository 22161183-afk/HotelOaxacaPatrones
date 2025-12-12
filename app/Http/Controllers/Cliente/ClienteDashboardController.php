<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
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
            ->orderByRaw('CAST(numero AS UNSIGNED)')
            ->take(8)
            ->get();

        // Estadísticas del cliente
        $totalReservas = Reserva::where('cliente_id', $cliente->id)
            ->whereIn('estado', ['confirmada', 'completada'])
            ->count();
        $totalGastado = Reserva::where('cliente_id', $cliente->id)
            ->whereIn('estado', ['confirmada', 'completada'])
            ->sum('precio_total');

        // Calcular total reembolsado (solo cuando el cliente ya aceptó el reembolso)
        $totalReembolsado = Reserva::where('cliente_id', $cliente->id)
            ->where('estado', 'reembolsado')
            ->sum('monto_reembolso');

        return response()
            ->view('cliente.dashboard', [
                'cliente' => $cliente,
                'reservasActivas' => $reservasActivas,
                'reservasPasadas' => $reservasPasadas,
                'reservasCanceladas' => $reservasCanceladas,
                'habitacionesDisponibles' => $habitacionesDisponibles,
                'totalReservas' => $totalReservas,
                'totalGastado' => $totalGastado,
                'totalReembolsado' => $totalReembolsado,
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
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
                ->orderByRaw('CAST(numero AS UNSIGNED)');
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
            'fecha_inicio' => 'required|date|after_or_equal:'.now()->toDateString(),
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

        // Refrescar y cargar relaciones necesarias
        $reserva->refresh();
        $reserva->load(['habitacion', 'cliente', 'servicios']);

        // NOTA: El precio ya fue calculado por el Builder con impuestos incluidos
        // No se aplica estrategia de descuento automáticamente para evitar
        // sobrescribir el cálculo correcto del Builder
        \Log::info("Precio total guardado: {$reserva->precio_total} para reserva #{$reserva->id}");

        return redirect()
            ->route('cliente.reservas.index')
            ->with('success', '¡Reserva creada exitosamente! Total: $'.number_format($reserva->precio_total, 2))
            ->with('email_notification', [
                'title' => 'Confirmación de Reserva Enviada',
                'message' => 'Se ha enviado un correo de confirmación con los detalles de tu reserva.',
                'recipient' => $cliente->nombre.' '.$cliente->apellido,
                'email' => $cliente->email,
            ]);
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
            'fecha_inicio' => 'required|date|after_or_equal:'.now()->toDateString(),
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
            ->with('success', 'Reserva actualizada exitosamente. Nuevo total: $'.number_format($reserva->precio_total, 2))
            ->with('email_notification', [
                'title' => 'Modificación de Reserva Confirmada',
                'message' => 'Se ha enviado un correo confirmando los cambios en tu reserva.',
                'recipient' => $cliente->nombre.' '.$cliente->apellido,
                'email' => $cliente->email,
            ]);
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

        // Marcar pagos asociados como reembolsados
        $reserva->pagos()
            ->where('estado', 'completado')
            ->update([
                'estado' => 'reembolsado',
                'observaciones' => 'Reembolsado por cancelación de reserva',
            ]);

        return redirect()
            ->route('cliente.reservas.index')
            ->with('success', 'Reserva cancelada exitosamente')
            ->with('email_notification', [
                'title' => 'Cancelación de Reserva Confirmada',
                'message' => 'Se ha enviado un correo confirmando la cancelación de tu reserva.',
                'recipient' => $cliente->nombre.' '.$cliente->apellido,
                'email' => $cliente->email,
            ]);
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

        // Verificar si es pago de diferencia
        $esDiferencia = $request->query('diferencia') === 'true';

        if ($esDiferencia) {
            // Validar que la reserva esté confirmada y tenga diferencia pendiente
            if ($reserva->estado !== 'confirmada' || $reserva->tipo_diferencia !== 'pagar' || ! $reserva->monto_diferencia || $reserva->fecha_diferencia_pagada) {
                return redirect()
                    ->route('cliente.reservas.index')
                    ->with('error', 'No hay diferencia pendiente de pago para esta reserva');
            }
        } else {
            // Verificar que la reserva esté pendiente
            if ($reserva->estado !== 'pendiente') {
                return redirect()
                    ->route('cliente.reservas.index')
                    ->with('error', 'Esta reserva ya no está pendiente de pago');
            }
        }

        $metodosPago = \App\Models\MetodoPago::where('activo', true)->get();

        return view('cliente.pagos.create', [
            'reserva' => $reserva,
            'metodosPago' => $metodosPago,
            'esDiferencia' => $esDiferencia,
        ]);
    }

    public function storePago(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'metodo_pago_id' => 'required|exists:metodo_pagos,id',
            'es_diferencia' => 'nullable|boolean',
        ]);

        $usuario = Auth::user();
        $cliente = $usuario->cliente;

        $reserva = Reserva::where('id', $validated['reserva_id'])
            ->where('cliente_id', $cliente->id)
            ->with('habitacion')
            ->firstOrFail();

        $esDiferencia = $validated['es_diferencia'] ?? false;

        if ($esDiferencia) {
            // Validar que sea pago de diferencia
            if ($reserva->estado !== 'confirmada' || $reserva->tipo_diferencia !== 'pagar' || ! $reserva->monto_diferencia || $reserva->fecha_diferencia_pagada) {
                return redirect()
                    ->route('cliente.reservas.index')
                    ->with('error', 'No hay diferencia pendiente de pago para esta reserva');
            }

            // Crear el pago de la diferencia EN PROCESO (para que el admin lo apruebe)
            $pago = \App\Models\Pago::create([
                'reserva_id' => $reserva->id,
                'metodo_pago_id' => $validated['metodo_pago_id'],
                'monto' => $reserva->monto_diferencia,
                'estado' => 'en_proceso',
                'referencia' => 'PAG-DIF-'.strtoupper(uniqid()),
                'fecha_pago' => now(),
                'observaciones' => 'Pago de diferencia por cambio de habitación - Pendiente de verificación',
            ]);

            return redirect()
                ->route('cliente.reservas.index')
                ->with('success', '¡Pago de diferencia enviado! El pago de $'.number_format($reserva->monto_diferencia, 2).' está en proceso de verificación.')
                ->with('email_notification', [
                    'title' => 'Pago de Diferencia Recibido',
                    'message' => 'Se ha recibido tu pago de diferencia por $'.number_format($reserva->monto_diferencia, 2).'. Está siendo procesado y verificado.',
                    'recipient' => $cliente->nombre.' '.$cliente->apellido,
                    'email' => $cliente->email,
                ]);
        } else {
            // Pago normal de reserva
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
                ->with('success', '¡Pago realizado exitosamente! Reserva confirmada.')
                ->with('email_notification', [
                    'title' => 'Pago Recibido y Confirmado',
                    'message' => 'Se ha enviado un comprobante de pago y confirmación de tu reserva.',
                    'recipient' => $cliente->nombre.' '.$cliente->apellido,
                    'email' => $cliente->email,
                ]);
        }
    }

    /**
     * Aceptar reembolso
     */
    public function aceptarReembolso($id)
    {
        $cliente = Cliente::where('usuario_id', auth()->id())->firstOrFail();
        $reserva = Reserva::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->where('estado', 'en_proceso_reembolso')
            ->firstOrFail();

        // Marcar como reembolsado
        $reserva->update([
            'estado' => 'reembolsado',
            'fecha_reembolso' => now(),
        ]);

        // Marcar todos los pagos completados como reembolsados
        $reserva->pagos()
            ->where('estado', 'completado')
            ->update([
                'estado' => 'reembolsado',
                'observaciones' => 'Reembolsado - Cliente aceptó el reembolso el '.now()->format('d/m/Y H:i'),
            ]);

        return redirect()
            ->route('cliente.reservas.index')
            ->with('success', 'Reembolso aceptado. El monto de $'.number_format($reserva->monto_reembolso, 2).' será procesado en los próximos días.')
            ->with('email_notification', [
                'title' => 'Reembolso Aceptado',
                'message' => 'Has aceptado el reembolso de $'.number_format($reserva->monto_reembolso, 2).'. El dinero será procesado en 3-5 días hábiles.',
                'recipient' => $cliente->nombre.' '.$cliente->apellido,
                'email' => $cliente->email,
            ]);
    }

    /**
     * Aceptar reembolso de diferencia cuando se cambió a una habitación más barata
     */
    public function aceptarReembolsoDiferencia($id)
    {
        $cliente = auth()->user()->cliente;
        $reserva = Reserva::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        // Validar que tenga diferencia por reembolsar y no esté ya procesada
        if ($reserva->tipo_diferencia !== 'reembolsar' || $reserva->fecha_diferencia_pagada || ! $reserva->monto_diferencia) {
            return redirect()
                ->route('cliente.reservas.index')
                ->with('error', 'No se puede aceptar este reembolso. Verifique el estado de la diferencia.');
        }

        // Marcar la diferencia como procesada
        $reserva->update([
            'fecha_diferencia_pagada' => now(),
        ]);

        return redirect()
            ->route('cliente.reservas.index')
            ->with('success', 'Reembolso de diferencia aceptado. El monto de $'.number_format($reserva->monto_diferencia, 2).' será procesado en los próximos 3-5 días hábiles.')
            ->with('email_notification', [
                'title' => 'Reembolso de Diferencia Aceptado',
                'message' => 'Has aceptado el reembolso de diferencia de $'.number_format($reserva->monto_diferencia, 2).' por cambio de habitación. El dinero será procesado en 3-5 días hábiles.',
                'recipient' => $cliente->nombre.' '.$cliente->apellido,
                'email' => $cliente->email,
            ]);
    }

    /**
     * Actualizar perfil del cliente
     */
    public function updatePerfil(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:usuarios,email,'.auth()->id(),
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser válido',
            'email.unique' => 'Este email ya está en uso',
        ]);

        $usuario = auth()->user();
        $usuario->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Perfil actualizado correctamente');
    }
}
