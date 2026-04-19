<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mismo contenido que el digest crítico, pero envío síncrono (sin cola) para envíos masivos puntuales.
 */
class CriticalAlertsBacklogDigestNotification extends Notification
{
    /**
     * @param  array<int, Alert>  $alerts
     */
    public function __construct(
        public array $alerts,
        public int $partIndex = 1,
        public int $partTotal = 1,
    ) {}

    /** Solo correo: evita llenar la campana con decenas de entradas por el envío retroactivo. */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->alerts);
        $partLabel = $this->partTotal > 1 ? " (parte {$this->partIndex} de {$this->partTotal})" : '';
        $subject = $count === 1
            ? 'Alertas críticas pendientes — resumen' . $partLabel . ' — ' . config('app.name')
            : "{$count} alertas críticas pendientes — resumen" . $partLabel . ' — ' . config('app.name');

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hola ' . ($notifiable->name ?: '') . ',')
            ->line('Este es un envío **retroactivo** con alertas críticas que siguen **pendientes** en el sistema (no se habían notificado por correo en su momento).')
            ->line('Incluye ' . $count . ' alerta(s) en este mensaje.')
            ->line('')
            ->action('Ver módulo de alertas', route('alerts.index'));

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
