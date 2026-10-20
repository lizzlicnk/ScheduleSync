
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción:
     * Crea la tabla asignatura_preferida para relacionar  docentes, asignaturas y periodos académicos.
     *
     * Entradas:
     * No recibe parámetros.
     *
     * Salidas:
     * Crea la tabla asignatura_preferida en la base de datos.
     */
    public function up(): void
    {
        Schema::create('asignatura_preferida', function (Blueprint $tabla) {
            // Identificador único de la preferencia.
            $tabla->id('id_asignPreferida');

            // Periodo en el que se registra la preferencia.
            $tabla->unsignedBigInteger('id_periodo');

            // Docente que selecciona la asignatura.
            $tabla->unsignedBigInteger('id_docente');

            // Asignatura seleccionada.
            $tabla->unsignedBigInteger('id_asignatura');

            // Relación con la tabla periodo.
            $tabla->foreign('id_periodo')
                ->references('id_periodo')
                ->on('periodo')
                ->restrictOnDelete();

            // Relación con la tabla docente.
            $tabla->foreign('id_docente')
                ->references('id_docente')
                ->on('docente')
                ->restrictOnDelete();

            // Relación con la tabla asignatura.
            $tabla->foreign('id_asignatura')
                ->references('id_asignatura')
                ->on('asignatura')
                ->restrictOnDelete();

            // Evita duplicar la misma preferencia
            // para un docente, asignatura y periodo.
            $tabla->unique(
                ['id_periodo', 'id_docente', 'id_asignatura'],
                'uq_asignatura_preferida'
            );
        });
    }

    /**
     * Descripción:
     * Elimina la tabla asignatura_preferida.
     *
     * Entradas:
     * No recibe parámetros.
     *
     * Salidas:
     * Elimina la tabla asignatura_preferida de la base de datos.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignatura_preferida');
    }
};
