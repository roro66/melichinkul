<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverVehicleDocumentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public string $driverName,
        public array $lines,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Documentos del móvil por vencer - '.config('app.name'))
            ->greeting('Hola '.$this->driverName.',')
            ->line('Hay documentos del móvil que impiden o van a impedir circular:');

        foreach ($this->lines as $line) {
            $message->line('• '.$line);
        }

        return $message
            ->line('El aviso sale una vez por semana si faltan más de 30 días, tres veces por semana si faltan entre 16 y 30, y todos los días si faltan 15 días o menos o el documento ya venció.')
            ->line('Entrega el documento vigente para que quede cargado en el sistema. El correo se detiene si un administrador aplaza el caso (máximo una semana) o registra el documento nuevo.');
    }
}
