<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Descripción: Marca una notificación como leída.
     *
     * Entradas:
     * - $idNotificacion: Identificador de la notificación.
     *
     * Salidas:
     * - Respuesta JSON indicando si la notificación fue actualizada.
     */
    public function marcarLeida($idNotificacion)
    {
        // Buscar la notificación correspondiente al usuario autenticado.
        $notificacion = Notificacion::where(
            'id_notificacion',
            $idNotificacion
        )
            ->where('id_usuario', auth()->user()->id_usuario)
            ->firstOrFail();

        // Actualizar el estado de la notificación.
        $notificacion->estado = true;
        $notificacion->save();

        // Confirmar que la notificación fue marcada como leída.
        return response()->json([
            'exito' => true,
            'mensaje' => 'La notificación fue marcada como leída.'
        ]);
    }
}
