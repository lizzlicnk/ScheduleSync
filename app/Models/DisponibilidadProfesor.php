<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Docente;
use App\Models\Periodo;

class DisponibilidadProfesor extends Model
{
    // Funcionalidades para crear registros.
    use HasFactory;

    // Nombre de la tabla utilizada en MySQL.
    protected $table = 'disponibilidadProfesor';

    // Nombre de la llave primaria de la tabla.
    protected $primaryKey = 'id_disponibilidad';

    public $timestamps = false;

    // Atributos que se pueden registrar o modificar.
    protected $fillable = [
        'dia',
        'hora_inicio',
        'hora_fin',
        'estadoConfirmacion',
        'fechaConfirmacion',
        'id_docente',
        'id_periodo',
    ];

    /**
     * Descripción: Define la conversión automática de los campos.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna un arreglo con los tipos de datos.
     */
    protected function casts(): array
    {
        return [
            'dia' => 'integer',
            'estadoConfirmacion' => 'integer',
            'fechaConfirmacion' => 'datetime',
            'id_docente' => 'integer',
            'id_periodo' => 'integer',
        ];
    }

    /**
     * Descripción: Obtiene el docente al que pertenece la disponibilidad.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna la relación con el modelo Docente.
     */
    public function docente()
    {
        // Relaciona la disponibilidad con su docente.
        return $this->belongsTo(
            Docente::class,
            'id_docente',
            'id_docente'
        );
    }

    /**
     * Descripción: Obtiene el periodo de la disponibilidad.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna la relación con el modelo Periodo.
     */
    public function periodo()
    {
        // Relaciona la disponibilidad con su periodo académico.
        return $this->belongsTo(
            Periodo::class,
            'id_periodo',
            'id_periodo'
        );
    }
}
