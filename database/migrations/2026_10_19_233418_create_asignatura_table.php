
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción:
     * Crea la tabla asignatura para almacenar las asignaturas
     * correspondientes a las carreras de la universidad.
     *
     * Entradas:
     * No recibe parámetros.
     *
     * Salidas:
     * Crea la tabla asignatura en la base de datos.
     */
    public function up(): void
    {
        Schema::create('asignatura', function (Blueprint $tabla) {
            // Identificador único de la asignatura.
            $tabla->id('id_asignatura');

            // Nombre y clave de identificación.
            $tabla->string('nombreAsignatura', 100);
            $tabla->string('clave', 20)->unique();

            // Información académica.
            $tabla->unsignedTinyInteger('cuatrimestre');
            $tabla->unsignedSmallInteger('cantidadHoras');

            // Carrera a la que pertenece la asignatura.
            $tabla->unsignedBigInteger('id_carrera');

            // Estado de la asignatura: 1 activa, 0 inactiva.
            $tabla->boolean('estado')->default(true);

            // Relación con la tabla carrera.
            $tabla->foreign('id_carrera')
                ->references('id_carrera')
                ->on('carrera')
                ->restrictOnDelete();
        });
    }

    /**
     * Descripción:
     * Elimina la tabla asignatura.
     *
     * Entradas:
     * No recibe parámetros.
     *
     * Salidas:
     * Elimina la tabla asignatura de la base de datos.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignatura');
    }
};
