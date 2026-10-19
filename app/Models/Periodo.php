<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'periodo';

    // Llave primaria de la tabla.
    protected $primaryKey = 'id_periodo';

    public $timestamps = false;

    // Campos
    protected $fillable = [
        'estacion',
        'anio',
    ];

    /**
     * Descripción: Define los tipos de datos del modelo.
     * Entradas: Ninguna.
     * Salidas: Identificador y año tratados como enteros.
     */
    protected function casts(): array
    {
        return [
            'id_periodo' => 'integer',
            'anio' => 'integer',
        ];
    }

    /**
     * Descripción: Obtiene los grupos asociados al periodo.
     * Entradas: Ninguna.
     * Salidas: Relación con los grupos académicos.
     */
    public function grupos(): HasMany
    {
        return $this->hasMany(
            Grupo::class,
            'id_periodo',
            'id_periodo'
        );
    }
}
