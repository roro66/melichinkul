<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverLicenseReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $driverName,
        public string $detail,
        public ?string $dueDate,
        public string $cadence = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Tu licencia de conducir está por vencer - '.config('app.name'))
            ->greeting('Hola '.$this->driverName.',')
            ->line($this->detail);

        if ($this->dueDate) {
            $message->line('Fecha de vencimiento registrada: '.$this->dueDate.'.');
        }

        if ($this->cadence !== '') {
            $message->line($this->cadence);
        }

        return $message
            ->line('Entrega la licencia vigente para que quede cargada en el sistema. El correo se detiene si un administrador aplaza el caso (máximo una semana) o registra el documento nuevo.');
    }
}
