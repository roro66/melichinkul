<?php

namespace App\Support;

class ChileanValidationHelper
{
    /**
     * Letras permitidas en patentes chilenas (sin vocales ni Ñ, Q, etc.).
     */
    public const LETRAS_PATENTE = 'BCDFGHJKLPQRSTVWXYZ';

    /**
     * Valida RUT chileno: formato y dígito verificador (Módulo 11).
     */
    public static function validarRut(string $rut): bool
    {
        $rut = self::normalizarRut($rut);

        if (! preg_match('/^(\d{7,8})-([0-9K])$/', $rut)) {
            return false;
        }

        [, $cuerpo, $dv] = preg_match('/^(\d+)-([0-9K])$/', $rut, $m) ? [$m[0], $m[1], strtoupper($m[2])] : [null, null, null];

        if (! $cuerpo || $dv === null) {
            return false;
        }

        $dvCalculado = self::calcularDigitoVerificador($cuerpo);

        return $dv === $dvCalculado;
    }

    /**
     * Normaliza RUT: sin puntos, con guion, DV en mayúscula.
     */
    public static function normalizarRut(string $rut): string
    {
        $rut = preg_replace('/[^\dKk\-]/', '', $rut);

        if (! str_contains($rut, '-')) {
            if (strlen($rut) >= 2) {
                $dv = substr($rut, -1);
                $cuerpo = substr($rut, 0, -1);
                $rut = $cuerpo . '-' . strtoupper($dv);
            }
        } else {
            $partes = explode('-', $rut, 2);
            $rut = $partes[0] . '-' . strtoupper($partes[1] ?? '');
        }

        return $rut;
    }

    /**
     * Calcula el dígito verificador con algoritmo Módulo 11.
     * Devuelve '0', 'K' o '1'..'9'.
     */
    public static function calcularDigitoVerificador(string $cuerpo): string
    {
        $serie = [2, 3, 4, 5, 6, 7];
        $suma = 0;
        $i = 0;
        for ($k = strlen($cuerpo) - 1; $k >= 0; $k--) {
            $suma += (int) $cuerpo[$k] * $serie[$i % 6];
            $i++;
        }
        $resto = $suma % 11;
        $resultado = 11 - $resto;

        if ($resultado === 11) {
            return '0';
        }
        if ($resultado === 10) {
            return 'K';
        }

        return (string) $resultado;
    }

    /**
     * Valida patente chilena.
     * Autos: 4 letras + 2 dígitos (AAAA99). Motos: 3 letras + 2 dígitos (AAA99).
     */
    public static function validarPatente(string $patente, string $tipoVehiculo = 'auto'): bool
    {
        $patente = self::normalizarPatente($patente);

        $letras = '[' . self::LETRAS_PATENTE . ']';

        if ($tipoVehiculo === 'moto') {
            return (bool) preg_match('/^' . $letras . '{3}\d{2}$/', $patente);
        }

        return (bool) preg_match('/^' . $letras . '{4}\d{2}$/', $patente);
    }

    /**
     * Normaliza patente: mayúsculas, sin espacios ni guiones.
     */
    public static function normalizarPatente(string $patente): string
    {
        return strtoupper(preg_replace('/[\s\-]/', '', $patente));
    }

    /**
     * Regla de validación para Laravel: 'rut' => [ChileanValidationHelper::ruleRut()].
     */
    public static function ruleRut(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! is_string($value) || ! self::validarRut($value)) {
                $fail(__('chile.rut', ['attribute' => $attribute]));
            }
        };
    }

    /**
     * Regla de validación para Laravel: 'patente' => [ChileanValidationHelper::rulePatente('auto')].
     */
    public static function rulePatente(string $tipoVehiculo = 'auto'): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($tipoVehiculo) {
            if (! is_string($value) || ! self::validarPatente($value, $tipoVehiculo)) {
                $fail(__('chile.patente', ['attribute' => $attribute]));
            }
        };
    }
}
