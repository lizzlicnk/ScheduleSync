
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrera', function (Blueprint $table) {

            // Identificador único de la carrera.
            $table->id('id_carrera');

            // Nombre de la carrera.
            $table->string('nombreCarrera', 100);

            // Clave única de identificación de la carrera.
            $table->string('claveCarrera', 45)->unique();

            // Estado de la carrera: 1 activa, 0 inactiva.
            $table->integer('estado')->default(1);
        });
    }

    public function down(): void
    {
        // Eliminar la tabla carrera al revertir la migración.
        Schema::dropIfExists('carrera');
    }
};
