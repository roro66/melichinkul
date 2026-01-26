<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'tipo',
        'nombre',
        'archivo_path',
        'nombre_archivo',
        'mime_type',
        'tamano',
        'fecha_emision',
        'fecha_vencimiento',
        'obligatorio',
        'observaciones',
        'subido_por_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'obligatorio' => 'boolean',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por_id');
    }
}
