<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignatura extends Model
{
    // Nombre de la tabla asociada al modelo.
    protected $table = 'asignatura';

    // Llave primaria de la tabla.
    protected $primaryKey = 'id_asignatura';

    public $timestamps = false;

    // Atributos 
    protected $fillable = [
        'nombreAsignatura',
        'clave',
        'cuatrimestre',
        'cantidadHoras',
        'id_carrera',
        'estado',
    ];

    // Conversión de atributos a sus tipos correspondientes.
    protected function casts(): array
    {
        return [
            'cuatrimestre' => 'integer',
            'cantidadHoras' => 'integer',
            'id_carrera' => 'integer',
            'estado' => 'boolean',
        ];
    }

    /**
     * Descripción: Obtiene la carrera a la que pertenece la asignatura.
     * Entradas: No recibe parámetros.
     * Salidas:Retorna la relación con el modelo Carrera.
     */
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(
            Carrera::class,
            'id_carrera',
            'id_carrera'
        );
    }

    /**
     * Descripción: Obtiene las preferencias relacionadas con la asignatura.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna las preferencias registradas para la asignatura.
     */
    public function asignaturasPreferidas(): HasMany
    {
        return $this->hasMany(
            AsignaturaPreferida::class,
            'id_asignatura',
            'id_asignatura'
        );
    }
}
