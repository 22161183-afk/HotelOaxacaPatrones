@extends('layouts.public')

@section('page-title', 'Habitación ' . $habitacion->numero . ' - ' . $habitacion->tipoHabitacion->nombre)

@section('content')

@php
    // Usar el patrón Factory para obtener la galería de imágenes
    $galeriaImagenes = \App\Patterns\Creational\HabitacionImagenFactory::obtenerGaleriaCompleta($habitacion);
@endphp

<!-- GALERÍA DE IMÁGENES -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div id="galeriaCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    @foreach($galeriaImagenes as $index => $imagen)
                        <button type="button" data-bs-target="#galeriaCarousel" data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }}"
                                aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-label="Imagen {{ $index + 1 }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach($galeriaImagenes as $index => $imagen)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ $imagen }}" class="d-block w-100" alt="Habitación {{ $habitacion->numero }} - Foto {{ $index + 1 }}"
                                 style="height: 500px; object-fit: cover;">
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#galeriaCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#galeriaCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MINIATURAS DE LA GALERÍA -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex gap-2 overflow-auto pb-2">
            @foreach($galeriaImagenes as $index => $imagen)
                <img src="{{ $imagen }}"
                     class="img-thumbnail miniatura-galeria"
                     alt="Miniatura {{ $index + 1 }}"
                     style="width: 100px; height: 80px; object-fit: cover; cursor: pointer;"
                     data-bs-target="#galeriaCarousel"
                     data-bs-slide-to="{{ $index }}">
            @endforeach
        </div>
    </div>
</div>

