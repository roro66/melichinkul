<?php

namespace App\Traits;

use App\Support\ChileanValidationHelper;
use Illuminate\Validation\Validator;

trait ValidaRutChileno
{
    /**
     * Valida que el atributo sea un RUT chileno válido.
     * Uso en Form Request: $this->validateRut($validator, 'rut');
     * O en modelo/otro: ValidaRutChileno::validarRut($valor).
     */
    public function validateRut(Validator $validator, string $attribute = 'rut'): void
    {
        $valor = data_get($validator->getData(), $attribute);

        if ($valor !== null && $valor !== '' && ! ChileanValidationHelper::validarRut((string) $valor)) {
            $validator->errors()->add(
                $attribute,
                __('chile.rut', ['attribute' => $attribute])
            );
        }
    }

    /**
     * Regla para rules(): 'rut' => ['required', ChileanValidationHelper::ruleRut()].
     */
    public static function ruleRut(): \Closure
    {
        return ChileanValidationHelper::ruleRut();
    }
}
