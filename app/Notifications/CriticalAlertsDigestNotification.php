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
        return ['mail', 'database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $alertIds = array_map(fn (Alert $a) => $a->id, $this->alerts);
        $titles = array_map(fn (Alert $a) => $a->title, $this->alerts);

        return [
            'type' => 'critical_alerts_digest',
            'alert_ids' => $alertIds,
            'count' => count($this->alerts),
            'message' => count($this->alerts) === 1
                ? 'Se ha generado 1 alerta crítica.'
                : 'Se han generado ' . count($this->alerts) . ' alertas críticas.',
            'titles' => $titles,
            'url' => route('alerts.index'),
        ];
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
