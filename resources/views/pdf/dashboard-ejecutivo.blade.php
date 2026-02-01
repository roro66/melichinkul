<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Dashboard ejecutivo</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 20px; }
        .kpi-box { border: 1px solid #ddd; padding: 12px; margin-bottom: 12px; display: inline-block; width: 30%; min-width: 140px; vertical-align: top; }
        .kpi-box .label { font-size: 9px; text-transform: uppercase; color: #666; }
        .kpi-box .value { font-size: 18px; font-weight: bold; margin-top: 4px; }
        .footer { margin-top: 24px; font-size: 9px; color: #888; }
    </style>
</head>
<body>
    <h1>Dashboard ejecutivo</h1>
    <div class="meta"><strong>Período:</strong> {{ now()->format('d/m/Y') }} — Generado: {{ now()->format('d/m/Y H:i') }}</div>
    <div>
        <div class="kpi-box"><div class="label">Total vehículos</div><div class="value">{{ $stats['vehiculos_total'] }}</div><div class="label">{{ $stats['vehiculos_activos'] }} activos</div></div>
        <div class="kpi-box"><div class="label">Mantenimientos en proceso</div><div class="value">{{ $stats['mantenimientos_en_proceso'] }}</div><div class="label">{{ $stats['mantenimientos_programados'] }} programados</div></div>
        <div class="kpi-box"><div class="label">Costo del mes</div><div class="value">${{ number_format($costo_mes, 0, ',', '.') }}</div><div class="label">{{ $stats['mantenimientos_completados_mes'] }} completados</div></div>
        <div class="kpi-box"><div class="label">Vehículos en mantenimiento</div><div class="value">{{ $stats['vehiculos_mantenimiento'] }}</div></div>
        <div class="kpi-box"><div class="label">Alertas activas</div><div class="value">{{ $stats['alertas_activas'] }}</div><div class="label">{{ $stats['alertas_criticas'] }} críticas</div></div>
    </div>
    <div class="footer">{{ config('app.name') }} — Documento generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
