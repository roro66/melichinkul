<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalAlertsDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, Alert>  $alerts
     */
    public function __construct(
        public array $alerts
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->alerts);
        $subject = $count === 1
            ? 'Alerta crítica generada - ' . config('app.name')
            : "{$count} alertas críticas generadas - " . config('app.name');

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hola ' . ($notifiable->name ?: '') . ',')
            ->line('Se ha(n) generado ' . $count . ' alerta(s) crítica(s) en el sistema de gestión de flota.')
            ->line('')
            ->action('Ver alertas', route('alerts.index'));

        foreach ($this->alerts as $alert) {
            $context = $alert->vehicle
                ? $alert->vehicle->license_plate . ' - ' . $alert->vehicle->brand . ' ' . $alert->vehicle->model
                : ($alert->sparePart ? $alert->sparePart->code . ' - ' . $alert->sparePart->description : '—');
            $message->line('• **' . $alert->title . '**')
                ->line('  ' . $context . ($alert->due_date ? ' — ' . $alert->due_date->format('d/m/Y') : ''))
                ->line('  ' . $alert->message)
                ->line('');
        }

        $message->line('Revisa el módulo de alertas para cerrar o posponer según corresponda.');

        return $message;
    }
}
