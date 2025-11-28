@extends('layouts.admin')

@section('page-title', 'Crear Nueva Habitación')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">
                    <i class="fas fa-plus"></i> Crear Nueva Habitación
                </h4>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('admin.habitaciones.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="numero" class="form-label">Número de Habitación <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('numero') is-invalid @enderror"
                                    id="numero"
                                    name="numero"
                                    value="{{ old('numero') }}"
                                    placeholder="Ej: 101, 202, etc."
                                    required>
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo_habitacion_id" class="form-label">Tipo de Habitación <span class="text-danger">*</span></label>
                                <select
                                    class="form-select @error('tipo_habitacion_id') is-invalid @enderror"
                                    id="tipo_habitacion_id"
                                    name="tipo_habitacion_id"
                                    required>
                                    <option value="">Seleccione un tipo</option>
                                    @foreach($tiposHabitacion as $tipo)
                                        <option value="{{ $tipo->id }}" {{ old('tipo_habitacion_id') == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tipo_habitacion_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="piso" class="form-label">Piso <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    class="form-control @error('piso') is-invalid @enderror"
                                    id="piso"
                                    name="piso"
                                    value="{{ old('piso', 1) }}"
                                    min="1"
                                    required>
                                @error('piso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="capacidad" class="form-label">Capacidad (personas) <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    class="form-control @error('capacidad') is-invalid @enderror"
                                    id="capacidad"
                                    name="capacidad"
                                    value="{{ old('capacidad', 2) }}"
                                    min="1"
                                    required>
                                @error('capacidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="precio_base" class="form-label">Precio Base (por noche) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input
                                        type="number"
                                        class="form-control @error('precio_base') is-invalid @enderror"
                                        id="precio_base"
                                        name="precio_base"
                                        value="{{ old('precio_base') }}"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        required>
                                    @error('precio_base')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select
                                    class="form-select @error('estado') is-invalid @enderror"
                                    id="estado"
                                    name="estado"
                                    required>
                                    <option value="disponible" {{ old('estado') === 'disponible' ? 'selected' : 'selected' }}>Disponible</option>
                                    <option value="reservada" {{ old('estado') === 'reservada' ? 'selected' : '' }}>Reservada</option>
                                    <option value="ocupada" {{ old('estado') === 'ocupada' ? 'selected' : '' }}>Ocupada</option>
                                    <option value="mantenimiento" {{ old('estado') === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea
                                    class="form-control @error('descripcion') is-invalid @enderror"
                                    id="descripcion"
                                    name="descripcion"
                                    rows="3"
                                    placeholder="Descripción detallada de la habitación...">{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="amenidades" class="form-label">Amenidades</label>
                                <input
                                    type="text"
                                    class="form-control @error('amenidades') is-invalid @enderror"
                                    id="amenidades"
                                    name="amenidades"
                                    value="{{ old('amenidades') }}"
                                    placeholder="Ejemplo: WiFi, TV, Aire Acondicionado (separadas por comas)">
                                <small class="form-text text-muted">Separe las amenidades con comas</small>
                                @error('amenidades')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="imagen_url" class="form-label">
                                    URL de Imagen Personalizada
                                    <small class="text-muted">(Opcional - Usar patrón Factory)</small>
                                </label>
                                <input
                                    type="url"
                                    class="form-control @error('imagen_url') is-invalid @enderror"
                                    id="imagen_url"
                                    name="imagen_url"
                                    value="{{ old('imagen_url') }}"
                                    placeholder="https://ejemplo.com/imagen.jpg">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Si no se proporciona, se usará el <strong>Patrón Factory</strong> para generar imágenes automáticamente según el tipo de habitación.
                                </small>
                                @error('imagen_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Crear Habitación
                        </button>
                        <a href="{{ route('admin.habitaciones.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