<!-- INFORMACIÓN DE LA HABITACIÓN -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-door-open"></i> Habitación {{ $habitacion->numero }}
                </h3>
                <span class="badge bg-{{ $habitacion->estado === 'disponible' ? 'success' : ($habitacion->estado === 'ocupada' ? 'danger' : 'warning') }} fs-6">
                    {{ ucfirst($habitacion->estado) }}
                </span>
            </div>
            <div class="card-body">
                <!-- TIPO Y PRECIO DESTACADOS -->
                <div class="alert alert-light border mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0 text-primary">
                                <i class="fas fa-bed"></i> {{ $habitacion->tipoHabitacion->nombre }}
                            </h4>
                            <p class="mb-0 text-muted">
                                <i class="fas fa-users"></i> Hasta {{ $habitacion->capacidad }} personas
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h2 class="mb-0 text-success">
                                ${{ number_format($habitacion->precio_base, 2) }}
                            </h2>
                            <p class="mb-0 text-muted">por noche</p>
                        </div>
                    </div>
                </div>

                <!-- INFORMACIÓN GENERAL -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5><i class="fas fa-info-circle"></i> Información General</h5>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><i class="fas fa-building text-primary"></i> <strong>Piso:</strong></td>
                                <td>{{ $habitacion->piso }}</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-hashtag text-primary"></i> <strong>Número:</strong></td>
                                <td>{{ $habitacion->numero }}</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-users text-primary"></i> <strong>Capacidad:</strong></td>
                                <td>{{ $habitacion->capacidad }} personas</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-tag text-primary"></i> <strong>Precio Base:</strong></td>
                                <td class="text-success fw-bold">${{ number_format($habitacion->precio_base, 2) }}/noche</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-star"></i> Amenidades Incluidas</h5>
                        <div class="amenidades-grid">
                            @forelse($habitacion->amenidades ?? [] as $amenidad)
                                <div class="amenidad-item mb-2">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>{{ $amenidad }}</span>
                                </div>
                            @empty
                                <p class="text-muted">Sin amenidades especificadas</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <hr>

                <!-- DESCRIPCIÓN -->
                <div class="mb-4">
                    <h5><i class="fas fa-align-left"></i> Descripción</h5>
                    <p class="text-justify">
                        {{ $habitacion->descripcion ?? 'Esta habitación cuenta con todas las comodidades necesarias para una estadía placentera. Equipada con muebles de calidad y diseño moderno, ofrece un ambiente acogedor y funcional.' }}
                    </p>
                </div>

                <hr>

                <!-- CARACTERÍSTICAS BASE -->
                <div class="mb-4">
                    <h5><i class="fas fa-list-check"></i> Características de Base</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="caracteristica-item">
                                <i class="fas fa-wifi text-primary fa-2x mb-2"></i>
                                <h6>WiFi de Alta Velocidad</h6>
                                <p class="text-muted small">Conexión gratuita</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="caracteristica-item">
                                <i class="fas fa-tv text-primary fa-2x mb-2"></i>
                                <h6>TV por Cable</h6>
                                <p class="text-muted small">Canales premium</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="caracteristica-item">
                                <i class="fas fa-snowflake text-primary fa-2x mb-2"></i>
                                <h6>Aire Acondicionado</h6>
                                <p class="text-muted small">Control de clima</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="caracteristica-item">
                                <i class="fas fa-bath text-primary fa-2x mb-2"></i>
                                <h6>Baño Privado</h6>
                                <p class="text-muted small">Agua caliente 24/7</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="caracteristica-item">
                                <i class="fas fa-concierge-bell text-primary fa-2x mb-2"></i>
                                <h6>Servicio a Habitación</h6>
                                <p class="text-muted small">Disponible 24 horas</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="caracteristica-item">
                                <i class="fas fa-shield-alt text-primary fa-2x mb-2"></i>
                                <h6>Caja de Seguridad</h6>
                                <p class="text-muted small">Protege tus valores</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="col-md-4">
        <!-- BOTÓN DE RESERVA DESTACADO -->
        <div class="card mb-3 shadow">
            <div class="card-body text-center">
                <h4 class="text-success mb-3">
                    ${{ number_format($habitacion->precio_base, 2) }}
                    <small class="text-muted d-block fs-6">por noche</small>
                </h4>

                @if($habitacion->estado === 'disponible')
                    <div class="alert alert-success py-2">
                        <i class="fas fa-check-circle"></i> Disponible
                    </div>

                    @auth
                        @if(auth()->user()->isCliente())
                            <a href="{{ route('cliente.reservas.create', ['habitacion_id' => $habitacion->id]) }}"
                               class="btn btn-success btn-lg w-100 mb-2">
                                <i class="fas fa-calendar-check"></i> Reservar Ahora
                            </a>
                        @elseif(auth()->user()->isAdmin())
                            <a href="{{ route('admin.habitaciones.edit', $habitacion->id) }}"
                               class="btn btn-warning btn-lg w-100 mb-2">
                                <i class="fas fa-edit"></i> Editar Habitación
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-success btn-lg w-100 mb-2">
                            <i class="fas fa-sign-in-alt"></i> Inicia sesión para reservar
                        </a>
                    @endauth

                    <a href="javascript:history.back()" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                @else
                    <div class="alert alert-danger py-2">
                        <i class="fas fa-times-circle"></i> No Disponible
                    </div>
                    <a href="javascript:history.back()" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                @endif
            </div>
        </div>

        <!-- INFORMACIÓN ADICIONAL -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información Útil</h6>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-clock text-primary"></i> Horarios</h6>
                <p class="small mb-3">
                    <strong>Entrada:</strong> 15:00 hrs<br>
                    <strong>Salida:</strong> 12:00 hrs
                </p>

                <h6><i class="fas fa-ban text-danger"></i> Políticas</h6>
                <p class="small mb-3">
                    • Cancelación gratuita hasta 48 hrs antes<br>
                    • No se permiten mascotas<br>
                    • Prohibido fumar
                </p>

                <h6><i class="fas fa-phone text-success"></i> Contacto</h6>
                <p class="small mb-0">
                    <i class="fas fa-envelope"></i> reservas@hoteloaxaca.com<br>
                    <i class="fas fa-phone"></i> +52 951 123 4567
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.miniatura-galeria:hover {
    opacity: 0.7;
    transform: scale(1.05);
    transition: all 0.3s;
}

.caracteristica-item {
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s;
}

.caracteristica-item:hover {
    background-color: #f8f9fa;
    transform: translateY(-5px);
}

.amenidad-item {
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

@endsection
