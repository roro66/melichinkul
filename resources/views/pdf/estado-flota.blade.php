<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte estado de flota</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; }
        .kpi { display: inline-block; margin-right: 24px; margin-bottom: 8px; }
        .kpi strong { font-size: 14px; }
        .footer { margin-top: 24px; font-size: 9px; color: #888; }
    </style>
</head>
<body>
    <h1>Reporte estado de flota</h1>
    <div class="meta"><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</div>
    <div>
        <span class="kpi"><strong>Vehículos totales:</strong> {{ $stats['vehiculos_total'] }}</span>
        <span class="kpi"><strong>Activos:</strong> {{ $stats['vehiculos_activos'] }}</span>
        <span class="kpi"><strong>En mantenimiento:</strong> {{ $stats['vehiculos_mantenimiento'] }}</span>
        <span class="kpi"><strong>Alertas activas:</strong> {{ $stats['alertas_activas'] }}</span>
        <span class="kpi"><strong>Mantenimientos programados:</strong> {{ $stats['mantenimientos_programados'] }}</span>
        <span class="kpi"><strong>En proceso:</strong> {{ $stats['mantenimientos_en_proceso'] }}</span>
    </div>
    <h2 style="font-size: 13px; margin-top: 20px;">Resumen por estado de vehículos</h2>
    <table>
        <thead><tr><th>Estado</th><th>Cantidad</th></tr></thead>
        <tbody>
            <tr><td>Activos</td><td>{{ $stats['vehiculos_activos'] }}</td></tr>
            <tr><td>En mantenimiento</td><td>{{ $stats['vehiculos_mantenimiento'] }}</td></tr>
            <tr><td>Inactivos</td><td>{{ $stats['vehiculos_inactivos'] }}</td></tr>
        </tbody>
    </table>
    <div class="footer">{{ config('app.name') }} — Documento generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
