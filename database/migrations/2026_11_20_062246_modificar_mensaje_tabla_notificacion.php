<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Modifica el campo mensaje para almacenar texto.
     *
     * Entradas: Ninguna.
     *
     * Salidas: Columna mensaje modificada en la tabla notificacion.
     */
    public function up(): void
    {
        Schema::table('notificacion', function (Blueprint $tabla) {
            $tabla->string('mensaje')->change();
        });
    }

    /**
     * Descripción: Revierte el tipo de dato del campo mensaje.
     *
     * Entradas: Ninguna.
     *
     * Salidas: Columna mensaje restaurada a tipo entero.
     */
    public function down(): void
    {
        Schema::table('notificacion', function (Blueprint $tabla) {
            $tabla->integer('mensaje')->change();
        });
    }
};