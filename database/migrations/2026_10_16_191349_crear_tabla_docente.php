<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente', function (Blueprint $table) {
            // Identificador único del docente.
            $table->id('id_docente');

            // Número de empleado del docente.
            $table->string('matricula', 20)->unique();

            // Especialidad o área de conocimiento del docente.
            $table->string('especialidad', 100);

            // Tipo de docente dentro de la universidad.
            $table->integer('tipoDocente');

            // Horas de carga académica semanal asignadas al docente.
            $table->integer('cargaAcademica')->default(20);

            // Identificador de la cuenta de usuario relacionada.
            $table->foreignId('id_usuario')
                ->unique()
                ->constrained('usuario', 'id_usuario')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Eliminar la tabla docente al revertir la migración.
        Schema::dropIfExists('docente');
    }
};
