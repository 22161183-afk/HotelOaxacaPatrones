@extends('layouts.app')

@section('page-title', 'Detalles Habitación ' . $habitacion->numero)

@section('content')

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-door-open"></i> Habitación {{ $habitacion->numero }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información General</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Tipo:</strong></td>
                                    <td>{{ $habitacion->tipoHabitacion->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Piso:</strong></td>
                                    <td>{{ $habitacion->piso }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Capacidad:</strong></td>
                                    <td>{{ $habitacion->capacidad }} personas</td>
                                </tr>
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                    <span class="badge bg-{{ $habitacion->estado === 'disponible' ? 'success' : ($habitacion->estado === 'ocupada' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($habitacion->estado) }}
                                    </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Precio Base:</strong></td>
                                    <td class="text-primary" style="font-size: 18px; font-weight: bold;">
                                        ${{ number_format($habitacion->precio_base, 2) }}/noche
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Amenidades</h5>
                            <div>
                                @forelse($habitacion->amenidades ?? [] as $amenidad)
                                    <span class="badge bg-info me-2 mb-2">
                                    <i class="fas fa-check"></i> {{ $amenidad }}
                                </span>
                                @empty
                                    <span class="text-muted">Sin amenidades especiales</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5>Descripción</h5>
                    <p>{{ $habitacion->descripcion ?? 'Sin descripción' }}</p>

                    <hr>

                    <h5>Disponibilidad</h5>
                    @if($habitacion->estado === 'disponible')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Esta habitación está disponible
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> Esta habitación NO está disponible
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.habitaciones.edit', $habitacion->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Habitación
                            </a>
                        @elseif(auth()->user()->isCliente() && $habitacion->estado === 'disponible')
                            <a href="{{ route('cliente.reservas.create', ['habitacion_id' => $habitacion->id]) }}" class="btn btn-success">
                                <i class="fas fa-calendar-plus"></i> Reservar Ahora
                            </a>
                        @endif
                    @else
                        @if($habitacion->estado === 'disponible')
                            <a href="{{ route('login') }}" class="btn btn-success">
                                <i class="fas fa-sign-in-alt"></i> Inicia sesión para reservar
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Resumen</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tipo:</strong></p>
                    <p class="text-muted">{{ $habitacion->tipoHabitacion->nombre }}</p>

                    <p><strong>Número:</strong></p>
                    <p class="text-muted">{{ $habitacion->numero }}</p>

                    <p><strong>Capacidad:</strong></p>
                    <p class="text-muted">{{ $habitacion->capacidad }} personas</p>

                    <p><strong>Precio/Noche:</strong></p>
                    <p class="text-success" style="font-size: 20px; font-weight: bold;">
                        ${{ number_format($habitacion->precio_base, 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection
