<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaVehiculo extends Model
{
    protected $table = 'categorias_vehiculos';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'contador_principal',
        'criticidad_default',
        'requiere_certificaciones',
        'requiere_certificaciones_especiales',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'requiere_certificaciones' => 'boolean',
            'requiere_certificaciones_especiales' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'categoria_id');
    }
}
