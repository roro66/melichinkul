<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $stats,
        public int $costoMes,
        public string $mesLabel
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reporte mensual de flota - ' . $this->mesLabel . ' - ' . config('app.name'))
            ->greeting('Hola ' . ($notifiable->name ?: '') . ',')
            ->line('Resumen del mes de ' . $this->mesLabel . ':')
            ->line('')
            ->line('**Vehículos:** ' . ($this->stats['vehiculos_total'] ?? 0) . ' total, ' . ($this->stats['vehiculos_activos'] ?? 0) . ' activos, ' . ($this->stats['vehiculos_mantenimiento'] ?? 0) . ' en mantenimiento.')
            ->line('**Mantenimientos:** ' . ($this->stats['mantenimientos_programados'] ?? 0) . ' programados, ' . ($this->stats['mantenimientos_en_proceso'] ?? 0) . ' en proceso, ' . ($this->stats['mantenimientos_completados_mes'] ?? 0) . ' completados.')
            ->line('**Costo del mes:** $' . number_format($this->costoMes, 0, ',', '.'))
            ->line('**Alertas activas:** ' . ($this->stats['alertas_activas'] ?? 0) . ' (' . ($this->stats['alertas_criticas'] ?? 0) . ' críticas).')
            ->line('')
            ->action('Ver reportes en el sistema', route('reportes.index'))
            ->line('Gracias por usar ' . config('app.name') . '.');
    }
}
