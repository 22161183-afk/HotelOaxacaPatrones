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
        return abs($this->fecha_inicio->diffInDays($this->fecha_fin));
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

    // ============================================================
    // STATE PATTERN - Gestión de Estados
    // ============================================================

    /**
     * Obtener el contexto de estado de la reserva
     */
    public function stateContext(): \App\Patterns\Behavioral\ReservaContext
    {
        return new \App\Patterns\Behavioral\ReservaContext($this);
    }

    /**
     * Confirmar reserva usando State Pattern
     */
    public function confirmarReserva(): bool
    {
        $context = $this->stateContext();
        $resultado = $context->confirmar();

        if ($resultado) {
            // Cambiar estado de habitación a reservada
            $this->habitacion->update(['estado' => 'reservada']);
        }

        return $resultado;
    }

    /**
     * Cancelar reserva usando State Pattern
     */
    public function cancelarReserva(): bool
    {
        $context = $this->stateContext();
        $resultado = $context->cancelar();

        if ($resultado) {
            // Liberar habitación
            $this->habitacion->update(['estado' => 'disponible']);
        }

        return $resultado;
    }

    /**
     * Completar reserva usando State Pattern
     */
    public function completarReserva(): bool
    {
        $context = $this->stateContext();
        $resultado = $context->completar();

        if ($resultado) {
            // Liberar habitación
            $this->habitacion->update(['estado' => 'disponible']);
        }

        return $resultado;
    }

    /**
     * Verificar si la reserva puede ser modificada
     */
    public function puedeModificar(): bool
    {
        $context = $this->stateContext();

        return $context->puedeModificar();
    }

    /**
     * Obtener nombre del estado actual usando State Pattern
     */
    public function obtenerEstadoActual(): string
    {
        $context = $this->stateContext();

        return $context->getEstadoActual();
    }

    // ============================================================
    // PRICING STRATEGY PATTERN - Cálculo de Precios Dinámico
    // ============================================================

    /**
     * Calcular precio usando estrategia específica
     */
    public function calcularPrecioConEstrategia(string $tipoEstrategia = 'normal'): float
    {
        $estrategia = match ($tipoEstrategia) {
            'temporada' => new \App\Patterns\Behavioral\PrecioTemporada,
            'fidelidad' => new \App\Patterns\Behavioral\PrecioFidelidad,
            'ultima_hora' => new \App\Patterns\Behavioral\PrecioUltimaHora,
            default => new \App\Patterns\Behavioral\PrecioNormal,
        };

        $calculador = new \App\Patterns\Behavioral\CalculadorPrecio($estrategia);

        return $calculador->calcular($this->habitacion, $this->calcularNoches());
    }

    /**
     * Detectar y aplicar la mejor estrategia de precio automáticamente
     */
    public function aplicarMejorEstrategia(): float
    {
        // Verificar si es cliente frecuente (más de 3 reservas)
        $reservasCliente = self::where('cliente_id', $this->cliente_id)
            ->where('estado', 'completada')
            ->count();

        if ($reservasCliente >= 3) {
            return $this->calcularPrecioConEstrategia('fidelidad');
        }

        // Verificar si es última hora (menos de 3 días para la fecha)
        $diasHastaReserva = now()->diffInDays($this->fecha_inicio);
        if ($diasHastaReserva <= 3) {
            return $this->calcularPrecioConEstrategia('ultima_hora');
        }

        // Verificar si es temporada alta (diciembre, semana santa, julio-agosto)
        $mes = $this->fecha_inicio->month;
        if (in_array($mes, [12, 7, 8])) {
            return $this->calcularPrecioConEstrategia('temporada');
        }

        return $this->calcularPrecioConEstrategia('normal');
    }
}
