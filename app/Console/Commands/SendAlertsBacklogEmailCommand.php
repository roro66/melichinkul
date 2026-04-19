<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\User;
use App\Notifications\CriticalAlertsBacklogDigestNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendAlertsBacklogEmailCommand extends Command
{
    protected $signature = 'alerts:email-backlog
                            {--dry-run : Mostrar conteos sin enviar correos}
                            {--force : Enviar sin pedir confirmación (recomendado con --no-interaction)}
                            {--chunk=40 : Máximo de alertas por cada correo}
                            {--sleep=3 : Segundos de pausa entre cada correo (SMTP / reputación)}';

    protected $description = 'Envía por correo un resumen de las alertas críticas ya existentes y pendientes (retroactivo), a los mismos destinatarios que el digest diario.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $sleepSeconds = max(0, (float) $this->option('sleep'));

        $alerts = Alert::query()
            ->active()
            ->where('severity', 'critica')
            ->where(function ($q) {
                $q->whereNull('snoozed_until')
                    ->orWhere('snoozed_until', '<=', now());
            })
            ->with(['vehicle', 'sparePart'])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        if ($alerts->isEmpty()) {
            $this->info('No hay alertas críticas pendientes (no cerradas y no pospuestas) para enviar.');

            return self::SUCCESS;
        }

        $recipients = User::criticalAlertDigestRecipients();
        if ($recipients->isEmpty()) {
            $this->warn('No hay destinatarios (administrador o supervisor activo, email @sover.cl).');

            return self::FAILURE;
        }

        $this->line('Destinatarios detectados: ' . $recipients->map(fn (User $u) => $u->email . ' (rol efectivo: ' . $u->rol . ')')->implode(', '));

        $chunks = $alerts->chunk($chunkSize);
        $partTotal = $chunks->count();
        $mailRecipients = $recipients->filter(fn (User $u) => $u->email_notifications ?? true);
        if ($mailRecipients->isEmpty()) {
            $this->warn('Ningún destinatario tiene «Recibir notificaciones por email» activo; no se enviarían correos.');
        }

        $totalMails = $mailRecipients->count() * $partTotal;

        $this->info("Alertas críticas a incluir: {$alerts->count()} (en {$partTotal} parte(s) de hasta {$chunkSize} alertas).");
        $this->info("Destinatarios con rol y dominio @sover.cl: {$recipients->count()}. Con correo activado: {$mailRecipients->count()}.");
        $this->info("Correos SMTP previstos: {$totalMails} (solo canal mail; sin duplicar en la campana de la app).");

        if ($dryRun) {
            $this->warn('Modo --dry-run: no se envió nada. Quite --dry-run para ejecutar.');

            return self::SUCCESS;
        }

        if ($mailRecipients->isEmpty()) {
            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('¿Enviar ahora los correos retroactivos?', true)) {
            $this->warn('Cancelado.');

            return self::FAILURE;
        }

        $sent = 0;
        $partIndex = 0;
        foreach ($chunks as $chunk) {
            $partIndex++;
            $batch = $chunk->all();

            foreach ($mailRecipients as $user) {
                Notification::sendNow($user, new CriticalAlertsBacklogDigestNotification(
                    $batch,
                    $partIndex,
                    $partTotal
                ));
                $sent++;
                if ($sleepSeconds > 0) {
                    usleep((int) ($sleepSeconds * 1_000_000));
                }
            }
        }

        $this->info("Listo. Se enviaron {$sent} correos (sendNow, por destinatario y parte).");

        return self::SUCCESS;
    }
}
