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
}
