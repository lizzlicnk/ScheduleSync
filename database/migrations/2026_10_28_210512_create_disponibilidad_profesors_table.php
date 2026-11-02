
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Crea la tabla disponibilidadProfesor para almacenar la disponibilidad horaria de los docentes.
     * Entradas: No recibe parámetros.
     * Salidas: Crea la tabla disponibilidadProfesor en la base de datos.
     */
    public function up(): void
    {
        Schema::create('disponibilidadProfesor', function (Blueprint $tabla) {

            // Identificador único de la disponibilidad.
            $tabla->id('id_disponibilidad');

            // Día de la semana de la disponibilidad.
            $tabla->integer('dia');

            // Hora de inicio de la disponibilidad.
            $tabla->time('hora_inicio');

            // Hora de finalización de la disponibilidad.
            $tabla->time('hora_fin');

            // Indica si el docente confirmó su disponibilidad.
            $tabla->integer('estadoConfirmacion')->default(0);

            // Fecha y hora en que se confirmó la disponibilidad.
            $tabla->dateTime('fechaConfirmacion')->nullable();

            // Identificador del docente.
            $tabla->unsignedBigInteger('id_docente');

            // Identificador del periodo académico.
            $tabla->unsignedBigInteger('id_periodo');

            // Relación con la tabla docente.
            $tabla->foreign('id_docente') ->references('id_docente') ->on('docente') ->restrictOnDelete();

            // Relación con la tabla periodo.
            $tabla->foreign('id_periodo') ->references('id_periodo') ->on('periodo') ->restrictOnDelete();
        });
    }

    /**
     * Descripción: Elimina la tabla disponibilidadProfesor.
     * Entradas: No recibe parámetros.
     * Salidas: Elimina la tabla de la base de datos.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilidadProfesor');
    }
};
