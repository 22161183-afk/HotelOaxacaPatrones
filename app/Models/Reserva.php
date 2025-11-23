<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'habitacion_id',
        'usuario_id',
        'fecha_inicio',
        'fecha_fin',
        'numero_huespedes',
        'estado',
        'precio_total',
        'precio_servicios',
        'observaciones',
        'fecha_confirmacion',
        'fecha_cancelacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_confirmacion' => 'datetime',
        'fecha_cancelacion' => 'datetime',
        'precio_total' => 'decimal:2',
        'precio_servicios' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'reserva_servicio')
            ->withPivot(['cantidad', 'precio_unitario', 'subtotal'])
            ->withTimestamps();
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    // ============================================================
    // DECORATOR - PATRÓN ESTRUCTURAL (Agregar servicios)
    // ============================================================

    public function conDesayuno()
    {
        $servicio = Servicio::where('nombre', 'Desayuno')->first();
        if ($servicio && ! $this->servicios->contains($servicio->id)) {
            $this->servicios()->attach($servicio->id, ['precio_unitario' => $servicio->precio, 'cantidad' => 1, 'subtotal' => $servicio->precio]);
        }

        return $this;
    }

    public function conSpa()
    {
        $servicio = Servicio::where('nombre', 'Spa')->first();
        if ($servicio && ! $this->servicios->contains($servicio->id)) {
            $this->servicios()->attach($servicio->id, ['precio_unitario' => $servicio->precio, 'cantidad' => 1, 'subtotal' => $servicio->precio]);
        }

        return $this;
    }

    public function conTransporte()
    {
        $servicio = Servicio::where('nombre', 'Transporte')->first();
        if ($servicio && ! $this->servicios->contains($servicio->id)) {
            $this->servicios()->attach($servicio->id, ['precio_unitario' => $servicio->precio, 'cantidad' => 1, 'subtotal' => $servicio->precio]);
        }

        return $this;
    }

    public function conExcursion()
    {
        $servicio = Servicio::where('nombre', 'Excursión')->first();
        if ($servicio && ! $this->servicios->contains($servicio->id)) {
            $this->servicios()->attach($servicio->id, ['precio_unitario' => $servicio->precio, 'cantidad' => 1, 'subtotal' => $servicio->precio]);
        }

        return $this;
    }

    // ============================================================
    // CÁLCULOS DE PRECIO
    // ============================================================

    public function calcularNoches()
    {
        return $this->fecha_fin->diffInDays($this->fecha_inicio);
    }

    public function calcularPrecioServicios()
    {
        return $this->servicios->sum('pivot.subtotal') ?? 0;
    }

    public function calcularPrecioFinal($conImpuestos = true)
    {
        $habitacion = $this->habitacion->precio_base * $this->calcularNoches();
        $servicios = $this->calcularPrecioServicios();
        $subtotal = $habitacion + $servicios;

        if ($conImpuestos) {
            $config = \App\Patterns\Creational\ConfiguracionSingleton::getInstance();
            $impuestos = $subtotal * ($config->getImpuesto() / 100);

            return $subtotal + $impuestos;
        }

        return $subtotal;
    }

    // PROTOTYPE - Clonar reserva
    public function clonarConServicios()
    {
        $clone = $this->replicate();
        $clone->save();

        // Copiar servicios
        foreach ($this->servicios as $servicio) {
            $clone->servicios()->attach($servicio->id, ['precio_unitario' => $servicio->pivot->precio_unitario, 'cantidad' => $servicio->pivot->cantidad, 'subtotal' => $servicio->pivot->subtotal]);
        }

        return $clone;
    }

    // Trait para usar el patrón State
    private $stateInstance;

    public function setState($state): void
    {
        $this->stateInstance = $state;
    }

    public function getState()
    {
        return $this->stateInstance;
    }
}
