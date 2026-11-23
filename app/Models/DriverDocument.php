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
        'kind',
        'expiration_date',
        'file_path',
        'original_name',
        'reverse_file_path',
        'reverse_original_name',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
        ];
    }

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

    public function hasReverse(): bool
    {
        return filled($this->reverse_file_path);
    }

    public static function kindCanHaveReverse(?string $kind): bool
    {
        return in_array($kind, ['carnet', 'licencia'], true);
    }

    /**
     * Elimina el archivo físico al borrar el registro.
     */
    protected static function booted(): void
    {
        static::deleting(function (DriverDocument $document) {
            foreach ([$document->file_path, $document->reverse_file_path] as $path) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }
}
