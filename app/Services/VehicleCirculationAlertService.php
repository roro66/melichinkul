<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Certification;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VehicleCirculationAlertService
{
    public const TYPES = ['documento_circulacion_por_vencer', 'documento_circulacion_vencido'];

    /**
     * Documentos que, vencidos, impiden la libre circulación de cualquier móvil.
     *
     * @var array<string, array{label: string, phrase: string, article: string, participle: string}>
     */
    public const DOCUMENT_TYPES = [
        'permiso_circulacion' => [
            'label' => 'Permiso de circulación',
            'phrase' => 'permiso de circulación',
            'article' => 'El',
            'participle' => 'vencido',
        ],
        'technical_review' => [
            'label' => 'Revisión técnica',
            'phrase' => 'revisión técnica',
            'article' => 'La',
            'participle' => 'vencida',
        ],
        'soap' => [
            'label' => 'SOAP',
            'phrase' => 'SOAP',
            'article' => 'El',
            'participle' => 'vencido',
        ],
        'analisis_gases' => [
            'label' => 'Análisis de gases',
            'phrase' => 'análisis de gases',
            'article' => 'El',
            'participle' => 'vencido',
        ],
    ];

    public function generate(?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $created = 0;

        foreach (Vehicle::query()->orderBy('id')->get() as $vehicle) {
            $created += $this->refreshVehicle($vehicle, $today)['created'];
        }

        return $created;
    }

    /**
     * @return array{created: int, closed: int, state: string}
     */
    public function refreshVehicle(Vehicle $vehicle, ?Carbon $today = null, bool $clearSnooze = false): array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $this->retireGenericAlerts($vehicle->id);

        if ($vehicle->status === 'inactive') {
            $closed = $this->closeOpenForVehicle($vehicle->id);

            return ['created' => 0, 'closed' => $closed, 'state' => 'clear'];
        }

        $created = 0;
        $closed = 0;
        foreach (array_keys(self::DOCUMENT_TYPES) as $type) {
            $result = $this->refreshDocument($vehicle, $type, $today, $clearSnooze);
            $created += $result['created'];
            $closed += $result['closed'];
        }

        return [
            'created' => $created,
            'closed' => $closed,
            'state' => $created > 0 || $this->openForVehicle($vehicle->id)->isNotEmpty() ? 'ongoing' : 'clear',
        ];
    }

    /**
     * @return Collection<int, Alert>
     */
    public function pendingReminders(): Collection
    {
        return Alert::query()
            ->with(['vehicle.currentDriver'])
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
            ->unique(fn (Alert $alert) => (string) ($alert->metadata['key'] ?? 'alert-'.$alert->id))
            ->map(function (Alert $alert) {
                return [
                    'title' => $alert->title,
                    'message' => $alert->message,
                    'due_date' => $alert->due_date?->format('d/m/Y'),
                    'url' => $alert->vehicle_id
                        ? route('vehiculos.show', $alert->vehicle_id)
                        : route('alerts.index'),
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

    public function emailTier(Alert $alert, ?Carbon $today = null): ?string
    {
        return app(LicenseAlertService::class)->emailTier($alert, $today);
    }

    public function shouldEmailToday(Alert $alert, ?Carbon $today = null): bool
    {
        return app(LicenseAlertService::class)->shouldEmailToday($alert, $today);
    }

    public function emailCadenceText(Alert $alert, ?Carbon $today = null): string
    {
        return match ($this->emailTier($alert, $today)) {
            'weekly' => 'Mientras falten más de 30 días, el correo sale una vez por semana, los lunes.',
            'thrice' => 'Mientras falten entre 16 y 30 días, el correo sale tres veces por semana: lunes, miércoles y viernes.',
            'daily' => 'Cuando faltan 15 días o menos, o el documento ya venció, el correo sale todos los días.',
            default => '',
        };
    }

    public function currentBusinessSlot(?Carbon $moment = null): ?string
    {
        return app(LicenseAlertService::class)->currentBusinessSlot($moment);
    }

    /**
     * @return Collection<int, Alert>
     */
    public function openForKey(string $key): Collection
    {
        if ($key === '') {
            return collect();
        }

        return $this->openCirculationAlerts()
            ->filter(fn (Alert $alert) => ($alert->metadata['key'] ?? null) === $key)
            ->values();
    }

    /**
     * @return array{created: int, closed: int}
     */
    private function refreshDocument(Vehicle $vehicle, string $type, Carbon $today, bool $clearSnooze): array
    {
        $governing = $this->governingCertification($vehicle, $type);
        $level = $governing ? $this->level($vehicle, $governing, $today) : null;
        $open = $this->openForKey($this->key($vehicle->id, $type));

        if ($level === null || $governing === null) {
            $closed = 0;
            foreach ($open as $alert) {
                $this->closeAlert($alert);
                $closed++;
            }

            return ['created' => 0, 'closed' => $closed];
        }

        if ($open->isEmpty()) {
            $this->createAlert($vehicle, $governing, $level);

            return ['created' => 1, 'closed' => 0];
        }

        $closed = 0;
        foreach ($open as $index => $alert) {
            if ($index === 0) {
                $this->updateAlert($alert, $vehicle, $governing, $level, $clearSnooze);
                continue;
            }
            $this->closeAlert($alert);
            $closed++;
        }

        return ['created' => 0, 'closed' => $closed];
    }

    private function governingCertification(Vehicle $vehicle, string $type): ?Certification
    {
        return Certification::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('type', $type)
            ->where('active', true)
            ->orderByDesc('expiration_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array{type: string, severity: string, title: string, message: string}|null
     */
    private function level(Vehicle $vehicle, Certification $certification, Carbon $today): ?array
    {
        $spec = self::DOCUMENT_TYPES[$certification->type] ?? null;
        $expiration = $certification->expiration_date?->copy()->startOfDay();
        if ($spec === null || $expiration === null) {
            return null;
        }

        $daysUntil = (int) round($today->diffInDays($expiration, false));
        if ($daysUntil > LicenseAlertService::WARNING_DAYS) {
            return null;
        }

        $plate = $vehicle->license_plate;
        $when = $expiration->format('d/m/Y');
        if ($daysUntil < 0) {
            return [
                'type' => 'documento_circulacion_vencido',
                'severity' => 'critica',
                'title' => "{$spec['label']} {$spec['participle']}: {$plate}",
                'message' => "{$spec['article']} {$spec['phrase']} del móvil {$plate} venció el {$when}.",
            ];
        }

        return [
            'type' => 'documento_circulacion_por_vencer',
            'severity' => $daysUntil <= LicenseAlertService::CRITICAL_DAYS ? 'critica' : 'advertencia',
            'title' => "{$spec['label']} por vencer: {$plate}",
            'message' => "{$spec['article']} {$spec['phrase']} del móvil {$plate} vence el {$when} ({$daysUntil} días).",
        ];
    }

    /**
     * @param  array{type: string, severity: string, title: string, message: string}  $level
     */
    private function createAlert(Vehicle $vehicle, Certification $certification, array $level): void
    {
        Alert::create([
            'vehicle_id' => $vehicle->id,
            'maintenance_id' => null,
            'type' => $level['type'],
            'severity' => $level['severity'],
            'title' => $level['title'],
            'message' => $level['message'],
            'generated_at' => now(),
            'due_date' => $certification->expiration_date,
            'status' => 'pending',
            'metadata' => $this->metadata($vehicle, $certification),
        ]);
    }

    /**
     * @param  array{type: string, severity: string, title: string, message: string}  $level
     */
    private function updateAlert(Alert $alert, Vehicle $vehicle, Certification $certification, array $level, bool $clearSnooze): void
    {
        $dateChanged = $alert->due_date?->toDateString() !== $certification->expiration_date?->toDateString();
        $metadata = $this->metadata($vehicle, $certification);
        $metadata['last_reminder_slot'] = $alert->metadata['last_reminder_slot'] ?? null;
        $metadata['last_email_on'] = $alert->metadata['last_email_on'] ?? null;
        if ($clearSnooze && $dateChanged) {
            unset($metadata['last_reminder_slot'], $metadata['last_email_on']);
        }

        $payload = [
            'type' => $level['type'],
            'severity' => $level['severity'],
            'title' => $level['title'],
            'message' => $level['message'],
            'due_date' => $certification->expiration_date,
            'metadata' => $metadata,
        ];

        if ($clearSnooze && $dateChanged) {
            $payload['snoozed_until'] = null;
            $payload['snoozed_by_id'] = null;
            $payload['snoozed_reason'] = null;
        }

        $alert->update($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(Vehicle $vehicle, Certification $certification): array
    {
        $spec = self::DOCUMENT_TYPES[$certification->type];

        return [
            'key' => $this->key($vehicle->id, $certification->type),
            'document_type' => $certification->type,
            'document_name' => $spec['label'],
            'certification_id' => $certification->id,
            'license_plate' => $vehicle->license_plate,
        ];
    }

    private function key(int $vehicleId, string $type): string
    {
        return 'circulation_'.$vehicleId.'_'.$type;
    }

    /**
     * @return Collection<int, Alert>
     */
    private function openForVehicle(int $vehicleId): Collection
    {
        return $this->openCirculationAlerts()
            ->filter(fn (Alert $alert) => (int) $alert->vehicle_id === $vehicleId)
            ->values();
    }

    /**
     * @return Collection<int, Alert>
     */
    private function openCirculationAlerts(): Collection
    {
        return Alert::query()
            ->whereIn('type', self::TYPES)
            ->where('status', '!=', 'closed')
            ->orderBy('id')
            ->get();
    }

    private function closeOpenForVehicle(int $vehicleId): int
    {
        $closed = 0;
        foreach ($this->openForVehicle($vehicleId) as $alert) {
            $this->closeAlert($alert);
            $closed++;
        }

        return $closed;
    }

    private function retireGenericAlerts(int $vehicleId): void
    {
        $ids = Certification::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('type', array_keys(self::DOCUMENT_TYPES))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($ids === []) {
            return;
        }

        Alert::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('type', ['certificado_vencido', 'certificado_por_vencer'])
            ->where('status', '!=', 'closed')
            ->orderBy('id')
            ->get()
            ->each(function (Alert $alert) use ($ids) {
                $certificationId = (int) ($alert->metadata['certification_id'] ?? 0);
                if (in_array($certificationId, $ids, true)) {
                    $this->closeAlert($alert);
                }
            });
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
