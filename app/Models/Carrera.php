<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Permite definir la relación de una carrera con varios grupos.
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    /**
     * Descripción: Modelo encargado de administrar la información de las carreras.
     * Entradas: Datos de identificación, nombre, clave y estado de la carrera.
     * Salidas: Registros de carreras almacenados en la base de datos.
     */

    // Nombre de la tabla asociada al modelo.
    protected $table = 'carrera';

    // Nombre de la llave primaria.
    protected $primaryKey = 'id_carrera';

    public $timestamps = false;

    // Atributos 
    protected $fillable = [
        'nombreCarrera',
        'claveCarrera',
        'estado',
    ];

    // Convertir el estado a entero al recuperar el registro.
    protected $casts = [
        'estado' => 'integer',
    ];

    /**
     * Descripción: Obtiene los grupos asociados a la carrera.
     * Entradas: Ninguna.
     * Salidas: Relación con los grupos académicos.
     */
    public function grupos(): HasMany
    {
        return $this->hasMany(
            Grupo::class,
            'id_carrera',
            'id_carrera'
        );
    }
}
