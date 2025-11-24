@extends('layouts.cliente')

@section('page-title', 'Habitaciones Disponibles')

@section('content')

<!-- BÚSQUEDA AVANZADA -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-search"></i> Búsqueda Avanzada
                    <button class="btn btn-sm btn-light float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosBusqueda">
                        <i class="fas fa-filter"></i> Filtros
                    </button>
                </h5>
            </div>
            <div class="collapse {{ request()->hasAny(['tipo', 'capacidad', 'precio_min', 'precio_max', 'piso', 'amenidades']) ? 'show' : '' }}" id="filtrosBusqueda">
                <div class="card-body">
                    <form action="{{ route('cliente.habitaciones.index') }}" method="GET">
                        <div class="row g-3">
                            <!-- Tipo de Habitación -->
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-bed"></i> Tipo de Habitación</label>
                                <select name="tipo" class="form-select">
                                    <option value="">Todos los tipos</option>
                                    @foreach($tiposHabitacion as $tipo)
                                        <option value="{{ $tipo->id }}" {{ request('tipo') == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Capacidad -->
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-users"></i> Capacidad Mínima</label>
                                <select name="capacidad" class="form-select">
                                    <option value="">Cualquier capacidad</option>
                                    <option value="1" {{ request('capacidad') == 1 ? 'selected' : '' }}>1 persona</option>
                                    <option value="2" {{ request('capacidad') == 2 ? 'selected' : '' }}>2 personas</option>
                                    <option value="3" {{ request('capacidad') == 3 ? 'selected' : '' }}>3 personas</option>
                                    <option value="4" {{ request('capacidad') == 4 ? 'selected' : '' }}>4 personas</option>
                                    <option value="5" {{ request('capacidad') == 5 ? 'selected' : '' }}>5+ personas</option>
                                </select>
                            </div>

                            <!-- Piso -->
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-building"></i> Piso</label>
                                <select name="piso" class="form-select">
                                    <option value="">Cualquier piso</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ request('piso') == $i ? 'selected' : '' }}>Piso {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Precio Mínimo -->
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-dollar-sign"></i> Precio Mínimo</label>
                                <input type="number" name="precio_min" class="form-control" placeholder="$0" value="{{ request('precio_min') }}" step="0.01">
                            </div>

                            <!-- Precio Máximo -->
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-dollar-sign"></i> Precio Máximo</label>
                                <input type="number" name="precio_max" class="form-control" placeholder="$1000" value="{{ request('precio_max') }}" step="0.01">
                            </div>

                            <!-- Amenidades -->
                            <div class="col-md-9">
                                <label class="form-label"><i class="fas fa-star"></i> Amenidades</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @php
                                        $amenidadesComunes = ['WiFi', 'TV', 'Aire Acondicionado', 'Minibar', 'Jacuzzi', 'Vista al Mar', 'Balcón'];
                                        $amenidadesSeleccionadas = request('amenidades', []);
                                    @endphp
                                    @foreach($amenidadesComunes as $amenidad)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenidades[]" value="{{ $amenidad }}"
                                                   id="amenidad_{{ str_replace(' ', '_', $amenidad) }}"
                                                   {{ in_array($amenidad, $amenidadesSeleccionadas) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="amenidad_{{ str_replace(' ', '_', $amenidad) }}">
                                                {{ $amenidad }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="col-12">
                                <hr>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <a href="{{ route('cliente.habitaciones.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-redo"></i> Limpiar Filtros
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resultados de Búsqueda -->
@if(request()->hasAny(['tipo', 'capacidad', 'precio_min', 'precio_max', 'piso', 'amenidades']))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <strong>Resultados de búsqueda:</strong> Se encontraron {{ $habitaciones->total() }} habitaciones disponibles que coinciden con tus criterios
            </div>
        </div>
    </div>
@endif

<div class="row">
    @forelse($habitaciones as $habitacion)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                @php
                    $imagenPrincipal = \App\Patterns\Creational\HabitacionImagenFactory::obtenerImagenPrincipal($habitacion);
                @endphp
                <img src="{{ $imagenPrincipal }}"
                     class="card-img-top"
                     alt="Habitación {{ $habitacion->numero }}"
                     style="height: 250px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">Habitación {{ $habitacion->numero }}</h5>
                        @if($habitacion->estado === 'disponible')
                            <span class="badge bg-success">Disponible</span>
                        @elseif($habitacion->estado === 'reservada')
                            <span class="badge bg-warning text-dark">Reservada</span>
                        @elseif($habitacion->estado === 'ocupada')
                            <span class="badge bg-danger">Ocupada</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($habitacion->estado) }}</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <h6 class="text-primary">{{ $habitacion->tipoHabitacion->nombre }}</h6>
                    </div>

                    <p class="card-text">
                        <i class="fas fa-building"></i> <strong>Piso:</strong> {{ $habitacion->piso }}<br>
                        <i class="fas fa-users"></i> <strong>Capacidad:</strong> {{ $habitacion->capacidad }} personas<br>
                        <i class="fas fa-dollar-sign"></i> <strong>Precio:</strong> <span class="text-success fw-bold">${{ number_format($habitacion->precio_base, 2) }}</span>/noche
                    </p>

                    @if($habitacion->descripcion)
                        <p class="card-text small text-muted">
                            {{ Str::limit($habitacion->descripcion, 100) }}
                        </p>
                    @endif

                    <div class="mb-3">
                        <small class="text-muted"><strong>Amenidades:</strong></small><br>
                        @if($habitacion->amenidades && is_array($habitacion->amenidades))
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach(array_slice($habitacion->amenidades, 0, 3) as $amenidad)
                                    <span class="badge bg-light text-dark">{{ $amenidad }}</span>
                                @endforeach
                                @if(count($habitacion->amenidades) > 3)
                                    <span class="badge bg-light text-dark">+{{ count($habitacion->amenidades) - 3 }} más</span>
                                @endif
                            </div>
                        @else
                            <span class="text-muted small">No especificadas</span>
                        @endif
                    </div>

                    <div class="d-grid gap-2 mt-auto">
                        <a href="{{ route('habitaciones.show', $habitacion->id) }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Detalles Completos
                        </a>
                        @if($habitacion->estado === 'disponible')
                            <a href="{{ route('cliente.reservas.create', ['habitacion_id' => $habitacion->id]) }}" class="btn btn-success">
                                <i class="fas fa-calendar-plus"></i> Reservar Ahora
                            </a>
                        @else
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-ban"></i> No Disponible
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle"></i> No hay habitaciones disponibles en este momento
            </div>
        </div>
    @endforelse
</div>

<!-- PAGINACIÓN PERSONALIZADA -->
@if($habitaciones->hasPages())
    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Botones de navegación -->
            <div>
                @if($habitaciones->onFirstPage())
                    <span class="text-muted">« Volver</span>
                @else
                    <a href="{{ $habitaciones->previousPageUrl() }}" class="text-decoration-none">« Volver</a>
                @endif

                <span class="mx-3">|</span>

                @if($habitaciones->hasMorePages())
                    <a href="{{ $habitaciones->nextPageUrl() }}" class="text-decoration-none">Siguiente »</a>
                @else
                    <span class="text-muted">Siguiente »</span>
                @endif
            </div>

            <!-- Información de resultados -->
            <div class="text-muted small">
                Mostrando {{ $habitaciones->firstItem() ?? 0 }} a {{ $habitaciones->lastItem() ?? 0 }} de {{ $habitaciones->total() }} resultados
            </div>
        </div>
    </div>
@endif

<!-- INFORMACIÓN ADICIONAL -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información Importante</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6><i class="fas fa-clock text-primary"></i> Horarios</h6>
                        <p class="small mb-2">
                            <strong>Entrada:</strong> 15:00 hrs<br>
                            <strong>Salida:</strong> 12:00 hrs
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-credit-card text-success"></i> Pagos</h6>
                        <p class="small mb-2">
                            Aceptamos tarjetas de crédito/débito, transferencias y efectivo.
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-ban text-danger"></i> Políticas</h6>
                        <p class="small mb-2">
                            Cancelación gratuita hasta 48 hrs antes de la fecha de entrada.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
