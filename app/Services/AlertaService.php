<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\User;

class AlertaService
{
    public const SNOOZE_MIN_HORAS = 48;

    public const SNOOZE_MAX_HORAS = 72;

    /**
     * Posponer (snooze) una alerta por 48–72 horas.
     * Plan 13.3: cuando el trámite ya está en curso.
     *
     * @param  int  $horas  Entre 48 y 72. Por defecto 48.
     * @throws \InvalidArgumentException Si horas fuera de rango o alerta ya cerrada.
     */
    public function snoozar(Alerta $alerta, User $usuario, string $motivo, int $horas = self::SNOOZE_MIN_HORAS): void
    {
        if ($alerta->estado === 'cerrada') {
            throw new \InvalidArgumentException(__('alerta.snooze_cerrada'));
        }

        $horas = max(self::SNOOZE_MIN_HORAS, min(self::SNOOZE_MAX_HORAS, $horas));

        $alerta->update([
            'snoozed_until' => now()->addHours($horas),
            'snoozed_by' => $usuario->id,
            'snoozed_reason' => $motivo,
        ]);
    }

    /**
     * Quita el snooze de una alerta (vuelve a mostrarse de inmediato).
     */
    public function limpiarSnooze(Alerta $alerta): void
    {
        $alerta->update([
            'snoozed_until' => null,
            'snoozed_by' => null,
            'snoozed_reason' => null,
        ]);
    }
}
