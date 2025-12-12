<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Reserva;
use App\Patterns\Behavioral\CambiarHabitacionCommand;
use App\Patterns\Behavioral\CancelarReservaCommand;
use App\Patterns\Behavioral\ConfirmarReservaCommand;
use App\Patterns\Behavioral\HabitacionSearchInterpreter;
use App\Patterns\Behavioral\ReservaCommandInvoker;
use App\Patterns\Behavioral\ReservaSearchInterpreter;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = today();

        // ESTADÍSTICAS PRINCIPALES
        $reservasHoy = Reserva::whereDate('fecha_inicio', $today)
            ->whereIn('estado', ['confirmada', 'completada'])
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
            ->whereHas('reserva', function ($query) {
                $query->whereIn('estado', ['confirmada', 'completada']);
            })
            ->sum('monto');

        $ingresosTotal = Pago::where('estado', 'completado')
            ->whereHas('reserva', function ($query) {
                $query->whereIn('estado', ['confirmada', 'completada']);
            })
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
            ->whereIn('estado', ['confirmada', 'completada'])
            ->with('cliente', 'habitacion')
            ->orderBy('fecha_inicio')
            ->get();

        // HABITACIONES CON RESERVAS PRÓXIMAS
        $habitacionesProximas = Habitacion::with('reservas')
            ->whereHas('reservas', function ($query) use ($today, $fechaFin) {
                $query->where('fecha_inicio', '>=', $today)
                    ->where('fecha_inicio', '<=', $fechaFin)
                    ->whereIn('estado', ['confirmada', 'completada']);
            })
            ->get();

        // CLIENTES TOP (últimos 12 meses)
        // FIX: Filtrar por reservas recientes para reflejar clientes activos
        $hace12Meses = now()->subMonths(12);
        $clientesTop = Cliente::withCount(['reservas' => function ($query) use ($hace12Meses) {
            $query->whereIn('estado', ['confirmada', 'completada'])
                ->where('created_at', '>=', $hace12Meses);
        }])
            ->having('reservas_count', '>', 0)
            ->orderBy('reservas_count', 'desc')
            ->take(5)
            ->get();

        // SERVICIOS MÁS VENDIDOS (últimos 6 meses)
        // FIX: Filtrar por ventas recientes para reflejar tendencias actuales
        $hace6Meses = now()->subMonths(6);
        $serviciosMasVendidos = DB::table('reserva_servicio')
            ->join('servicios', 'reserva_servicio.servicio_id', '=', 'servicios.id')
            ->join('reservas', 'reserva_servicio.reserva_id', '=', 'reservas.id')
            ->whereIn('reservas.estado', ['confirmada', 'completada'])
            ->where('reservas.created_at', '>=', $hace6Meses)
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
        // FIX: Guardar now() fuera del loop para evitar inconsistencias
        $fechaBase = now();
        $ingresosUltimaSemana = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = $fechaBase->copy()->subDays($i)->format('Y-m-d');
            $ingreso = Pago::whereDate('created_at', $fecha)
                ->where('estado', 'completado')
                ->whereHas('reserva', function ($query) {
                    $query->whereIn('estado', ['confirmada', 'completada']);
                })
                ->sum('monto');
            $ingresosUltimaSemana[$fecha] = $ingreso;
        }

        return response()
            ->view('admin.dashboard', [
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
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function reservas(\Illuminate\Http\Request $request)
    {
        $query = Reserva::with('cliente', 'habitacion', 'servicios', 'pagos');

        // Usar Interpreter Pattern si hay parámetros de búsqueda
        if ($request->hasAny(['estado', 'cliente', 'habitacion', 'fecha_inicio', 'fecha_fin', 'precio_min', 'precio_max'])) {
            $interpreter = ReservaSearchInterpreter::fromRequest($request->all());
            $query = $interpreter->interpret($query);
        }

        $reservas = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.reservas', [
            'reservas' => $reservas,
            'filtros' => $request->all(),
        ]);
    }

    public function habitaciones(\Illuminate\Http\Request $request)
    {
        $query = Habitacion::with('tipoHabitacion', 'reservas');

        // Usar Interpreter Pattern si hay parámetros de búsqueda
        if ($request->hasAny(['tipo', 'capacidad', 'precio_min', 'precio_max', 'piso', 'amenidades', 'estado'])) {
            $interpreter = HabitacionSearchInterpreter::fromRequest($request->all());
            $query = $interpreter->interpret($query);
        }

        // Ordenar por número de habitación (numéricamente)
        $habitaciones = $query->orderByRaw('CAST(numero AS UNSIGNED)')->paginate(15);

        $tiposHabitacion = \App\Models\TipoHabitacion::all();

        return view('admin.habitaciones', [
            'habitaciones' => $habitaciones,
            'tiposHabitacion' => $tiposHabitacion,
            'filtros' => $request->all(),
        ]);
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
            'numero' => [
                'required',
                'string',
                'min:1',
                'max:10',
                'unique:habitacions,numero,'.$id,
            ],
            'tipo_habitacion_id' => 'required|exists:tipo_habitacions,id',
            'piso' => 'required|integer|min:1|max:50',
            'capacidad' => 'required|integer|min:1|max:20',
            'precio_base' => 'required|numeric|min:1|max:999999',
            'estado' => 'required|in:disponible,reservada,ocupada,mantenimiento',
            'descripcion' => 'nullable|string|max:1000',
            'amenidades' => 'nullable|string|max:500',
            'imagen_url' => 'nullable|url|max:500',
        ], [
            'numero.required' => 'El número de habitación es obligatorio',
            'numero.unique' => 'Ya existe otra habitación con este número',
            'numero.min' => 'El número debe tener al menos 1 caracter',
            'numero.max' => 'El número no puede exceder 10 caracteres',
            'tipo_habitacion_id.required' => 'Debe seleccionar un tipo de habitación',
            'tipo_habitacion_id.exists' => 'El tipo de habitación seleccionado no existe',
            'piso.required' => 'El piso es obligatorio',
            'piso.integer' => 'El piso debe ser un número entero',
            'piso.min' => 'El piso debe ser mayor a 0',
            'piso.max' => 'El piso no puede ser mayor a 50',
            'capacidad.required' => 'La capacidad es obligatoria',
            'capacidad.min' => 'La capacidad debe ser al menos 1 persona',
            'capacidad.max' => 'La capacidad no puede exceder 20 personas',
            'precio_base.required' => 'El precio base es obligatorio',
            'precio_base.min' => 'El precio debe ser mayor a 0',
            'precio_base.max' => 'El precio no puede exceder $999,999',
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado seleccionado no es válido',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres',
            'amenidades.max' => 'Las amenidades no pueden exceder 500 caracteres',
            'imagen_url.url' => 'La URL de la imagen no es válida',
            'imagen_url.max' => 'La URL no puede exceder 500 caracteres',
        ]);

        try {
            // Convertir amenidades de string a array
            if (isset($validated['amenidades']) && is_string($validated['amenidades'])) {
                $amenidades = array_filter(array_map('trim', explode(',', $validated['amenidades'])));

                if (count($amenidades) > 20) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'No puede agregar más de 20 amenidades');
                }

                $validated['amenidades'] = $amenidades;
            }

            $numeroAnterior = $habitacion->numero;
            $habitacion->update($validated);

            // Refrescar el modelo para obtener los datos actualizados
            $habitacion->refresh();

            $tipoHabitacion = \App\Models\TipoHabitacion::find($validated['tipo_habitacion_id']);

            return redirect()
                ->route('admin.habitaciones.index')
                ->with('success', "Habitación #{$numeroAnterior} actualizada correctamente a #{$habitacion->numero}. Tipo: {$tipoHabitacion->nombre}, Piso: {$habitacion->piso}, Capacidad: {$habitacion->capacidad} personas, Precio: $".number_format($habitacion->precio_base, 2));
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Ya existe otra habitación con ese número. Por favor, elija otro.');
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error de base de datos al actualizar la habitación. Por favor, intente nuevamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar la habitación: '.$e->getMessage());
        }
    }

    public function createHabitacion()
    {
        $tiposHabitacion = \App\Models\TipoHabitacion::all();

        return view('admin.habitaciones.create', [
            'tiposHabitacion' => $tiposHabitacion,
        ]);
    }

    public function storeHabitacion(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'numero' => [
                'required',
                'string',
                'min:1',
                'max:10',
                'unique:habitacions,numero',
            ],
            'tipo_habitacion_id' => 'required|exists:tipo_habitacions,id',
            'piso' => 'required|integer|min:1|max:50',
            'capacidad' => 'required|integer|min:1|max:20',
            'precio_base' => 'required|numeric|min:1|max:999999',
            'estado' => 'required|in:disponible,reservada,ocupada,mantenimiento',
            'descripcion' => 'nullable|string|max:1000',
            'amenidades' => 'nullable|string|max:500',
            'imagen_url' => 'nullable|url|max:500',
        ], [
            'numero.required' => 'El número de habitación es obligatorio',
            'numero.unique' => 'Ya existe una habitación con este número',
            'numero.min' => 'El número debe tener al menos 1 caracter',
            'numero.max' => 'El número no puede exceder 10 caracteres',
            'tipo_habitacion_id.required' => 'Debe seleccionar un tipo de habitación',
            'tipo_habitacion_id.exists' => 'El tipo de habitación seleccionado no existe',
            'piso.required' => 'El piso es obligatorio',
            'piso.integer' => 'El piso debe ser un número entero',
            'piso.min' => 'El piso debe ser mayor a 0',
            'piso.max' => 'El piso no puede ser mayor a 50',
            'capacidad.required' => 'La capacidad es obligatoria',
            'capacidad.min' => 'La capacidad debe ser al menos 1 persona',
            'capacidad.max' => 'La capacidad no puede exceder 20 personas',
            'precio_base.required' => 'El precio base es obligatorio',
            'precio_base.min' => 'El precio debe ser mayor a 0',
            'precio_base.max' => 'El precio no puede exceder $999,999',
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado seleccionado no es válido',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres',
            'amenidades.max' => 'Las amenidades no pueden exceder 500 caracteres',
            'imagen_url.url' => 'La URL de la imagen no es válida',
            'imagen_url.max' => 'La URL no puede exceder 500 caracteres',
        ]);

        try {
            // Convertir amenidades de string a array
            if (isset($validated['amenidades'])) {
                $amenidades = array_filter(array_map('trim', explode(',', $validated['amenidades'])));

                if (count($amenidades) > 20) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'No puede agregar más de 20 amenidades');
                }

                $validated['amenidades'] = $amenidades;
            }

            $habitacion = Habitacion::create($validated);

            $tipoHabitacion = \App\Models\TipoHabitacion::find($validated['tipo_habitacion_id']);

            return redirect()
                ->route('admin.habitaciones.index')
                ->with('success', "Habitación #{$habitacion->numero} creada exitosamente. Tipo: {$tipoHabitacion->nombre}, Piso: {$habitacion->piso}, Capacidad: {$habitacion->capacidad} personas, Precio: $".number_format($habitacion->precio_base, 2));
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Ya existe una habitación con ese número. Por favor, elija otro.');
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error de base de datos al crear la habitación. Por favor, intente nuevamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear la habitación: '.$e->getMessage());
        }
    }

    public function showReserva($id)
    {
        $reserva = Reserva::with('cliente', 'habitacion.tipoHabitacion', 'servicios', 'pagos.metodoPago')
            ->findOrFail($id);

        return view('admin.reservas.show', [
            'reserva' => $reserva,
        ]);
    }

    public function metodosPago()
    {
        $metodosPago = \App\Models\MetodoPago::orderBy('nombre')->paginate(15);

        return view('admin.metodos-pago.index', [
            'metodosPago' => $metodosPago,
        ]);
    }

    public function createMetodoPago()
    {
        return view('admin.metodos-pago.create');
    }

    public function storeMetodoPago(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:tarjeta_credito,tarjeta_debito,transferencia,efectivo',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->has('activo');

        \App\Models\MetodoPago::create($validated);

        return redirect()
            ->route('admin.metodos-pago.index')
            ->with('success', 'Método de pago creado correctamente');
    }

    public function editMetodoPago($id)
    {
        $metodoPago = \App\Models\MetodoPago::findOrFail($id);

        return view('admin.metodos-pago.edit', [
            'metodoPago' => $metodoPago,
        ]);
    }

    public function updateMetodoPago(\Illuminate\Http\Request $request, $id)
    {
        $metodoPago = \App\Models\MetodoPago::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:tarjeta_credito,tarjeta_debito,transferencia,efectivo',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->has('activo');

        $metodoPago->update($validated);

        return redirect()
            ->route('admin.metodos-pago.index')
            ->with('success', 'Método de pago actualizado correctamente');
    }

    public function apiDocumentation()
    {
        return view('admin.api-docs');
    }

    /**
     * Clonar habitación usando Prototype Pattern
     */
    public function clonarHabitacion(\Illuminate\Http\Request $request, $id)
    {
        $habitacionOriginal = Habitacion::with('tipoHabitacion')->findOrFail($id);

        // Validar datos de entrada
        $validated = $request->validate([
            'nuevo_numero' => [
                'required',
                'string',
                'min:1',
                'max:10',
                'unique:habitacions,numero',
            ],
            'nuevo_piso' => 'nullable|integer|min:1|max:50',
        ], [
            'nuevo_numero.required' => 'El número de la nueva habitación es obligatorio',
            'nuevo_numero.unique' => 'Ya existe una habitación con el número ingresado',
            'nuevo_numero.min' => 'El número de habitación debe tener al menos 1 caracter',
            'nuevo_numero.max' => 'El número de habitación no puede tener más de 10 caracteres',
            'nuevo_piso.integer' => 'El piso debe ser un número entero',
            'nuevo_piso.min' => 'El piso debe ser mayor a 0',
            'nuevo_piso.max' => 'El piso no puede ser mayor a 50',
        ]);

        // Verificar que la habitación original no esté en mantenimiento
        if ($habitacionOriginal->estado === 'mantenimiento') {
            return redirect()
                ->back()
                ->with('warning', "No se puede clonar la habitación {$habitacionOriginal->numero} porque está en mantenimiento. Por favor, cambie su estado primero.");
        }

        // Verificar que el número nuevo sea diferente del original
        if ($validated['nuevo_numero'] === $habitacionOriginal->numero) {
            return redirect()
                ->back()
                ->with('error', 'El número de la nueva habitación debe ser diferente al original');
        }

        try {
            // Usar el patrón Prototype para clonar
            $prototype = new \App\Patterns\Creational\HabitacionPrototype($habitacionOriginal);

            if (isset($validated['nuevo_piso'])) {
                // Verificar que el piso sea diferente si se especificó
                if ($validated['nuevo_piso'] === $habitacionOriginal->piso) {
                    return redirect()
                        ->back()
                        ->with('info', "La habitación se clonará en el mismo piso ({$habitacionOriginal->piso}). Si desea clonarla en otro piso, especifique un piso diferente.");
                }

                // Clonar con modificaciones
                $nuevaHabitacion = $prototype->clonarConModificaciones($validated['nuevo_numero'], [
                    'piso' => $validated['nuevo_piso'],
                ]);

                return redirect()
                    ->route('admin.habitaciones.index')
                    ->with('success', "Habitación {$habitacionOriginal->numero} clonada exitosamente como #{$nuevaHabitacion->numero} en el piso {$nuevaHabitacion->piso}. Se copiaron todas las características: tipo, capacidad, precio y amenidades.");
            } else {
                // Clonar simple
                $nuevaHabitacion = $prototype->clonar($validated['nuevo_numero']);

                return redirect()
                    ->route('admin.habitaciones.index')
                    ->with('success', "Habitación {$habitacionOriginal->numero} clonada exitosamente como #{$nuevaHabitacion->numero} en el piso {$nuevaHabitacion->piso}. Se copiaron todas las características: tipo, capacidad, precio y amenidades.");
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Error de base de datos
            if ($e->errorInfo[1] === 1062) {
                return redirect()
                    ->back()
                    ->with('error', 'El número de habitación ya existe en el sistema. Por favor, elija otro número.');
            }

            return redirect()
                ->back()
                ->with('error', 'Error de base de datos al clonar la habitación. Por favor, intente nuevamente.');
        } catch (\Exception $e) {
            // Error general
            return redirect()
                ->back()
                ->with('error', 'Error al clonar habitación: '.$e->getMessage());
        }
    }

    /**
     * Mostrar formulario para clonar habitación
     */
    public function showClonarForm($id)
    {
        $habitacion = Habitacion::with('tipoHabitacion')->findOrFail($id);

        return view('admin.habitaciones.clonar', [
            'habitacion' => $habitacion,
        ]);
    }

    /**
     * Confirmar reserva
     */
    public function confirmarReservaCommand($id)
    {
        $reserva = Reserva::with('habitacion', 'cliente')->findOrFail($id);

        // ============================================================
        // COMMAND PATTERN - Operación de confirmar reserva
        // ============================================================
        $comando = new ConfirmarReservaCommand($reserva);
        $invoker = new ReservaCommandInvoker;

        if ($invoker->ejecutar($comando)) {
            return redirect()
                ->back()
                ->with('success', 'Reserva confirmada exitosamente')
                ->with('email_notification', [
                    'title' => 'Cliente Notificado - Reserva Confirmada',
                    'message' => 'Se ha enviado una notificación al cliente confirmando su reserva.',
                    'recipient' => $reserva->cliente->nombre.' '.$reserva->cliente->apellido,
                    'email' => $reserva->cliente->email,
                ]);
        }

        return redirect()
            ->back()
            ->with('error', 'No se pudo confirmar la reserva');
    }

    /**
     * Cancelar reserva
     */
    public function cancelarReservaCommand(\Illuminate\Http\Request $request, $id)
    {
        $reserva = Reserva::with('habitacion', 'cliente', 'pagos')->findOrFail($id);

        $validated = $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        // Verificar si la reserva tiene pagos completados
        $totalPagado = $reserva->pagos()
            ->where('estado', 'completado')
            ->sum('monto');

        // Si tiene pagos, marcar para reembolso
        if ($totalPagado > 0) {
            $reserva->update([
                'estado' => 'en_proceso_reembolso',
                'fecha_cancelacion' => now(),
                'fecha_solicitud_reembolso' => now(),
                'monto_reembolso' => $totalPagado,
                'motivo_reembolso' => $validated['motivo'] ?? 'Cancelada por administrador',
                'observaciones' => ($reserva->observaciones ?? '')."\n".now()->format('Y-m-d H:i:s').' - Cancelada por administrador. Reembolso iniciado: $'.number_format($totalPagado, 2),
            ]);

            // Liberar habitación
            $reserva->habitacion->update(['estado' => 'disponible']);

            return redirect()
                ->back()
                ->with('success', 'Reserva cancelada. Se ha iniciado el proceso de reembolso por $'.number_format($totalPagado, 2))
                ->with('email_notification', [
                    'title' => 'Cliente Notificado - Reembolso en Proceso',
                    'message' => 'Se ha iniciado el proceso de reembolso por $'.number_format($totalPagado, 2).'. El cliente debe aceptar el reembolso desde su panel.',
                    'recipient' => $reserva->cliente->nombre.' '.$reserva->cliente->apellido,
                    'email' => $reserva->cliente->email,
                ]);
        }

        // Si no tiene pagos, cancelación normal
        // ============================================================
        // COMMAND PATTERN - Operación de cancelar reserva
        // ============================================================
        $comando = new CancelarReservaCommand(
            $reserva,
            $validated['motivo'] ?? 'Cancelada por administrador'
        );
        $invoker = new ReservaCommandInvoker;

        if ($invoker->ejecutar($comando)) {
            return redirect()
                ->back()
                ->with('success', 'Reserva cancelada exitosamente')
                ->with('email_notification', [
                    'title' => 'Cliente Notificado - Reserva Cancelada',
                    'message' => 'Se ha enviado una notificación al cliente sobre la cancelación de su reserva.',
                    'recipient' => $reserva->cliente->nombre.' '.$reserva->cliente->apellido,
                    'email' => $reserva->cliente->email,
                ]);
        }

        return redirect()
            ->back()
            ->with('error', 'No se pudo cancelar la reserva');
    }

    /**
     * Cambiar habitación de una reserva
     */
    public function cambiarHabitacionCommand(\Illuminate\Http\Request $request, $id)
    {
        $reserva = Reserva::with('habitacion', 'cliente')->findOrFail($id);

        $validated = $request->validate([
            'nueva_habitacion_id' => 'required|exists:habitacions,id',
        ]);

        $nuevaHabitacion = Habitacion::findOrFail($validated['nueva_habitacion_id']);

        // ============================================================
        // COMMAND PATTERN - Operación de cambiar habitación
        // ============================================================
        $comando = new CambiarHabitacionCommand(
            $reserva,
            $nuevaHabitacion->id
        );
        $invoker = new ReservaCommandInvoker;

        if ($invoker->ejecutar($comando)) {
            return redirect()
                ->back()
                ->with('success', "Habitación cambiada exitosamente a {$nuevaHabitacion->numero}")
                ->with('email_notification', [
                    'title' => 'Cliente Notificado - Cambio de Habitación',
                    'message' => "Se ha enviado una notificación al cliente sobre el cambio de habitación a la #{$nuevaHabitacion->numero}.",
                    'recipient' => $reserva->cliente->nombre.' '.$reserva->cliente->apellido,
                    'email' => $reserva->cliente->email,
                ]);
        }

        return redirect()
            ->back()
            ->with('error', 'No se pudo cambiar la habitación');
    }

    /**
     * Aprobar pago de diferencia (cuando el cliente ya pagó)
     */
    public function aprobarPagoDiferencia($id)
    {
        $pago = Pago::with('reserva')->findOrFail($id);

        // Validar que sea un pago de diferencia en proceso
        if ($pago->estado !== 'en_proceso' || strpos($pago->observaciones, 'Pago de diferencia') === false) {
            return redirect()->back()->with('error', 'Este pago no es una diferencia en proceso.');
        }

        $reserva = $pago->reserva;

        // Actualizar estado del pago a completado
        $pago->update([
            'estado' => 'completado',
            'observaciones' => 'Pago de diferencia por cambio de habitación - Aprobado',
        ]);

        // Marcar la diferencia como pagada en la reserva
        $reserva->update([
            'fecha_diferencia_pagada' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pago de diferencia aprobado: $'.number_format($pago->monto, 2));
    }

    /**
     * Cancelar gestión de diferencia de precio
     */
    public function cancelarDiferencia($id)
    {
        $reserva = Reserva::findOrFail($id);

        // Validar que tenga una diferencia pendiente
        if (! $reserva->monto_diferencia) {
            return redirect()->back()->with('error', 'No hay diferencia de precio para cancelar.');
        }

        // Limpiar campos de diferencia
        $reserva->update([
            'monto_diferencia' => null,
            'tipo_diferencia' => null,
            'fecha_diferencia_pagada' => null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Gestión de diferencia de precio cancelada');
    }

    /**
     * Actualizar perfil del administrador
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

        return redirect()->back()->with('success', 'Perfil actualizado correctamente');
    }
}
