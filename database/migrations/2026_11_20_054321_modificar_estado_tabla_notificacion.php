<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Modifica el estado predeterminado de las notificaciones
     * para que se registren inicialmente como no leídas.
     *
     * Entradas: No recibe parámetros.
     *
     * Salidas: Actualiza el valor predeterminado del campo estado.
     */
    public function up(): void
    {
        Schema::table('notificacion', function (Blueprint $tabla) {

            // Las notificaciones se crean inicialmente como no leídas.
            $tabla->boolean('estado')->default(false)->change();

        });
    }

    /**
     * Descripción: Restablece el estado predeterminado anterior.
     *
     * Entradas: No recibe parámetros.
     *
     * Salidas: Establece el valor predeterminado del campo estado en true.
     */
    public function down(): void
    {
        Schema::table('notificacion', function (Blueprint $tabla) {

            $tabla->boolean('estado')->default(true)->change();

        });
    }
};