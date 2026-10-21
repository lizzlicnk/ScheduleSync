<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignaturaPreferida extends Model
{
    // Nombre de la tabla asociada al modelo.
    protected $table = 'asignatura_preferida';

    // Llave primaria de la tabla.
    protected $primaryKey = 'id_asignPreferida';

    public $timestamps = false;

    // Atributos 
    protected $fillable = [
        'id_periodo',
        'id_docente',
        'id_asignatura',
    ];

    // Conversión de llaves foráneas a enteros.
    protected function casts(): array
    {
        return [
            'id_periodo' => 'integer',
            'id_docente' => 'integer',
            'id_asignatura' => 'integer',
        ];
    }

    /**
     * Descripción: Obtiene el periodo académico de la preferencia.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna la relación con el modelo Periodo.
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
     * Descripción: Obtiene el docente que registró la preferencia.
     *
     * Entradas: No recibe parámetros.
     *
     * Salidas: Retorna la relación con el modelo Docente.
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(
            Docente::class,
            'id_docente',
            'id_docente'
        );
    }

    /**
     * Descripción: Obtiene la asignatura seleccionada como preferencia.
     * Entradas:No recibe parámetros.
     * Salidas: Retorna la relación con el modelo Asignatura.
     */
    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(
            Asignatura::class,
            'id_asignatura',
            'id_asignatura'
        );
    }
}
