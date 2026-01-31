<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DriverDocument extends Model
{
    protected $fillable = [
        'driver_id',
        'name',
        'file_path',
        'original_name',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function isPdf(): bool
    {
        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function isImage(): bool
    {
        return in_array(
            strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
        );
    }

    /**
     * Elimina el archivo físico al borrar el registro.
     */
    protected static function booted(): void
    {
        static::deleting(function (DriverDocument $document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
        });
    }
}
