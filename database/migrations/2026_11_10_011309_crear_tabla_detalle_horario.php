<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Crea la tabla detalle_horario para almacenar las asignaciones de asignaturas y docentes en los horarios académicos.
     * Entradas: No recibe parámetros.
     * Salidas: Crea la tabla detalle_horario en la base de datos.
     */
    public function up(): void
    {
        Schema::create('detalle_horario', function (Blueprint $tabla) {

            // Identificador único del detalle del horario.
            $tabla->id('id_detalle');

            // Día de la semana en que se imparte la asignatura.
            $tabla->string('dia', 15);

            // Hora de inicio de la clase.
            $tabla->time('hora_inicio');

            // Hora de finalización de la clase.
            $tabla->time('hora_fin');

            // Aula o laboratorio donde se imparte la clase.
            $tabla->string('aula', 20)->nullable();

            // Horario al que pertenece el detalle.
            $tabla->unsignedBigInteger('id_horario');

            // Docente encargado de impartir la asignatura.
            $tabla->unsignedBigInteger('id_docente');

            // Asignatura que se impartirá.
            $tabla->unsignedBigInteger('id_asignatura');

            // Relación con la tabla horario.
            $tabla->foreign('id_horario')->references('id_horario')->on('horario')->restrictOnDelete();

            // Relación con la tabla docente.
            $tabla->foreign('id_docente')->references('id_docente')->on('docente')->restrictOnDelete();

            // Relación con la tabla asignatura.
            $tabla->foreign('id_asignatura')->references('id_asignatura')->on('asignatura')->restrictOnDelete();
        });
    }

    /**
     * Descripción: Elimina la tabla detalle_horario.
     * Entradas: No recibe parámetros.
     * Salidas: Elimina la tabla detalle_horario de la base de datos.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_horario');
    }
};