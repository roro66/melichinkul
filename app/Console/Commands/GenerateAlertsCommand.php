<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Certification;
use App\Models\Driver;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\CriticalAlertsDigestNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAlertsCommand extends Command
{
    protected $signature = 'alerts:generate';

    protected $description = 'Generate alerts for expiring certifications, driver licenses, and overdue maintenances.';

    public function handle(): int
    {
        $today = Carbon::today();
        $count = 0;
        $criticalAlertIds = [];

        // Certifications expiring or expired (required + active only)
        $certifications = Certification::where('required', true)
            ->where('active', true)
            ->with('vehicle')
            ->get();

        foreach ($certifications as $cert) {
            $due = $cert->expiration_date;
            if (! $due) {
                continue;
            }
            $daysUntil = $today->diffInDays($due, false);
            $vehicleId = $cert->vehicle_id;
            $name = $cert->name ?: $cert->type;

            $key = 'certification_' . $cert->id;
            if ($daysUntil < 0) {
                [$n, $id] = $this->createAlertOnce($vehicleId, 'certificado_vencido', 'critica',
                    "Documento vencido: {$name}",
                    "El documento {$name} venció el " . $due->format('d/m/Y') . ".",
                    $due, ['key' => $key, 'certification_id' => $cert->id], null);
                $count += $n;
                if ($id !== null) {
                    $criticalAlertIds[] = $id;
                }
            } elseif ($daysUntil <= 15) {
                [$n, $id] = $this->createAlertOnce($vehicleId, 'certificado_por_vencer', 'critica',
                    "Documento por vencer: {$name}",
                    "El documento {$name} vence el " . $due->format('d/m/Y') . " (" . $daysUntil . " días).",
                    $due, ['key' => $key, 'certification_id' => $cert->id], null);
                $count += $n;
                if ($id !== null) {
                    $criticalAlertIds[] = $id;
                }
            } elseif ($daysUntil <= 60) {
                $count += $this->createAlertOnce($vehicleId, 'certificado_por_vencer', 'advertencia',
                    "Documento por vencer: {$name}",
                    "El documento {$name} vence el " . $due->format('d/m/Y') . " (" . $daysUntil . " días).",
                    $due, ['key' => $key, 'certification_id' => $cert->id], null)[0];
            }
        }

        // Driver licenses expiring or expired (per vehicle assigned to that driver)
        $drivers = Driver::where('active', true)->get();
        foreach ($drivers as $driver) {
            $exp = $driver->license_expiration_date;
            if (! $exp) {
                continue;
            }
            $daysUntil = $today->diffInDays($exp, false);
            $driverName = $driver->full_name ?? 'Conductor';

            $key = 'driver_' . $driver->id;
            $vehicles = Vehicle::where('current_driver_id', $driver->id)->get();
            foreach ($vehicles as $vehicle) {
                if ($daysUntil < 0) {
                    [$n, $id] = $this->createAlertOnce($vehicle->id, 'licencia_vencida', 'critica',
                        "Licencia vencida: {$driverName}",
                        "La licencia del conductor {$driverName} venció el " . $exp->format('d/m/Y') . ".",
                        $exp, ['key' => $key, 'driver_id' => $driver->id], null);
                    $count += $n;
                    if ($id !== null) {
                        $criticalAlertIds[] = $id;
                    }
                } elseif ($daysUntil <= 15) {
                    [$n, $id] = $this->createAlertOnce($vehicle->id, 'licencia_por_vencer', 'critica',
                        "Licencia por vencer: {$driverName}",
                        "La licencia del conductor {$driverName} vence el " . $exp->format('d/m/Y') . " (" . $daysUntil . " días).",
                        $exp, ['key' => $key, 'driver_id' => $driver->id], null);
                    $count += $n;
                    if ($id !== null) {
                        $criticalAlertIds[] = $id;
                    }
                } elseif ($daysUntil <= 60) {
                    $count += $this->createAlertOnce($vehicle->id, 'licencia_por_vencer', 'advertencia',
                        "Licencia por vencer: {$driverName}",
                        "La licencia del conductor {$driverName} vence el " . $exp->format('d/m/Y') . " (" . $daysUntil . " días).",
                        $exp, ['key' => $key, 'driver_id' => $driver->id], null)[0];
                }
            }
        }

        // Overdue maintenances (scheduled in the past, not completed)
        $overdue = Maintenance::with('vehicle')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where('scheduled_date', '<', $today)
            ->get();

        foreach ($overdue as $m) {
            [$n, $id] = $this->createAlertOnce(
                $m->vehicle_id,
                'mantenimiento_vencido',
                'critica',
                'Mantenimiento vencido',
                'Mantenimiento programado para el ' . $m->scheduled_date->format('d/m/Y') . ' no realizado.',
                $m->scheduled_date,
                ['key' => 'maintenance_' . $m->id, 'maintenance_id' => $m->id],
                $m->id
            );
            $count += $n;
            if ($id !== null) {
                $criticalAlertIds[] = $id;
            }
        }

        if (! empty($criticalAlertIds)) {
            $alerts = Alert::with('vehicle')->whereIn('id', $criticalAlertIds)->orderBy('due_date')->get();
            $recipients = User::whereIn('role', ['administrator', 'supervisor'])
                ->where('active', true)
                ->whereNotNull('email')
                ->get();
            foreach ($recipients as $user) {
                $user->notify(new CriticalAlertsDigestNotification($alerts->all()));
            }
        }

        $this->info("Generated {$count} alert(s).");

        return self::SUCCESS;
    }

    /**
     * @return array{0: int, 1: int|null} [count 0|1, created alert id or null]
     */
    private function createAlertOnce(
        int $vehicleId,
        string $type,
        string $severity,
        string $title,
        string $message,
        $dueDate,
        array $metadata,
        ?int $maintenanceId
    ): array {
        $refKey = $metadata['key'] ?? null;
        if ($refKey !== null) {
            $exists = Alert::where('vehicle_id', $vehicleId)
                ->where('type', $type)
                ->where('status', '!=', 'closed')
                ->whereRaw("metadata->>'key' = ?", [$refKey])
                ->exists();
            if ($exists) {
                return [0, null];
            }
        }

        $alert = Alert::create([
            'vehicle_id' => $vehicleId,
            'maintenance_id' => $maintenanceId,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'generated_at' => now(),
            'due_date' => $dueDate,
            'status' => 'pending',
            'metadata' => $metadata,
        ]);

        return [1, $alert->id];
    }
}
