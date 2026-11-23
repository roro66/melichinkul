<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\User;

class AlertService
{
    public const SNOOZE_MIN_HOURS = 48;

    public const SNOOZE_MAX_HOURS = 72;

    /**
     * Snooze an alert. Other alerts stay between 48 and 72 hours.
     * License alerts use a window of 1 to 7 days when $licenseWindow is true.
     *
     * @param  int  $hours  Hours to snooze. Clamped to the window that applies.
     * @throws \InvalidArgumentException If hours out of range or alert already closed.
     */
    public function snooze(Alert $alert, User $user, string $reason, int $hours = self::SNOOZE_MIN_HOURS, bool $licenseWindow = false): void
    {
        if ($alert->status === 'closed') {
            throw new \InvalidArgumentException(__('alerta.snooze_cerrada'));
        }

        if ($licenseWindow) {
            $hours = max(24, min(24 * LicenseAlertService::SNOOZE_MAX_DAYS, $hours));
        } else {
            $hours = max(self::SNOOZE_MIN_HOURS, min(self::SNOOZE_MAX_HOURS, $hours));
        }

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
