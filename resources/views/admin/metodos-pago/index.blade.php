@extends('layouts.admin')

@section('page-title', 'Métodos de Pago')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('admin.metodos-pago.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Agregar Método de Pago
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-credit-card"></i> Métodos de Pago Disponibles</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($metodosPago as $metodo)
                        <tr>
                            <td>{{ $metodo->nombre }}</td>
                            <td>
                                @if($metodo->tipo === 'tarjeta_credito')
                                    <span class="badge bg-primary">Tarjeta Crédito</span>
                                @elseif($metodo->tipo === 'tarjeta_debito')
                                    <span class="badge bg-info">Tarjeta Débito</span>
                                @elseif($metodo->tipo === 'transferencia')
                                    <span class="badge bg-success">Transferencia</span>
                                @elseif($metodo->tipo === 'efectivo')
                                    <span class="badge bg-warning">Efectivo</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($metodo->tipo) }}</span>
                                @endif
                            </td>
                            <td>{{ $metodo->descripcion ?: 'N/A' }}</td>
                            <td>
                                @if($metodo->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.metodos-pago.edit', $metodo->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay métodos de pago registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN PERSONALIZADA -->
        @if($metodosPago->hasPages())
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Botones de navegación -->
                    <div>
                        @if($metodosPago->onFirstPage())
                            <span class="text-muted">« Volver</span>
                        @else
                            <a href="{{ $metodosPago->previousPageUrl() }}" class="text-decoration-none">« Volver</a>
                        @endif

                        <span class="mx-3">|</span>

                        @if($metodosPago->hasMorePages())
                            <a href="{{ $metodosPago->nextPageUrl() }}" class="text-decoration-none">Siguiente »</a>
                        @else
                            <span class="text-muted">Siguiente »</span>
                        @endif
                    </div>

                    <!-- Información de resultados -->
                    <div class="text-muted small">
                        Mostrando {{ $metodosPago->firstItem() ?? 0 }} a {{ $metodosPago->lastItem() ?? 0 }} de {{ $metodosPago->total() }} resultados
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
