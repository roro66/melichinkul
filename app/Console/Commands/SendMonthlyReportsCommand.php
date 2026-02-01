<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\MonthlyReportNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMonthlyReportsCommand extends Command
{
    protected $signature = 'reports:send-monthly';

    protected $description = 'Envía por email el reporte mensual a administradores y supervisores (día 1 del mes).';

    public function handle(): int
    {
        $stats = [
            'vehiculos_total' => Vehicle::count(),
            'vehiculos_activos' => Vehicle::where('status', 'active')->count(),
            'vehiculos_mantenimiento' => Vehicle::where('status', 'maintenance')->count(),
            'vehiculos_inactivos' => Vehicle::where('status', 'inactive')->count(),
            'mantenimientos_programados' => Maintenance::where('status', 'scheduled')->count(),
            'mantenimientos_en_proceso' => Maintenance::where('status', 'in_progress')->count(),
            'mantenimientos_completados_mes' => Maintenance::where('status', 'completed')
                ->whereMonth('end_date', now()->month)
                ->whereYear('end_date', now()->year)
                ->count(),
            'alertas_activas' => Alert::where('status', '!=', 'closed')->count(),
            'alertas_criticas' => Alert::where('status', '!=', 'closed')->where('severity', 'critica')->count(),
        ];

        $costoMes = (int) Maintenance::where('status', 'completed')
            ->whereMonth('end_date', now()->month)
            ->whereYear('end_date', now()->year)
            ->sum('total_cost');

        $mesLabel = Carbon::now()->locale('es')->translatedFormat('F Y');

        $recipients = User::role(['administrator', 'supervisor'])
            ->where('active', true)
            ->where(function ($q) {
                $q->where('email_notifications', true)->orWhereNull('email_notifications');
            })
            ->get();

        foreach ($recipients as $user) {
            if (! $user->email) {
                continue;
            }
            try {
                $user->notify(new MonthlyReportNotification($stats, $costoMes, $mesLabel));
                $this->info('Enviado a: ' . $user->email);
            } catch (\Throwable $e) {
                $this->warn('Error enviando a ' . $user->email . ': ' . $e->getMessage());
            }
        }

        $this->info('Reporte mensual enviado a ' . $recipients->count() . ' destinatario(s).');
        return self::SUCCESS;
    }
}
