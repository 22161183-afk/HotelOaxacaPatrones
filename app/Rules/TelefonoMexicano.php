<?php

namespace App\Rules;

use App\Services\Notifications\TelefonoValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelefonoMexicano implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Permitir vacío si no es required
        }

        if (! TelefonoValidator::validar($value)) {
            $fail('El :attribute debe ser un número de teléfono mexicano válido de 10 dígitos (ejemplo: 9512342422).');
        }
    }
}
