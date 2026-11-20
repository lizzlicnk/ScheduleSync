<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class Notificacion extends Model
{
    /**
     * Descripción: Indica el nombre de la tabla relacionada con el modelo.
     */
    protected $table = 'notificacion';

    /**
     * Descripción: Establece la llave primaria de la tabla.
     */
    protected $primaryKey = 'id_notificacion';

    /**
     * Descripción: Indica que la tabla no utiliza las columnas
     * created_at y updated_at.
     */
    public $timestamps = false;

    /**
     * Descripción: Define los campos que pueden asignarse masivamente.
     */
    protected $fillable = [
        'titulo',
        'mensaje',
        'medio',
        'estado',
        'fechaEnvio',
        'id_usuario'
    ];

    /**
     * Descripción: Define los tipos de datos de los atributos.
     */
    protected $casts = [
        'estado' => 'boolean',
        'fechaEnvio' => 'datetime'
    ];

    /**
     * Descripción: Obtiene el usuario al que pertenece la notificación.
     * Entradas: No recibe parámetros.
     * Salidas: Devuelve el usuario relacionado con la notificación.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
