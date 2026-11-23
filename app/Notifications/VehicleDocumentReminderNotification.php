<?php

namespace App\Notifications;

use App\Models\Alert;
use App\Services\LicenseAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VehicleDocumentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, Alert>  $alerts
     * @param  list<array{title: string, message: string|null, due_date: string|null, url: string}>  $items
     */
    public function __construct(
        public array $alerts,
        public array $items,
        public string $slot,
        public bool $mailOnly = false,
    ) {}

    public function via(object $notifiable): array
    {
        if ($this->mailOnly) {
            return ($notifiable->email_notifications ?? true) ? ['mail'] : [];
        }

        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $count = count($this->items);

        return [
            'reminder_kind' => 'circulation',
            'slot' => $this->slot,
            'count' => $count,
            'message' => $count === 1
                ? 'Hay 1 documento de circulación por vencer o vencido.'
                : 'Hay '.$count.' documentos de circulación por vencer o vencidos.',
            'items' => $this->items,
            'url' => route('alerts.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->items);
        $message = (new MailMessage)
            ->subject($count === 1
                ? 'Documento de circulación por vencer - '.config('app.name')
                : $count.' documentos de circulación por vencer - '.config('app.name'))
            ->greeting('Hola '.($notifiable->name ?: '').',')
            ->line($count === 1
                ? 'Hay un documento que impide o va a impedir la circulación de un móvil.'
                : 'Hay '.$count.' documentos que impiden o van a impedir la circulación de un móvil.')
            ->line('El correo sigue el plazo que falta: una vez por semana (lunes) si faltan entre 31 y 60 días; tres veces por semana (lunes, miércoles y viernes) si faltan entre 16 y 30 días; y todos los días si faltan 15 días o menos, o el documento ya venció.')
            ->line('Se deja de enviar si un administrador aplaza el caso (máximo una semana) o si se carga el documento vigente.')
            ->action('Ver alertas', route('alerts.index'));

        foreach ($this->alerts as $alert) {
            if (! $alert instanceof Alert) {
                continue;
            }
            $context = $alert->vehicle
                ? $alert->vehicle->license_plate.' - '.$alert->vehicle->brand.' '.$alert->vehicle->model
                : ($alert->metadata['license_plate'] ?? 'Móvil');
            $message->line('• '.$alert->title)
                ->line('  '.$context.($alert->due_date ? ' — '.$alert->due_date->format('d/m/Y') : ''))
                ->line('  '.($alert->message ?: ''));
        }

        $message->line('Para cerrar el caso hay que dejar el documento vigente, con una fecha de vencimiento a más de '.LicenseAlertService::WARNING_DAYS.' días. Aplica al permiso de circulación, la revisión técnica, el SOAP y el análisis de gases.');

        return $message;
    }
}
