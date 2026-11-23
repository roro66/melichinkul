<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Driver;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LicenseAlertService
{
    public const TYPES = ['licencia_por_vencer', 'licencia_vencida'];

    public const WARNING_DAYS = 60;

    public const CRITICAL_DAYS = 15;

    public const SNOOZE_MAX_DAYS = 7;

    public const THRICE_WEEKLY_DAYS = 30;

    /**
     * Create or refresh license alerts for every driver.
     */
    public function generate(?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $created = 0;

        foreach (Driver::query()->orderBy('id')->get() as $driver) {
            $created += $this->refreshDriver($driver, $today)['created'];
        }

        return $created;
    }

    /**
     * Align open license alerts with the driver's current expiration date.
     *
     * @return array{created: int, closed: int, state: string}
     */
    public function refreshDriver(Driver $driver, ?Carbon $today = null, bool $clearSnooze = false): array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $level = $driver->active ? $this->level($driver, $today) : null;

        if ($level === null) {
            $closed = $this->closeOpenForDriver($driver->id);

            return ['created' => 0, 'closed' => $closed, 'state' => 'clear'];
        }

        $vehicleIds = Vehicle::query()
            ->where('current_driver_id', $driver->id)
            ->orderBy('id')
            ->pluck('id')
            ->all();
        $desiredVehicleIds = $vehicleIds === []
            ? [null]
            : array_map(fn ($id) => (int) $id, $vehicleIds);

        $closed = 0;
        foreach ($this->openForDriver($driver->id) as $alert) {
            $alertVehicleId = $alert->vehicle_id === null ? null : (int) $alert->vehicle_id;
            if (! in_array($alertVehicleId, $desiredVehicleIds, true)) {
                $this->closeAlert($alert);
                $closed++;
            }
        }

        $created = 0;
        foreach ($desiredVehicleIds as $vehicleId) {
            $matches = $this->openForDriver($driver->id)
                ->filter(function (Alert $alert) use ($vehicleId) {
                    $alertVehicleId = $alert->vehicle_id === null ? null : (int) $alert->vehicle_id;

                    return $alertVehicleId === $vehicleId;
                })
                ->values();

            if ($matches->isEmpty()) {
                $this->createAlert($driver, $vehicleId, $level);
                $created++;
                continue;
            }

            foreach ($matches as $index => $alert) {
                if ($index === 0) {
                    $this->updateAlert($alert, $driver, $level, $clearSnooze);
                    continue;
                }
                $this->closeAlert($alert);
                $closed++;
            }
        }

        return [
            'created' => $created,
            'closed' => $closed,
            'state' => 'ongoing',
        ];
    }

    /**
     * Open license alerts that are not currently postponed.
     *
     * @return Collection<int, Alert>
     */
    public function pendingReminders(): Collection
    {
        return Alert::query()
            ->with('vehicle')
            ->whereIn('type', self::TYPES)
            ->where('status', '!=', 'closed')
            ->where(function ($query) {
                $query->whereNull('snoozed_until')->orWhere('snoozed_until', '<=', now());
            })
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, Alert>  $alerts
     * @return list<array{title: string, message: string|null, due_date: string|null, url: string}>
     */
    public function reminderItems(Collection $alerts): array
    {
        return $alerts
            ->unique(fn (Alert $alert) => (string) ($alert->metadata['driver_id'] ?? 'alert-'.$alert->id))
            ->map(function (Alert $alert) {
                $driverId = $alert->metadata['driver_id'] ?? null;

                return [
                    'title' => $alert->title,
                    'message' => $alert->message,
                    'due_date' => $alert->due_date?->format('d/m/Y'),
                    'url' => $driverId ? route('conductores.edit', $driverId) : route('alerts.index'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Alert>  $alerts
     */
    public function markReminderSlot(Collection $alerts, string $slot): void
    {
        foreach ($alerts as $alert) {
            $metadata = $alert->metadata ?? [];
            $metadata['last_reminder_slot'] = $slot;
            $alert->update(['metadata' => $metadata]);
        }
    }

    /**
     * @param  Collection<int, Alert>  $alerts
     */
    public function markEmailSent(Collection $alerts, Carbon $today): void
    {
        $date = $today->toDateString();
        foreach ($alerts as $alert) {
            $metadata = $alert->metadata ?? [];
            $metadata['last_email_on'] = $date;
            $alert->update(['metadata' => $metadata]);
        }
    }

    /**
     * Correo: semanal entre 31 y 60 días, tres veces por semana entre 16 y 30,
     * y todos los días cuando faltan 15 días o menos (o ya venció).
     */
    public function emailTier(Alert $alert, ?Carbon $today = null): ?string
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $due = $alert->due_date?->copy()->startOfDay();
        if ($due === null) {
            return 'daily';
        }

        $daysUntil = (int) round($today->diffInDays($due, false));
        if ($daysUntil > self::WARNING_DAYS) {
            return null;
        }
        if ($daysUntil <= self::CRITICAL_DAYS) {
            return 'daily';
        }
        if ($daysUntil <= self::THRICE_WEEKLY_DAYS) {
            return 'thrice';
        }

        return 'weekly';
    }

    public function shouldEmailToday(Alert $alert, ?Carbon $today = null): bool
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $tier = $this->emailTier($alert, $today);
        if ($tier === null) {
            return false;
        }
        if (($alert->metadata['last_email_on'] ?? null) === $today->toDateString()) {
            return false;
        }

        $weekday = $today->dayOfWeekIso;

        return match ($tier) {
            'weekly' => $weekday === 1,
            'thrice' => in_array($weekday, [1, 3, 5], true),
            'daily' => true,
            default => false,
        };
    }

    public function emailCadenceText(Alert $alert, ?Carbon $today = null): string
    {
        return match ($this->emailTier($alert, $today)) {
            'weekly' => 'Mientras falten más de 30 días, el correo sale una vez por semana, los lunes.',
            'thrice' => 'Mientras falten entre 16 y 30 días, el correo sale tres veces por semana: lunes, miércoles y viernes.',
            'daily' => 'Cuando faltan 15 días o menos, o la licencia ya venció, el correo sale todos los días.',
            default => '',
        };
    }

    public function currentBusinessSlot(?Carbon $moment = null): ?string
    {
        $moment = ($moment ?? now())->timezone(config('app.timezone'));
        $minutes = ($moment->hour * 60) + $moment->minute;
        $date = $moment->toDateString();

        if ($minutes >= (9 * 60 + 30) && $minutes < (13 * 60)) {
            return $date.'-manana';
        }
        if ($minutes >= (13 * 60) && $minutes < (17 * 60)) {
            return $date.'-mediodia';
        }
        if ($minutes >= (17 * 60) && $minutes < (19 * 60)) {
            return $date.'-tarde';
        }

        return null;
    }

    /**
     * @return Collection<int, Alert>
     */
    public function openForDriver(int $driverId): Collection
    {
        if ($driverId < 1) {
            return collect();
        }

        return Alert::query()
            ->whereIn('type', self::TYPES)
            ->where('status', '!=', 'closed')
            ->whereRaw("metadata->>'driver_id' = ?", [(string) $driverId])
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array{type: string, severity: string, title: string, message: string}|null
     */
    private function level(Driver $driver, Carbon $today): ?array
    {
        $expiration = $driver->license_expiration_date?->copy()->startOfDay();
        if (! $expiration) {
            return null;
        }

        $daysUntil = (int) round($today->diffInDays($expiration, false));
        if ($daysUntil > self::WARNING_DAYS) {
            return null;
        }

        $name = $driver->full_name ?: 'Conductor';
        if ($daysUntil < 0) {
            return [
                'type' => 'licencia_vencida',
                'severity' => 'critica',
                'title' => "Licencia vencida: {$name}",
                'message' => "La licencia del conductor {$name} venció el ".$expiration->format('d/m/Y').'.',
            ];
        }

        return [
            'type' => 'licencia_por_vencer',
            'severity' => $daysUntil <= self::CRITICAL_DAYS ? 'critica' : 'advertencia',
            'title' => "Licencia por vencer: {$name}",
            'message' => "La licencia del conductor {$name} vence el ".$expiration->format('d/m/Y')." ({$daysUntil} días).",
        ];
    }

    /**
     * @param  array{type: string, severity: string, title: string, message: string}  $level
     */
    private function createAlert(Driver $driver, ?int $vehicleId, array $level): void
    {
        Alert::create([
            'vehicle_id' => $vehicleId,
            'maintenance_id' => null,
            'type' => $level['type'],
            'severity' => $level['severity'],
            'title' => $level['title'],
            'message' => $level['message'],
            'generated_at' => now(),
            'due_date' => $driver->license_expiration_date,
            'status' => 'pending',
            'metadata' => [
                'key' => 'driver_'.$driver->id,
                'driver_id' => $driver->id,
                'driver_name' => $driver->full_name,
            ],
        ]);
    }

    /**
     * @param  array{type: string, severity: string, title: string, message: string}  $level
     */
    private function updateAlert(Alert $alert, Driver $driver, array $level, bool $clearSnooze): void
    {
        $metadata = $alert->metadata ?? [];
        $metadata['key'] = 'driver_'.$driver->id;
        $metadata['driver_id'] = $driver->id;
        $metadata['driver_name'] = $driver->full_name;
        if ($clearSnooze) {
            unset($metadata['last_reminder_slot'], $metadata['last_email_on']);
        }

        $payload = [
            'type' => $level['type'],
            'severity' => $level['severity'],
            'title' => $level['title'],
            'message' => $level['message'],
            'due_date' => $driver->license_expiration_date,
            'metadata' => $metadata,
        ];

        if ($clearSnooze) {
            $payload['snoozed_until'] = null;
            $payload['snoozed_by_id'] = null;
            $payload['snoozed_reason'] = null;
        }

        $alert->update($payload);
    }

    private function closeOpenForDriver(int $driverId): int
    {
        $closed = 0;
        foreach ($this->openForDriver($driverId) as $alert) {
            $this->closeAlert($alert);
            $closed++;
        }

        return $closed;
    }

    private function closeAlert(Alert $alert): void
    {
        $alert->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by_id' => auth()->id(),
        ]);
    }
}
