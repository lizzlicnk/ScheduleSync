
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Crea la tabla de grupos académicos.
     * Entradas: Ninguna.
     * Salidas: Tabla grupo con sus relaciones a carrera y periodo.
     */
    public function up(): void
    {
        Schema::create('grupo', function (Blueprint $tabla) {

            // Identificador único del grupo.
            $tabla->id('id_grupo');

            // Nombre del grupo.
            $tabla->string('nombreGrupo', 10);

            // Cuatrimestre al que pertenece el grupo.
            $tabla->integer('cuatrimestre');

            // Periodo académico al que pertenece el grupo.
            $tabla->unsignedBigInteger('id_periodo');

            // Carrera a la que pertenece el grupo.
            $tabla->unsignedBigInteger('id_carrera');

            // Relación con la tabla periodo.
            $tabla->foreign('id_periodo')
                ->references('id_periodo')
                ->on('periodo')
                ->onDelete('restrict');

            // Relación con la tabla carrera.
            $tabla->foreign('id_carrera')
                ->references('id_carrera')
                ->on('carrera')
                ->onDelete('restrict');
        });
    }

    /**
     * Descripción: Elimina la tabla de grupos.
     * Entradas: Ninguna.
     * Salidas: Tabla grupo eliminada.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo');
    }
};
