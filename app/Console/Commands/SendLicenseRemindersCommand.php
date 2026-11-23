<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Driver;
use App\Models\User;
use App\Notifications\DriverLicenseReminderNotification;
use App\Notifications\LicenseExpirationReminderNotification;
use App\Services\LicenseAlertService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SendLicenseRemindersCommand extends Command
{
    protected $signature = 'alerts:license-reminders';

    protected $description = 'On-screen license reminders each work slot, and email on the cadence for each expiration.';

    public function handle(LicenseAlertService $licenses): int
    {
        $slot = $licenses->currentBusinessSlot();
        if ($slot === null) {
            $this->info('Fuera del horario laboral de avisos de licencia.');

            return self::SUCCESS;
        }

        $pending = $licenses->pendingReminders();
        if ($pending->isEmpty()) {
            $this->info('Sin licencias pendientes de aviso.');

            return self::SUCCESS;
        }

        $admins = User::licenseReminderRecipients();
        $screenDue = $pending->filter(function (Alert $alert) use ($slot) {
            return ($alert->metadata['last_reminder_slot'] ?? null) !== $slot;
        })->values();

        if ($screenDue->isNotEmpty()) {
            $items = $licenses->reminderItems($screenDue);
            $uniqueAlerts = $this->uniqueByDriver($screenDue);
            foreach ($admins as $admin) {
                $admin->notify(new LicenseExpirationReminderNotification($uniqueAlerts, $items, $slot));
            }
            $licenses->markReminderSlot($screenDue, $slot);
        }

        $today = Carbon::today();
        $emailDue = $pending->filter(fn (Alert $alert) => $licenses->shouldEmailToday($alert, $today))->values();
        $driversMailed = 0;

        if ($emailDue->isNotEmpty()) {
            $emailItems = $licenses->reminderItems($emailDue);
            $emailAlerts = $this->uniqueByDriver($emailDue);
            foreach ($admins as $admin) {
                $admin->notify(new LicenseExpirationReminderNotification($emailAlerts, $emailItems, $slot.'-correo', true));
            }

            $byDriver = $emailDue->groupBy(fn (Alert $alert) => (string) ($alert->metadata['driver_id'] ?? ''));
            foreach ($byDriver as $driverId => $group) {
                if ($driverId === '' || ! ctype_digit((string) $driverId)) {
                    continue;
                }
                $driver = Driver::query()->find((int) $driverId);
                if (! $driver || ! filter_var($driver->email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }
                $alert = $group->first();
                Notification::route('mail', $driver->email)->notify(new DriverLicenseReminderNotification(
                    $driver->full_name ?: 'Conductor',
                    $alert->message ?: $alert->title,
                    $alert->due_date?->format('d/m/Y'),
                    $licenses->emailCadenceText($alert, $today),
                ));
                $driversMailed++;
            }

            $licenses->markEmailSent($emailDue, $today);
        }

        $this->info('Aviso en pantalla: '.$screenDue->count().' alerta(s). Correo: '.$admins->count().' administrador(es) y '.$driversMailed.' conductor(es).');

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, Alert>  $alerts
     * @return array<int, Alert>
     */
    private function uniqueByDriver(Collection $alerts): array
    {
        return $alerts
            ->unique(fn (Alert $alert) => (string) ($alert->metadata['driver_id'] ?? 'alert-'.$alert->id))
            ->values()
            ->all();
    }
}
