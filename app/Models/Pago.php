<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reserva_id',
        'metodo_pago_id',
        'monto',
        'estado',
        'transaccion_id',
        'referencia',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    // RELACIONES
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function metodoPago(): BelongsTo
    {
        return $this->belongsTo(MetodoPago::class);
    }
}
