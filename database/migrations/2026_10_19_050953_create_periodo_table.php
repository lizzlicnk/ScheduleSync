
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Crea la tabla de periodos académicos.
     * Entradas: Ninguna.
     * Salidas: Tabla periodo con identificador, estación y año.
     */
    public function up(): void
    {
        Schema::create('periodo', function (Blueprint $tabla) {
            // Identificador único del periodo.
            $tabla->id('id_periodo');

            // Estación académica: Primavera, Otoño o Invierno.
            $tabla->string('estacion', 30);

            // Año del periodo académico.
            $tabla->integer('anio');

            // Evita repetir una estación en el mismo año.
            $tabla->unique(
                ['estacion', 'anio'],
                'periodo_estacion_anio_unico'
            );
        });
    }

    /**
     * Descripción: Elimina la tabla de periodos.
     * Entradas: Ninguna.
     * Salidas: Tabla periodo eliminada.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodo');
    }
};
