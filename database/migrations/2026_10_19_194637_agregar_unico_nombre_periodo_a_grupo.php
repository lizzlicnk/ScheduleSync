<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Evita repetir el nombre de un grupo en el mismo periodo.
     * Entradas: Ninguna.
     * Salidas: Restricción única agregada a la tabla grupo.
     */
    public function up(): void
    {
        Schema::table('grupo', function (Blueprint $tabla) {
            $tabla->unique(
                ['nombreGrupo', 'id_periodo'],
                'grupo_nombre_periodo_unico'
            );
        });
    }

    /**
     * Descripción: Elimina la restricción única.
     * Entradas: Ninguna.
     * Salidas: Restricción única eliminada.
     */
    public function down(): void
    {
        Schema::table('grupo', function (Blueprint $tabla) {
            $tabla->dropUnique('grupo_nombre_periodo_unico');
        });
    }
};