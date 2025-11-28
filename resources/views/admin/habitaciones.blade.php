@extends('layouts.admin')

@section('page-title', 'Gestión de Habitaciones')

@section('content')

<!-- BOTÓN CREAR NUEVA HABITACIÓN -->
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('admin.habitaciones.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Crear Nueva Habitación
        </a>
    </div>
</div>

<!-- BÚSQUEDA AVANZADA (Patrón Interpreter) -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="fas fa-search"></i> Búsqueda Avanzada de Habitaciones
            <button class="btn btn-sm btn-light float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosBusqueda">
                <i class="fas fa-filter"></i> Filtros
            </button>
        </h5>
    </div>
    <div class="collapse {{ request()->hasAny(['tipo', 'capacidad', 'precio_min', 'precio_max', 'piso', 'amenidades', 'estado']) ? 'show' : '' }}" id="filtrosBusqueda">
        <div class="card-body">
            <form action="{{ route('admin.habitaciones.index') }}" method="GET">
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
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-users"></i> Capacidad Mínima</label>
                        <select name="capacidad" class="form-select">
                            <option value="">Cualquier capacidad</option>
                            <option value="1" {{ request('capacidad') == 1 ? 'selected' : '' }}>1 persona</option>
                            <option value="2" {{ request('capacidad') == 2 ? 'selected' : '' }}>2 personas</option>
                            <option value="3" {{ request('capacidad') == 3 ? 'selected' : '' }}>3 personas</option>
                            <option value="4" {{ request('capacidad') == 4 ? 'selected' : '' }}>4+ personas</option>
                        </select>
                    </div>

                    <!-- Piso -->
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-building"></i> Piso</label>
                        <input type="number" name="piso" class="form-control" placeholder="Número" value="{{ request('piso') }}" min="1">
                    </div>

                    <!-- Estado -->
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-info-circle"></i> Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="disponible" {{ request('estado') === 'disponible' ? 'selected' : '' }}>Disponible</option>
                            <option value="reservada" {{ request('estado') === 'reservada' ? 'selected' : '' }}>Reservada</option>
                            <option value="ocupada" {{ request('estado') === 'ocupada' ? 'selected' : '' }}>Ocupada</option>
                            <option value="mantenimiento" {{ request('estado') === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                        </select>
                    </div>

                    <!-- Precio Mínimo -->
                    <div class="col-md-1">
                        <label class="form-label"><i class="fas fa-dollar-sign"></i> Precio Mín.</label>
                        <input type="number" name="precio_min" class="form-control" placeholder="$0" value="{{ request('precio_min') }}" min="0" step="0.01">
                    </div>

                    <!-- Precio Máximo -->
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-dollar-sign"></i> Precio Máx.</label>
                        <input type="number" name="precio_max" class="form-control" placeholder="$9999" value="{{ request('precio_max') }}" min="0" step="0.01">
                    </div>

                    <!-- Amenidades -->
                    <div class="col-md-12">
                        <label class="form-label"><i class="fas fa-star"></i> Amenidades</label>
                        <input type="text" name="amenidades" class="form-control" placeholder="WiFi, TV, Aire Acondicionado (separadas por comas)" value="{{ request('amenidades') }}">
                        <small class="form-text text-muted">Buscar por amenidades específicas</small>
                    </div>

                    <!-- Botones -->
                    <div class="col-12">
                        <hr>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <a href="{{ route('admin.habitaciones.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Limpiar Filtros
                            </a>
                            @if(request()->hasAny(['tipo', 'capacidad', 'precio_min', 'precio_max', 'piso', 'amenidades', 'estado']))
                                <div class="alert alert-success mb-0 py-2 px-3">
                                    <i class="fas fa-check-circle"></i> Filtros aplicados - Se encontraron {{ $habitaciones->total() }} habitaciones
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LISTA DE HABITACIONES EN TARJETAS -->
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
                     style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0">Habitación {{ $habitacion->numero }}</h5>
                        @if($habitacion->estado === 'disponible')
                            <span class="badge bg-success">Disponible</span>
                        @elseif($habitacion->estado === 'reservada')
                            <span class="badge bg-warning text-dark">Reservada</span>
                        @elseif($habitacion->estado === 'ocupada')
                            <span class="badge bg-danger">Ocupada</span>
                        @else
                            <span class="badge bg-secondary">Mantenimiento</span>
                        @endif
                    </div>

                    <p class="card-text">
                        <strong>Tipo:</strong> {{ $habitacion->tipoHabitacion->nombre }}<br>
                        <strong>Piso:</strong> {{ $habitacion->piso }}<br>
                        <strong>Capacidad:</strong> {{ $habitacion->capacidad }} personas<br>
                        <strong>Precio:</strong> ${{ number_format($habitacion->precio_base, 2) }}/noche
                    </p>

                    <div class="mb-3">
                        <small class="text-muted"><strong>Amenidades:</strong></small><br>
                        @if($habitacion->amenidades && is_array($habitacion->amenidades))
                            @foreach($habitacion->amenidades as $amenidad)
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $amenidad }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>

                    <div class="d-grid gap-2 mt-auto">
                        <a href="{{ route('habitaciones.show', $habitacion->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> Ver Detalles
                        </a>
                        <a href="{{ route('admin.habitaciones.edit', $habitacion->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="#"
                           onclick="event.preventDefault(); clonarHabitacion({{ $habitacion->id }}, '{{ $habitacion->numero }}', {{ $habitacion->piso }});"
                           class="btn btn-sm btn-info">
                            <i class="fas fa-copy"></i> Clonar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                No hay habitaciones registradas
            </div>
        </div>
    @endforelse
</div>

<!-- Modal para Clonar Habitación -->
<div class="modal fade" id="clonarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="clonarForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-copy"></i> Clonar Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Se clonará la habitación <strong id="habitacionOriginal"></strong> con todas sus características.</p>

                    <div class="mb-3">
                        <label for="nuevo_numero" class="form-label">Nuevo Número de Habitación</label>
                        <input type="text" class="form-control" id="nuevo_numero" name="nuevo_numero" required>
                    </div>

                    <div class="mb-3">
                        <label for="nuevo_piso" class="form-label">Nuevo Piso (opcional)</label>
                        <input type="number" class="form-control" id="nuevo_piso" name="nuevo_piso" placeholder="Dejar vacío para mantener el mismo piso">
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Se copiarán: tipo, capacidad, precio, descripción y amenidades.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-copy"></i> Clonar Habitación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function clonarHabitacion(id, numero, piso) {
    document.getElementById('habitacionOriginal').textContent = numero;
    document.getElementById('nuevo_piso').placeholder = `Dejar vacío para mantener piso ${piso}`;
    document.getElementById('clonarForm').action = `/admin/habitaciones/${id}/clonar`;
    new bootstrap.Modal(document.getElementById('clonarModal')).show();
}
</script>

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

@endsection
