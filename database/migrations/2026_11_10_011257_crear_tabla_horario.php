<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Crea la tabla horario para almacenar los datos generales de los horarios académicos.
     * Entradas: No recibe parámetros.
     * Salidas: Crea la tabla horario en la base de datos.
     */
    public function up(): void
    {
        Schema::create('horario', function (Blueprint $tabla) {

            // Identificador único del horario.
            $tabla->id('id_horario');

            // Nombre del horario.
            $tabla->string('nombreHorario', 45);

            // Estado del horario (1 = activo, 0 = inactivo).
            $tabla->integer('estado');

            // Grupo académico al que pertenece el horario.
            $tabla->unsignedBigInteger('id_grupo');

            // Periodo escolar al que corresponde el horario.
            $tabla->unsignedBigInteger('id_periodo');

            // Relación con la tabla grupo.
            $tabla->foreign('id_grupo')->references('id_grupo')->on('grupo')->restrictOnDelete();

            // Relación con la tabla periodo.
            $tabla->foreign('id_periodo')->references('id_periodo')->on('periodo')->restrictOnDelete();
        });
    }

    /**
     * Descripción: Elimina la tabla horario.
     * Entradas: No recibe parámetros.
     * Salidas: Elimina la tabla horario de la base de datos.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario');
    }
};