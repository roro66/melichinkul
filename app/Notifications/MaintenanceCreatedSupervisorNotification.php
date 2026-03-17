<?php

namespace App\Notifications;

use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceCreatedSupervisorNotification extends Notification implements ShouldQueue
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
        $technician = $this->maintenance->responsibleTechnician;
        $techName = $technician instanceof User ? $technician->name : '—';

        return (new MailMessage)
            ->subject('Nuevo mantenimiento creado - '.config('app.name'))
            ->line('Se ha registrado un nuevo mantenimiento en el sistema.')
            ->line('Vehículo: '.$context)
            ->line('Técnico asignado: '.$techName)
            ->line('Fecha programada: '.$fecha)
            ->line('Descripción: '.($this->maintenance->work_description ?: '—'))
            ->action('Ver mantenimiento', route('mantenimientos.show', $this->maintenance->id));
    }

    public function toArray(object $notifiable): array
    {
        $vehicle = $this->maintenance->vehicle;
        $context = $vehicle ? $vehicle->license_plate.' - '.$vehicle->brand.' '.$vehicle->model : '—';
        $technician = $this->maintenance->responsibleTechnician;
        $techName = $technician instanceof User ? $technician->name : '—';

        return [
            'type' => 'maintenance_created_supervisor',
            'maintenance_id' => $this->maintenance->id,
            'vehicle_context' => $context,
            'technician_name' => $techName,
            'message' => 'Nuevo mantenimiento: '.$context.' · Técnico: '.$techName,
            'url' => route('mantenimientos.show', $this->maintenance->id),
        ];
    }
}
