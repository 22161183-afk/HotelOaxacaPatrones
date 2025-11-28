<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Habitacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tipo_habitacion_id',
        'numero',
        'piso',
        'capacidad',
        'precio_base',
        'descripcion',
        'amenidades',
        'imagen_url',
        'estado',
    ];

    protected $casts = [
        'amenidades' => 'array',
        'precio_base' => 'decimal:2',
    ];

    // RELACIONES
    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitacion_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    // ============================================================
    // STATE PATTERN - Gestión de Estados de Habitación
    // ============================================================

    /**
     * Obtener el contexto de estado de la habitación
     */
    public function stateContext(): \App\Patterns\Behavioral\HabitacionContext
    {
        return new \App\Patterns\Behavioral\HabitacionContext($this);
    }

    /**
     * Marcar habitación como reservada usando State Pattern
     */
    public function marcarComoReservada(): bool
    {
        $context = $this->stateContext();

        return $context->reservar();
    }

    /**
     * Marcar habitación como ocupada usando State Pattern
     */
    public function marcarComoOcupada(): bool
    {
        $context = $this->stateContext();

        return $context->ocupar();
    }

    /**
     * Liberar habitación usando State Pattern
     */
    public function liberarHabitacion(): bool
    {
        $context = $this->stateContext();

        return $context->liberar();
    }

    /**
     * Marcar habitación en mantenimiento usando State Pattern
     */
    public function marcarEnMantenimiento(): bool
    {
        $context = $this->stateContext();

        return $context->mantenimiento();
    }

    /**
     * Verificar si la habitación puede ser reservada
     */
    public function puedeSerReservada(): bool
    {
        $context = $this->stateContext();

        return $context->puedeReservar();
    }

    /**
     * Verificar si la habitación está disponible
     */
    public function estaDisponible(): bool
    {
        return $this->estado === 'disponible';
    }

    /**
     * Verificar si tiene reservas activas
     */
    public function tieneReservasActivas(): bool
    {
        return $this->reservas()
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where('fecha_fin', '>=', now())
            ->exists();
    }
}
