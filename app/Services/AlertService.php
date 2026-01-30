<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\User;

class AlertService
{
    public const SNOOZE_MIN_HOURS = 48;

    public const SNOOZE_MAX_HOURS = 72;

    /**
     * Snooze an alert for 48–72 hours (e.g. when the process is already in progress).
     *
     * @param  int  $hours  Between 48 and 72. Default 48.
     * @throws \InvalidArgumentException If hours out of range or alert already closed.
     */
    public function snooze(Alert $alert, User $user, string $reason, int $hours = self::SNOOZE_MIN_HOURS): void
    {
        if ($alert->status === 'closed') {
            throw new \InvalidArgumentException(__('alerta.snooze_cerrada'));
        }

        $hours = max(self::SNOOZE_MIN_HOURS, min(self::SNOOZE_MAX_HOURS, $hours));

        $alert->update([
            'snoozed_until' => now()->addHours($hours),
            'snoozed_by_id' => $user->id,
            'snoozed_reason' => $reason,
        ]);
    }

    /**
     * Clear snooze from an alert (show it again immediately).
     */
    public function clearSnooze(Alert $alert): void
    {
        $alert->update([
            'snoozed_until' => null,
            'snoozed_by_id' => null,
            'snoozed_reason' => null,
        ]);
    }
}
