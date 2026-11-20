<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descripción: Crea la tabla notificacion para almacenar las notificaciones
     * generadas por el sistema y dirigidas a los usuarios.
     * Entradas: No recibe parámetros.
     * Salidas: Crea la tabla notificacion en la base de datos.
     */
    public function up(): void
    {
        Schema::create('notificacion', function (Blueprint $tabla) {

            // Identificador único de la notificación.
            $tabla->increments('id_notificacion');

            // Identificador del título de la notificación.
            $tabla->integer('titulo');

            // Identificador del mensaje de la notificación.
            $tabla->integer('mensaje');

            // Identificador del medio de envío de la notificación.
            $tabla->integer('medio');

            // Estado de la notificación.
            $tabla->boolean('estado')->default(true);

            // Fecha y hora en que se genera o envía la notificación.
            $tabla->dateTime('fechaEnvio');

            // Usuario al que está dirigida la notificación.
            $tabla->unsignedBigInteger('id_usuario');

            // Relación con la tabla usuario.
            $tabla->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuario')
                ->onDelete('cascade');
        });
    }

    /**
     * Descripción: Elimina la tabla notificacion.
     * Entradas: No recibe parámetros.
     * Salidas: Elimina la tabla notificacion de la base de datos.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacion');
    }
};
