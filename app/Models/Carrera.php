<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Indicar que la tabla no utiliza created_at ni updated_at.
    public $timestamps = false;

    // Campos que pueden asignarse de manera masiva.
    protected $fillable = [
        'nombreCarrera',
        'claveCarrera',
        'estado',
    ];

    // Convertir el estado a entero al recuperar el registro.
    protected $casts = [
        'estado' => 'integer',
    ];
}
