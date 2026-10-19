<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grupo extends Model
{
    // Nombre de la tabla en la base de datos.
    protected $table = 'grupo';

    // Llave primaria de la tabla.
    protected $primaryKey = 'id_grupo';

    public $timestamps = false;

    // Campos 
    protected $fillable = [
        'nombreGrupo',
        'cuatrimestre',
        'id_periodo',
        'id_carrera',
    ];

    /**
     * Descripción: Define los tipos de datos del modelo.
     * Entradas: Ninguna.
     * Salidas: Identificador, cuatrimestre y llaves foráneas como enteros.
     */
    protected function casts(): array
    {
        return [
            'id_grupo' => 'integer',
            'cuatrimestre' => 'integer',
            'id_periodo' => 'integer',
            'id_carrera' => 'integer',
        ];
    }

    /**
     * Descripción: Obtiene el periodo académico del grupo.
     * Entradas: Ninguna.
     * Salidas: Relación con el modelo Periodo.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(
            Periodo::class,
            'id_periodo',
            'id_periodo'
        );
    }

    /**
     * Descripción: Obtiene la carrera a la que pertenece el grupo.
     * Entradas: Ninguna.
     * Salidas: Relación con el modelo Carrera.
     */
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(
            Carrera::class,
            'id_carrera',
            'id_carrera'
        );
    }
}
