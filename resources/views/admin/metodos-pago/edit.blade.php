@extends('layouts.admin')

@section('page-title', 'Editar Método de Pago')

@section('content')

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">
                    <i class="fas fa-edit"></i> Editar Método de Pago: {{ $metodoPago->nombre }}
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.metodos-pago.update', $metodoPago->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control @error('nombre') is-invalid @enderror"
                            id="nombre"
                            name="nombre"
                            value="{{ old('nombre', $metodoPago->nombre) }}"
                            required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select
                            class="form-select @error('tipo') is-invalid @enderror"
                            id="tipo"
                            name="tipo"
                            required>
                            <option value="">Seleccione un tipo</option>
                            <option value="tarjeta_credito" {{ old('tipo', $metodoPago->tipo) === 'tarjeta_credito' ? 'selected' : '' }}>Tarjeta de Crédito</option>
                            <option value="tarjeta_debito" {{ old('tipo', $metodoPago->tipo) === 'tarjeta_debito' ? 'selected' : '' }}>Tarjeta de Débito</option>
                            <option value="transferencia" {{ old('tipo', $metodoPago->tipo) === 'transferencia' ? 'selected' : '' }}>Transferencia Bancaria</option>
                            <option value="efectivo" {{ old('tipo', $metodoPago->tipo) === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea
                            class="form-control @error('descripcion') is-invalid @enderror"
                            id="descripcion"
                            name="descripcion"
                            rows="3">{{ old('descripcion', $metodoPago->descripcion) }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="activo"
                                name="activo"
                                value="1"
                                {{ old('activo', $metodoPago->activo) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activo">
                                Método de pago activo
                            </label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('admin.metodos-pago.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
