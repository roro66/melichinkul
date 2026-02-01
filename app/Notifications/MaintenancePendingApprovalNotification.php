<?php

namespace App\Notifications;

use App\Models\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenancePendingApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Maintenance $maintenance
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vehicle = $this->maintenance->vehicle;
        $context = $vehicle ? $vehicle->license_plate . ' - ' . $vehicle->brand . ' ' . $vehicle->model : '—';

        return (new MailMessage)
            ->subject('Mantenimiento pendiente de aprobación - ' . config('app.name'))
            ->line('Un mantenimiento ha superado el umbral de costo y quedó pendiente de aprobación.')
            ->line('Vehículo: ' . $context)
            ->line('Costo total: $' . number_format($this->maintenance->total_cost ?? 0, 0, ',', '.'))
            ->action('Ver mantenimiento', route('mantenimientos.show', $this->maintenance->id));
    }

    public function toArray(object $notifiable): array
    {
        $vehicle = $this->maintenance->vehicle;
        $context = $vehicle ? $vehicle->license_plate . ' - ' . $vehicle->brand . ' ' . $vehicle->model : '—';

        return [
            'type' => 'maintenance_pending_approval',
            'maintenance_id' => $this->maintenance->id,
            'vehicle_context' => $context,
            'total_cost' => $this->maintenance->total_cost,
            'message' => 'Mantenimiento pendiente de aprobación: ' . $context,
            'url' => route('mantenimientos.show', $this->maintenance->id),
        ];
    }
}
