<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Horario;
use App\Models\Docente;
use App\Models\Asignatura;

class DetalleHorario extends Model
{
    // Funcionalidades para la creación y gestión de registros.
    use HasFactory;

    // Nombre de la tabla.
    protected $table = 'detalle_horario';

    // Nombre de la llave primaria
    protected $primaryKey = 'id_detalle';

    // No utiliza created_at ni updated_at.
    public $timestamps = false;

    // Atributos
    protected $fillable = [
        'dia',
        'hora_inicio',
        'hora_fin',
        'aula',
        'id_horario',
        'id_docente',
        'id_asignatura',
    ];

    // Descripción: Definir la conversión automática de los campos.
    protected function casts(): array
    {
        return [
            'id_horario' => 'integer',
            'id_docente' => 'integer',
            'id_asignatura' => 'integer',
        ];
    }

    /**
     * Descripción: Obtiene el horario al que pertenece el detalle.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna la relación con el registro correspondiente en la tabla horario.
     */
    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id_horario');
    }

    /**
     * Descripción: Obtiene el docente encargado de impartir la asignatura.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna la relación con el registro correspondiente en la tabla docente.
     */
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }

    /**
     * Descripción: Obtiene la asignatura que se impartirá en el horario.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna la relación con el registro correspondiente en la tabla asignatura.
     */
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }
}