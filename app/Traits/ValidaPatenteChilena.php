<?php

namespace App\Traits;

use App\Support\ChileanValidationHelper;
use Illuminate\Validation\Validator;

trait ValidaPatenteChilena
{
    /**
     * Valida que el atributo sea una patente chilena válida.
     * Uso: $this->validatePatente($validator, 'patente', 'auto');
     */
    public function validatePatente(Validator $validator, string $attribute = 'patente', string $tipoVehiculo = 'auto'): void
    {
        $valor = data_get($validator->getData(), $attribute);

        if ($valor !== null && $valor !== '' && ! ChileanValidationHelper::validarPatente((string) $valor, $tipoVehiculo)) {
            $validator->errors()->add(
                $attribute,
                __('chile.patente', ['attribute' => $attribute])
            );
        }
    }

    /**
     * Regla para rules(): 'patente' => ['required', ChileanValidationHelper::rulePatente('auto')].
     */
    public static function rulePatente(string $tipoVehiculo = 'auto'): \Closure
    {
        return ChileanValidationHelper::rulePatente($tipoVehiculo);
    }
}
