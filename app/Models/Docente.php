<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//relación entre dos modelos.
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Docente extends Model
{
    /**
     * Descripción: Modelo encargado de administrar la información de los docentes.
     * Entradas: Datos de identificación, especialidad, tipo y carga académica.
     * Salidas: Registros de docentes y su relación con la cuenta de usuario.
     */

    // Nombre de la tabla asociada al modelo.
    protected $table = 'docente';

    // Nombre de la llave primaria.
    protected $primaryKey = 'id_docente';

    // Indicar que la tabla no utiliza created_at ni updated_at.
    public $timestamps = false;

    // Campos que pueden asignarse de manera masiva.
    protected $fillable = [
        'matricula',
        'especialidad',
        'tipoDocente',
        'cargaAcademica',
        'id_usuario',
    ];

    /**
     * Descripción: Establecer la relación entre el docente y su cuenta de usuario.
     * Entradas:
     * No recibe parámetros directamente. Utiliza el identificador
     * id_usuario del docente para localizar la cuenta relacionada.
     * Salidas: Devuelve una relación BelongsTo que permite acceder al registro del usuario asociado al docente.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }
}
