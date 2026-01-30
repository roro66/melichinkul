<?php

return [
    'cierre_sin_evidencia' => 'No se puede cerrar un mantenimiento correctivo sin subir al menos una factura (PDF) o foto del repuesto instalado (JPG/PNG).',

    'types' => [
        'preventive' => 'Preventivo',
        'corrective' => 'Correctivo',
        'inspection' => 'Inspección',
    ],

    'statuses' => [
        'scheduled' => 'Programado',
        'in_progress' => 'En progreso',
        'completed' => 'Completado',
        'pending_approval' => 'Pendiente de aprobación',
        'cancelled' => 'Cancelado',
    ],
];
