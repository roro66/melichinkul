<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\User;
use App\Notifications\DriverVehicleDocumentReminderNotification;
use App\Notifications\VehicleDocumentReminderNotification;
use App\Services\VehicleCirculationAlertService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendVehicleDocumentRemindersCommand extends Command
{
    protected $signature = 'alerts:vehicle-document-reminders';

    protected $description = 'On-screen circulation-document reminders each work slot, and email on the same cadence as driver licenses.';

    public function handle(VehicleCirculationAlertService $documents): int
    {
        $slot = $documents->currentBusinessSlot();
        if ($slot === null) {
            $this->info('Fuera del horario laboral de avisos de documentos.');

            return self::SUCCESS;
        }

        $pending = $documents->pendingReminders();
        if ($pending->isEmpty()) {
            $this->info('Sin documentos de circulación pendientes de aviso.');

            return self::SUCCESS;
        }

        $admins = User::licenseReminderRecipients();
        $screenDue = $pending->filter(function (Alert $alert) use ($slot) {
            return ($alert->metadata['last_reminder_slot'] ?? null) !== $slot;
        })->values();

        if ($screenDue->isNotEmpty()) {
            $items = $documents->reminderItems($screenDue);
            foreach ($admins as $admin) {
                $admin->notify(new VehicleDocumentReminderNotification($screenDue->all(), $items, $slot));
            }
            $documents->markReminderSlot($screenDue, $slot);
        }

        $today = Carbon::today();
        $emailDue = $pending->filter(fn (Alert $alert) => $documents->shouldEmailToday($alert, $today))->values();
        $driversMailed = 0;

        if ($emailDue->isNotEmpty()) {
            $emailItems = $documents->reminderItems($emailDue);
            foreach ($admins as $admin) {
                $admin->notify(new VehicleDocumentReminderNotification($emailDue->all(), $emailItems, $slot.'-correo', true));
            }

            $byDriver = $emailDue->groupBy(fn (Alert $alert) => (string) ($alert->vehicle?->current_driver_id ?? ''));
            foreach ($byDriver as $driverId => $group) {
                if ($driverId === '' || ! ctype_digit((string) $driverId)) {
                    continue;
                }
                $driver = $group->first()?->vehicle?->currentDriver;
                if (! $driver || ! filter_var($driver->email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }
                Notification::route('mail', $driver->email)->notify(new DriverVehicleDocumentReminderNotification(
                    $driver->full_name ?: 'Conductor',
                    $group->map(fn (Alert $alert) => $alert->message ?: $alert->title)->values()->all(),
                ));
                $driversMailed++;
            }

            $documents->markEmailSent($emailDue, $today);
        }

        $this->info('Aviso en pantalla: '.$screenDue->count().' alerta(s). Correo: '.$admins->count().' administrador(es) y '.$driversMailed.' conductor(es).');

        return self::SUCCESS;
    }
}
