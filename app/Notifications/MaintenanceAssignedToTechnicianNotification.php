<?php

namespace App\Notifications;

use App\Models\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceAssignedToTechnicianNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Maintenance $maintenance
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];
        if ($notifiable->email_notifications ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vehicle = $this->maintenance->vehicle;
        $context = $vehicle ? $vehicle->license_plate.' - '.($vehicle->brand ?? '').' '.($vehicle->model ?? '') : '—';
        $fecha = $this->maintenance->scheduled_date?->format('d/m/Y') ?? 'Sin fecha';
        $tipo = match ($this->maintenance->type) {
            'preventive' => 'Preventivo',
            'corrective' => 'Correctivo',
            default => 'Inspección',
        };

        return (new MailMessage)
            ->subject('Nuevo mantenimiento asignado - '.config('app.name'))
            ->line('Se te ha asignado un nuevo mantenimiento.')
            ->line('Vehículo: '.$context)
            ->line('Tipo: '.$tipo.' · Fecha programada: '.$fecha)
            ->line('Descripción: '.($this->maintenance->work_description ?: '—'))
            ->action('Ver mantenimiento', route('mantenimientos.show', $this->maintenance->id));
    }

    public function toArray(object $notifiable): array
    {
        $vehicle = $this->maintenance->vehicle;
        $context = $vehicle ? $vehicle->license_plate.' - '.$vehicle->brand.' '.$vehicle->model : '—';
        $fecha = $this->maintenance->scheduled_date?->format('d/m/Y') ?? 'Sin fecha';

        return [
            'type' => 'maintenance_assigned_technician',
            'maintenance_id' => $this->maintenance->id,
            'vehicle_context' => $context,
            'scheduled_date' => $this->maintenance->scheduled_date?->toDateString(),
            'message' => 'Te asignaron un mantenimiento: '.$context.' ('.$fecha.')',
            'url' => route('mantenimientos.show', $this->maintenance->id),
        ];
    }
}
