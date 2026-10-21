<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Agrega el campo estado a la tabla de grupos.
     * Entradas: Ninguna.
     * Salidas: Campo estado agregado con valor predeterminado 1.
     */
    public function up(): void
    {
        Schema::table('grupo', function (Blueprint $tabla) {

            // Estado del grupo: 1 = activo, 0 = inactivo.
            $tabla->integer('estado')->default(1);
        });
    }

    /**
     * Descripción: Elimina el campo estado de la tabla de grupos.
     * Entradas: Ninguna.
     * Salidas: Campo estado eliminado.
     */
    public function down(): void
    {
        Schema::table('grupo', function (Blueprint $tabla) {

            // Eliminar el campo estado.
            $tabla->dropColumn('estado');
        });
    }
};
