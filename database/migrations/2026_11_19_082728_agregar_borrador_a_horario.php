
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción:
     * Agrega el campo borrador a la tabla horario para identificar
     * si un horario está pendiente de guardar o ya es definitivo.
     *
     * Entradas:
     * - No recibe parámetros.
     *
     * Salidas:
     * - Agrega la columna borrador de tipo entero y permite valores NULL.
     */
    public function up(): void
    {
        Schema::table('horario', function (Blueprint $table) {

            $table->integer('borrador')->nullable()->after('estado');
        });
    }

    /**
     * Descripción:
     * Elimina el campo borrador de la tabla horario.
     *
     * Entradas:
     * - No recibe parámetros.
     *
     * Salidas:
     * - Elimina la columna borrador.
     */
    public function down(): void
    {
        Schema::table('horario', function (Blueprint $table) {

            $table->dropColumn('borrador');
        });
    }
};
