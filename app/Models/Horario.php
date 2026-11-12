<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\DetalleHorario;

class Horario extends Model
{
    // Funcionalidades para la creación y gestión de registros.
    use HasFactory;

    // Nombre de la tabla utilizada.
    protected $table = 'horario';

    // Nombre de la llave primaria.
    protected $primaryKey = 'id_horario';

    public $timestamps = false;

    // Atributos.
    protected $fillable = [
        'nombreHorario',
        'estado',
        'id_grupo',
        'id_periodo',
    ];

    /**
     * Descripción: Definir la conversión automática de los campos.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna un arreglo con los tipos de datos.
     */
    protected function casts(): array
    {
        return [
            'estado' => 'integer',
            'id_grupo' => 'integer',
            'id_periodo' => 'integer',
        ];
    }

    /**
     * Descripción: Obtiene el grupo académico al que pertenece el horario.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna la relación con el registro correspondiente en la tabla grupo.
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    /**
     * Descripción: Obtiene el periodo escolar al que pertenece el horario.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna la relación con el registro correspondiente en la tabla periodo.
     */
    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }

    /**
     * Descripción: Obtiene los detalles y asignaciones que conforman el horario.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna la relación con los registros correspondientes en la tabla detalle_horario.
     */
    public function detalles()
    {
        return $this->hasMany(DetalleHorario::class, 'id_horario', 'id_horario');
    }
}
