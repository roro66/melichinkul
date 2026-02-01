<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial de mantenimientos - {{ $vehicle->license_plate }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; }
        .text-right { text-align: right; }
        .footer { margin-top: 24px; font-size: 9px; color: #888; }
    </style>
</head>
<body>
    <h1>Historial de mantenimientos</h1>
    <div class="meta">
        <strong>Vehículo:</strong> {{ $vehicle->license_plate }} — {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year ?? '—' }})<br>
        <strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Fecha prog.</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Descripción</th>
                <th class="text-right">Costo total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($maintenances as $m)
            <tr>
                <td>{{ $m->scheduled_date ? $m->scheduled_date->format('d/m/Y') : '—' }}</td>
                <td>{{ __('mantenimiento.types.' . $m->type, [], 'es') }}</td>
                <td>{{ __('mantenimiento.statuses.' . $m->status, [], 'es') }}</td>
                <td>{{ Str::limit($m->work_description, 50) }}</td>
                <td class="text-right">${{ number_format($m->total_cost ?? 0, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5">Sin mantenimientos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($maintenances->isNotEmpty())
    <p style="margin-top: 12px;"><strong>Total completados (costo):</strong> ${{ number_format($maintenances->where('status', 'completed')->sum('total_cost'), 0, ',', '.') }}</p>
    @endif
    <div class="footer">{{ config('app.name') }} — Documento generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
