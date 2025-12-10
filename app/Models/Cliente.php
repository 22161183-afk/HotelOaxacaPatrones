<?php

namespace App\Models;

use App\Services\Notifications\TelefonoValidator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'direccion',
        'ciudad',
        'pais',
        'tipo',
    ];

    // RELACIONES
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    // MUTADORES
    /**
     * Formatear el teléfono al guardarlo
     */
    public function setTelefonoAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['telefono'] = null;

            return;
        }

        // Formatear automáticamente al formato internacional
        $formateado = TelefonoValidator::formatear($value);
        $this->attributes['telefono'] = $formateado ?? $value;
    }

    // ACCESSORS
    /**
     * Obtener el teléfono en formato legible
     */
    public function getTelefonoFormateadoAttribute(): ?string
    {
        if (empty($this->telefono)) {
            return null;
        }

        return TelefonoValidator::formatearLegible($this->telefono);
    }

    /**
     * Validar si el teléfono es válido
     */
    public function tieneTelefonoValido(): bool
    {
        if (empty($this->telefono)) {
            return false;
        }

        return TelefonoValidator::validar($this->telefono);
    }
}
